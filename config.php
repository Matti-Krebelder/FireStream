<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'firestream');

// Login System Configuration
define('LOGIN_SYSTEM_ENABLED', true);
define('MIN_PASSWORD_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 15); // minutes
define('PASSWORD_HASH_ALGO', PASSWORD_ARGON2ID);
define('SESSION_LIFETIME', 3600); // 1 hour