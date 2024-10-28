<?php
session_start();
session_destroy();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FireStream - Logout</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --main-color: #050301;
            --text-color: #ffffff;
            --hover-color: #151516;
            --accent-color: #00010a;
            --error-color: #ff4444;
            --success-color: #44ff44;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background-color: var(--main-color);
            color: var(--text-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .login-container {
            background-color: var(--hover-color);
            padding: 2rem;
            border-radius: 10px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            margin-bottom: 0.5rem;
        }

        .message {
            text-align: center;
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 5px;
        }

        .success {
            background-color: var(--success-color);
            color: var(--main-color);
        }

        .login-button {
            display: inline-block;
            width: 100%;
            background-color: var(--accent-color);
            color: var(--text-color);
            padding: 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
            text-decoration: none;
            text-align: center;
            margin-top: 1rem;
        }

        .login-button:hover {
            background-color: #000220;
        }

        .login-button i {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>FireStream</h1>
            <p>Logout erfolgreich</p>
        </div>
        <div class="message success">
            <i class="fas fa-check-circle"></i>
            Sie wurden erfolgreich ausgeloggt
        </div>
        <a href="login.php" class="login-button">
            <i class="fas fa-sign-in-alt"></i>
            Zurück zum Login
        </a>
    </div>
</body>
</html>