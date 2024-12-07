
![Logo](http://node.starlight-studios.de:2006/images/FireStream/logo.png)
# 🎥 FireStream

FireStream is a PHP-based web application enabling users to stream movies and TV shows seamlessly. The platform offers an intuitive interface for browsing, searching, and managing a comprehensive collection of movies. Additionally, an admin panel is provided for managing users and movie uploads.

---

**Attention this Repo wont be updated soon if you wish any updates for the  project please contact me via email or discord.**


Discord server: [https://discord.gg/cFKCJGQez2](https://discord.gg/cFKCJGQez2)


Mail: [matti.kre@gmx.de](mailto:matti.kre@gmx.de)


## 📋 Features

- **User Authentication**: Secure login and registration system with session management.
- **Movie Database Integration**: Retrieves movie data from the [The Movie Database (TMDb)](https://www.themoviedb.org/settings/api) API, including titles, release years, genres, and ratings.
- **Movie Management**: Administrators can upload, delete, and manage movies directly from the admin panel.
- **Responsive Design**: Built with a responsive layout for both desktop and mobile devices.
- **Search Functionality**: Users can search for movies through a dynamic search bar.

---

## 🚀 Installation

### Requirements

- PHP 7.4 or higher
- A web server (Apache, Nginx, etc.)
- MySQL database
- An account with [The Movie Database](https://www.themoviedb.org/settings/api) for an API key

### Steps

1. **Clone the Repository**:
   - Clone the repository using the following command:
     ```bash
     git clone https://github.com/Matti-Krebelder/FireStream.git
     cd firestream
     ```

2. **Upload Files**:
   - Upload all files to the desired directory on your web server.

3. **Create Database**:
   - Set up a new MySQL database and execute the following SQL to create the necessary tables:
     ```sql
     SET NAMES utf8;
     SET time_zone = '+00:00';
     SET foreign_key_checks = 0;
     SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

     SET NAMES utf8mb4;

     DROP TABLE IF EXISTS `login_history`;
     CREATE TABLE `login_history` (
       `id` int(11) NOT NULL AUTO_INCREMENT,
       `user_id` int(11) NOT NULL,
       `ip_address` varchar(45) NOT NULL,
       `login_time` timestamp NULL DEFAULT current_timestamp(),
       `success` tinyint(1) DEFAULT 1,
       PRIMARY KEY (`id`),
       KEY `user_id` (`user_id`),
       CONSTRAINT `login_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

     DROP TABLE IF EXISTS `users`;
     CREATE TABLE `users` (
       `is_admin` tinyint(1) NOT NULL,
       `username` varchar(255) NOT NULL,
       `id` int(11) NOT NULL AUTO_INCREMENT,
       `email` varchar(255) NOT NULL,
       `password` varchar(255) NOT NULL,
       `is_active` tinyint(1) DEFAULT 1,
       `created_at` timestamp NULL DEFAULT current_timestamp(),
       PRIMARY KEY (`id`),
       UNIQUE KEY `email` (`email`)
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

     -- Add Default User
     INSERT INTO `users` (`is_admin`, `username`, `id`, `email`, `password`, `is_active`, `created_at`) VALUES
     (1, 'Firestream', 8, 'Firestream@starlight-studios.de', '$2y$10$V.2zoktqcdwkjhlcuhA9ne9biDspYvLsAUUZYoPTuP/dZpqz53zIO', 1, '2024-11-04 16:27:03');
     ```

4. **Edit Configuration File**:
   - Open `config.php` in the main directory and set up the database, API key, and login system configurations:

#### Configuration

**Database Setup**
| Constant | Description | Value |
|----------|-------------|-------|
| DB_HOST  | Database host or IP address | 'localhost' |
| DB_USER  | Database username | 'your_username_here' |
| DB_PASS  | Database password | 'your_password_here' |
| DB_NAME  | Database name | 'your_database_name_here' |

**The Movie Database API Key**
| Constant            | Description         | Value              |
|---------------------|---------------------|--------------------|
| THEMOVIEDB_API_KEY  | TMDb API key        | 'your_api_key_here'|

**Login System Configuration**
| Constant              | Description                                        | Value        |
|-----------------------|----------------------------------------------------|--------------|
| LOGIN_SYSTEM_ENABLED  | Enable login system                                | true         |
| MIN_PASSWORD_LENGTH   | Minimum password length                            | 6            |
| MAX_LOGIN_ATTEMPTS    | Maximum login attempts before lockout              | 5            |
| LOGIN_LOCKOUT_TIME    | Lockout time in minutes                            | 15           |
| PASSWORD_HASH_ALGO    | Password hash algorithm                            | PASSWORD_ARGON2ID |
| SESSION_LIFETIME      | Session lifetime in seconds                        | 18000        |

**Movie Directory**
| Constant      | Description             | Value        |
|---------------|-------------------------|--------------|
| MOVIE_DIR     | Movie directory path    | 'movies/'    |

5. **Launch Application**:
   - Open the application in your browser at `http://localhost/firestream` (or your web server’s URL).

---

## 📝 License

FireStream is licensed under the MIT License. See [LICENSE](LICENSE) for more information.

---

## 📚 Credits

- [The Movie Database (TMDb)](https://www.themoviedb.org/settings/api) for providing movie data.
- Developed by [Matti Krebelder](https://starlight-studios.de).

---

## 📝 Contact

For questions or suggestions, please contact me at [matti.kre@gmx.de](mailto:matti.kre@gmx.de).

---

## 👍 Thank You

Thank you for using FireStream! I hope you enjoy the application.

## 📷 Screenshots

![App Screenshot](http://node.starlight-studios.de:2006/images/FireStream/p1.png)


![App Screenshot](http://node.starlight-studios.de:2006/images/FireStream/p2.png)


![App Screenshot](http://node.starlight-studios.de:2006/images/FireStream/p3.png)


![App Screenshot](http://node.starlight-studios.de:2006/images/FireStream/p4.png)


![App Screenshot](http://node.starlight-studios.de:2006/images/FireStream/p5.png)


![App Screenshot](http://node.starlight-studios.de:2006/images/FireStream/p6.png)


![App Screenshot](http://node.starlight-studios.de:2006/images/FireStream/p7.png)


![App Screenshot](http://node.starlight-studios.de:2006/images/FireStream/p8.png)


![App Screenshot](http://node.starlight-studios.de:2006/images/FireStream/p9.png)


![App Screenshot](http://node.starlight-studios.de:2006/images/FireStream/p10.png)


![App Screenshot](http://node.starlight-studios.de:2006/images/FireStream/p11.png)


![App Screenshot](http://node.starlight-studios.de:2006/images/FireStream/p12.png)
