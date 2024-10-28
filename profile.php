<?php
require_once 'config.php';
require_once 'classes/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userData = $auth->getUserData($userId);
$loginHistory = $auth->getLoginHistory($userId);

$message = '';
$messageClass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
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
        <a href="settings.php" class="icon"><i class="fas fa-cog"></i></a>
        <a href="logout.php" class="icon"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </header>

    <main class="profile-container">
        <div class="profile-header">
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <h1><?php echo htmlspecialchars($userData['email']); ?></h1>
            <p>Member since <?php echo date('F Y', strtotime($userData['created_at'])); ?></p>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $messageClass; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

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
                <?php foreach ($loginHistory as $login): ?>
                    <div class="login-entry <?php echo $login['success'] ? 'success' : 'failed'; ?>">
                        <div class="login-icon">
                            <i class="fas <?php echo $login['success'] ? 'fa-check' : 'fa-times'; ?>"></i>
                        </div>
                        <div class="login-details">
                            <p><?php echo $login['success'] ? 'Successful login' : 'Failed login attempt'; ?></p>
                            <small>
                                <?php echo date('F j, Y H:i', strtotime($login['login_time'])); ?>
                                from IP: <?php echo htmlspecialchars($login['ip_address']); ?>
                            </small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</body>
</html>