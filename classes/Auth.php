<?php
require_once 'Database.php';

class Auth {
    private $db;
    private $loginAttempts = [];

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        session_start();
    }

    public function login($email, $password) {
        if (!LOGIN_SYSTEM_ENABLED) {
            return ['success' => false, 'message' => 'Login system is disabled'];
        }

        if ($this->isAccountLocked($email)) {
            return ['success' => false, 'message' => 'Account is temporarily locked. Please try again later.'];
        }

        try {
            $stmt = $this->db->prepare("SELECT id, email, password, is_active FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                if (!$user['is_active']) {
                    return ['success' => false, 'message' => 'Account is not activated'];
                }

                $this->resetLoginAttempts($email);
                $this->createSession($user);
                
                return ['success' => true, 'message' => 'Login successful'];
            } else {
                $this->incrementLoginAttempts($email);
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during login'];
        }
    }

    private function isAccountLocked($email) {
        if (!isset($this->loginAttempts[$email])) {
            return false;
        }

        $attempts = $this->loginAttempts[$email];
        if ($attempts['count'] >= MAX_LOGIN_ATTEMPTS) {
            $lockoutTime = $attempts['last_attempt'] + (LOGIN_LOCKOUT_TIME * 60);
            if (time() < $lockoutTime) {
                return true;
            }
            $this->resetLoginAttempts($email);
        }
        return false;
    }

    private function incrementLoginAttempts($email) {
        if (!isset($this->loginAttempts[$email])) {
            $this->loginAttempts[$email] = ['count' => 0, 'last_attempt' => 0];
        }
        $this->loginAttempts[$email]['count']++;
        $this->loginAttempts[$email]['last_attempt'] = time();
    }

    private function resetLoginAttempts($email) {
        unset($this->loginAttempts[$email]);
    }

    private function createSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['last_activity'] = time();
        session_regenerate_id(true);
    }

    public function logout() {
        session_destroy();
        $this->loginAttempts = [];
        return ['success' => true, 'message' => 'Logout successful'];
    }

    public function isLoggedIn() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['last_activity'])) {
            return false;
        }

        if (time() - $_SESSION['last_activity'] > SESSION_LIFETIME) {
            $this->logout();
            return false;
        }

        $_SESSION['last_activity'] = time();
        return true;
    }
}