<?php
// Database Configuration
define('DB_HOST', 'localhost:3306');
define('DB_USER', 'your_username_here');
define('DB_PASS', 'your_password_here');
define('DB_NAME', 'your_database_name_here');

//Create an account and go to https://www.themoviedb.org/settings/api  to get your API key
define('THEMOVIEDB_API_KEY', 'your_api_key_here');
define('MOVIE_DIR', 'movies/');
define('LOGIN_SYSTEM_ENABLED', true);
define('MIN_PASSWORD_LENGTH', 6);
define('MAX_LOGIN_ATTEMPTS', 5); //before  account is locked for 15 minutes
define('LOGIN_LOCKOUT_TIME', 15); // minutes
define('PASSWORD_HASH_ALGO', PASSWORD_ARGON2ID);
define('SESSION_LIFETIME', 18000); // 5 hour