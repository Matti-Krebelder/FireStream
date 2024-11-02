<?php
require_once 'config.php';
require_once 'classes/Auth.php';
$auth = new Auth();

if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    error_log("No user ID found in session");
    header('Location: logout.php');
    exit;
}

error_log("Profile page accessed by user ID: " . $userId);

$userData = $auth->getUserData($userId);
$loginHistory = $auth->getLoginHistory($userId);

error_log("User data retrieved: " . ($userData ? 'yes' : 'no'));
error_log("Login history entries: " . count($loginHistory));

$message = '';
$messageClass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($newPassword !== $confirmPassword) {
            $message = 'New passwords do not match';
            $messageClass = 'error';
        } else {
            $result = $auth->changePassword($userId, $currentPassword, $newPassword);
            $message = $result['message'];
            $messageClass = $result['success'] ? 'success' : 'error';
        }
    }
    elseif (isset($_POST['action']) && $_POST['action'] === 'update_username') {
        $newUsername = trim($_POST['username'] ?? '');
        if (!empty($newUsername)) {
            $result = $auth->updateUsername($userId, $newUsername);
            $message = $result['message'];
            $messageClass = $result['success'] ? 'success' : 'error';
            if ($result['success']) {
                $userData = $auth->getUserData($userId); 
            }
        } else {
            $message = 'Username cannot be empty';
            $messageClass = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FireStream - Profile</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>
    <header class="header">
        <a href="index.php" class="logo">FireStream</a>
        <div class="nav-icons">
    <a href="index.php" class="icon"><i class="fas fa-home"></i></a>
    <a href="profile.php" class="icon"><i class="fas fa-user"></i></a>
    <?php if ($userData['is_admin']): ?>
        <a href="admin.php" class="icon"><i class="fas fa-shield-alt"></i></a>
    <?php endif; ?>
    <a href="logout.php" class="icon"><i class="fas fa-sign-out-alt"></i></a>
</div>
    </header>

    <main class="profile-container">
        <div class="profile-header">
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <h1><?php echo htmlspecialchars($userData['username'] ?? ''); ?></h1>
            <p class="user-email"><?php echo htmlspecialchars($userData['email'] ?? ''); ?></p>
            <p>Member since <?php echo isset($userData['created_at']) ? date('F Y', strtotime($userData['created_at'])) : 'N/A'; ?></p>
            <?php if ($userData['is_admin']): ?>
                <span class="admin-badge"><i class="fas fa-shield-alt"></i> Administrator</span>
            <?php endif; ?>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $messageClass; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="profile-section">
            <h2>Update Username</h2>
            <form class="username-form" method="POST" action="">
                <input type="hidden" name="action" value="update_username">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo htmlspecialchars($userData['username'] ?? ''); ?>" required>
                </div>
                <button type="submit" class="submit-button">Update Username</button>
            </form>
        </div>

        <div class="profile-section">
            <h2>Change Password</h2>
            <form class="password-form" method="POST" action="">
                <input type="hidden" name="action" value="change_password">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" 
                           pattern=".{<?php echo MIN_PASSWORD_LENGTH; ?>,}"
                           title="Password must be at least <?php echo MIN_PASSWORD_LENGTH; ?> characters long" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="submit-button">Update Password</button>
            </form>
        </div>

        <div class="profile-section">
            <h2>Recent Login Activity</h2>
            <div class="login-history">
                <?php if (is_array($loginHistory) && count($loginHistory) > 0): ?>
                    <?php foreach ($loginHistory as $login): ?>
                        <div class="login-entry <?php echo $login['success'] ? 'success' : 'failed'; ?>">
                            <div class="login-icon">
                                <i class="fas <?php echo $login['success'] ? 'fa-check' : 'fa-times'; ?>"></i>
                            </div>
                            <div class="login-details">
                                <p class="login-details-text"><?php echo $login['success'] ? 'Successful login' : 'Failed login attempt'; ?></p>
                                <small>
                                    <?php 
                                    $loginTime = strtotime($login['login_time']);
                                    echo $loginTime ? date('F j, Y H:i', $loginTime) : 'Invalid date';
                                    ?>
                                    from IP: <?php echo htmlspecialchars($login['ip_address']); ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No login history available.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>