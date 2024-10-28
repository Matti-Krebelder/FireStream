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

                $stmt = $this->db->prepare("
                    INSERT INTO login_history (user_id, ip_address, success) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([
                    $user['id'], 
                    $_SERVER['REMOTE_ADDR'], 
                    true
                ]);

                return ['success' => true, 'message' => 'Login successful'];
            } else {
                $this->incrementLoginAttempts($email);

                if ($user) {
                    $stmt = $this->db->prepare("
                        INSERT INTO login_history (user_id, ip_address, success) 
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([
                        $user['id'], 
                        $_SERVER['REMOTE_ADDR'], 
                        false
                    ]);
                }

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

    public function getUserData($userId) {
        try {
            $stmt = $this->db->prepare("SELECT id, email, created_at FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching user data: " . $e->getMessage());
            return null;
        }
    }

    public function changePassword($userId, $currentPassword, $newPassword) {
        if (strlen($newPassword) < MIN_PASSWORD_LENGTH) {
            return ['success' => false, 'message' => 'New password must be at least ' . MIN_PASSWORD_LENGTH . ' characters long'];
        }

        try {
            // Verify current password
            $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($currentPassword, $user['password'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }

            // Hash and update new password
            $hashedPassword = password_hash($newPassword, PASSWORD_HASH_ALGO);
            $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $userId]);

            return ['success' => true, 'message' => 'Password successfully updated'];
        } catch (PDOException $e) {
            error_log("Error changing password: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while changing the password'];
        }
    }

    public function getLoginHistory($userId, $limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT ip_address, login_time, success 
                FROM login_history 
                WHERE user_id = ? 
                ORDER BY login_time DESC 
                LIMIT ?
            ");
            $stmt->execute([$userId, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching login history: " . $e->getMessage());
            return [];
        }
    }
}
