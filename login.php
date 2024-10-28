<?php
require_once 'config.php';
require_once 'classes/Auth.php';

if (!LOGIN_SYSTEM_ENABLED) {
    die('<div class="login-container"><div class="message error">Login system is currently disabled</div></div>');
}

$auth = new Auth();
$message = '';
$messageClass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (strlen($password) < MIN_PASSWORD_LENGTH) {
        $message = 'Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters long';
        $messageClass = 'error';
    } else {
        $result = $auth->login($email, $password);
        if ($result['success']) {
            header('Location: index.php');
            exit;
        } else {
            $message = $result['message'];
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
    <title>FireStream - Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>FireStream</h1>
            <p>Sign in to your account</p>
        </div>
        <?php if ($message): ?>
            <div class="message <?php echo $messageClass; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form class="login-form" method="POST" action="">
            <div class="form-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="login-button">Sign In</button>
        </form>
    </div>
</body>
</html>