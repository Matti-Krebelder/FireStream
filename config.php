<?php
// Database Configuration
define('DB_HOST', '212.132.70.166:3306');
define('DB_USER', 'u16_4xtuqa99HU');
define('DB_PASS', 'anyHc!8kw4!.XBaliO+pBOjt');
define('DB_NAME', 's16_FireStream');

// Login System Configuration
define('LOGIN_SYSTEM_ENABLED', true);
define('MIN_PASSWORD_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 15); // minutes
define('PASSWORD_HASH_ALGO', PASSWORD_ARGON2ID);
define('SESSION_LIFETIME', 3600); // 1 hour