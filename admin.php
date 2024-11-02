<?php
require_once 'config.php';
require_once 'classes/Auth.php';
$auth = new Auth();

if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: index.php');
    exit;
}

$userId = $_SESSION['user_id'] ?? null;
$userData = $auth->getUserData($userId);
$message = '';
$messageClass = '';

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_moviedir':
                $newDir = rtrim($_POST['moviedir'], '/') . '/';
                if (is_dir($newDir)) {
                    $configFile = file_get_contents('config.php');
                    $configFile = preg_replace(
                        "/define\('MOVIE_DIR',\s*'[^']*'\);/",
                        "define('MOVIE_DIR', '$newDir');",
                        $configFile
                    );
                    file_put_contents('config.php', $configFile);
                    $message = 'Movie directory updated successfully';
                    $messageClass = 'success';
                } else {
                    $message = 'Invalid directory path';
                    $messageClass = 'error';
                }
                break;

            case 'delete_user':
                $deleteId = $_POST['user_id'];
                try {
                    $stmt = Database::getInstance()->getConnection()->prepare(
                        "DELETE FROM users WHERE id = ? AND id != ?"
                    );
                    $stmt->execute([$deleteId, $userId]);
                    $message = 'User deleted successfully';
                    $messageClass = 'success';
                } catch (PDOException $e) {
                    $message = 'Error deleting user';
                    $messageClass = 'error';
                }
                break;

            case 'toggle_admin':
                $toggleId = $_POST['user_id'];
                $isAdmin = $_POST['is_admin'];
                try {
                    $stmt = Database::getInstance()->getConnection()->prepare(
                        "UPDATE users SET is_admin = ? WHERE id = ? AND id != ?"
                    );
                    $stmt->execute([$isAdmin, $toggleId, $userId]);
                    $message = 'Admin status updated successfully';
                    $messageClass = 'success';
                } catch (PDOException $e) {
                    $message = 'Error updating admin status';
                    $messageClass = 'error';
                }
                break;

                case 'update_user':
                    $updateId = $_POST['user_id'];
                    $email = $_POST['email'];
                    $username = $_POST['username'];
                    $newPassword = $_POST['password'];
                    
                    try {
                        $sql = "UPDATE users SET ";
                        $params = [];
                        $updateParts = [];
                        
                        if (!empty($email)) {
                            $updateParts[] = "email = ?";
                            $params[] = $email;
                        }
                        if (!empty($username)) {
                            $updateParts[] = "username = ?";
                            $params[] = $username;
                        }
                        if (!empty($newPassword)) {
                            $updateParts[] = "password = ?";
                            $params[] = password_hash($newPassword, PASSWORD_DEFAULT);
                        }
                        
                        if (!empty($updateParts)) {
                            $sql .= implode(", ", $updateParts);
                            $sql .= " WHERE id = ?";
                            $params[] = $updateId;
                            
                            $stmt = Database::getInstance()->getConnection()->prepare($sql);
                            $stmt->execute($params);
                            
                            $message = 'User updated successfully';
                            $messageClass = 'success';
                        }
                    } catch (PDOException $e) {
                        $message = 'Error updating user: ' . $e->getMessage();
                        $messageClass = 'error';
                    }
                    break;

            case 'delete_movie':
                $moviePath = MOVIE_DIR . $_POST['movie_name'];
                if (file_exists($moviePath) && unlink($moviePath)) {
                    $message = 'Movie deleted successfully';
                    $messageClass = 'success';
                } else {
                    $message = 'Error deleting movie';
                    $messageClass = 'error';
                }
                break;

            case 'upload_movie':
                if (isset($_FILES['movie_file'])) {
                    $file = $_FILES['movie_file'];
                    $fileName = basename($file['name']);
                    $targetPath = MOVIE_DIR . $fileName;
                    
                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        $message = 'Movie uploaded successfully';
                        $messageClass = 'success';
                    } else {
                        $message = 'Error uploading movie';
                        $messageClass = 'error';
                    }
                }
                break;
        }
    }
}



// Fetch all users
$stmt = Database::getInstance()->getConnection()->query(
    "SELECT * FROM users ORDER BY created_at DESC"
);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all movies
$movies = array_diff(scandir(MOVIE_DIR), array('.', '..'));
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FireStream - Admin Panel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --main-color: #050301;
            --text-color: #ffffff;
            --hover-color: #151516;
            --accent-color: #00010a;
            --success-color: #4CAF50;
            --error-color: #f44336;
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
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            background-color: var(--main-color);
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
            color: var(--text-color);
        }

        .nav-icons {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .icon {
            font-size: 1.2rem;
            cursor: pointer;
            color: var(--text-color);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .icon:hover {
            color: #666;
        }

        .admin-container {
            padding: 6rem 2rem 2rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .tab {
            padding: 0.5rem 1rem;
            background-color: var(--hover-color);
            border: none;
            color: var(--text-color);
            cursor: pointer;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .tab.active {
            background-color: var(--accent-color);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }

        .message.success {
            background-color: var(--success-color);
        }

        .message.error {
            background-color: var(--error-color);
        }

        .section {
            background-color: var(--hover-color);
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .section h2 {
            margin-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #333;
            background-color: var(--main-color);
            color: var(--text-color);
            border-radius: 4px;
        }

        .submit-button {
            padding: 0.5rem 1rem;
            background-color: var(--accent-color);
            color: var(--text-color);
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-button:hover {
            opacity: 0.9;
        }

        .user-list, .movie-list {
            width: 100%;
            border-collapse: collapse;
        }

        .user-list th, .user-list td,
        .movie-list th, .movie-list td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #333;
        }

        .user-list th, .movie-list th {
            background-color: var(--accent-color);
        }

        .action-button {
            padding: 0.25rem 0.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 0.5rem;
            color: var(--text-color);
        }

        .edit-button {
            background-color: #2196F3;
        }

        .delete-button {
            background-color: var(--error-color);
        }

        .admin-toggle {
            background-color: var(--success-color);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1001;
        }

        .modal-content {
            background-color: var(--hover-color);
            padding: 2rem;
            border-radius: 8px;
            max-width: 500px;
            margin: 4rem auto;
        }

        .close {
            float: right;
            cursor: pointer;
            font-size: 1.5rem;
        }

        .upload-area {
            border: 2px dashed #666;
            padding: 2rem;
            text-align: center;
            margin-bottom: 1rem;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="index.php" class="logo">FireStream</a>
        <div class="nav-icons">
            <a href="index.php" class="icon"><i class="fas fa-home"></i></a>
            <a href="profile.php" class="icon"><i class="fas fa-user"></i></a>
            <a href="admin.php" class="icon active"><i class="fas fa-shield-alt"></i></a>
            <a href="logout.php" class="icon"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </header>

    <main class="admin-container">
        <?php if ($message): ?>
            <div class="message <?php echo $messageClass; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="tabs">
            <button class="tab active" data-tab="settings">Settings</button>
            <button class="tab" data-tab="users">Users</button>
            <button class="tab" data-tab="movies">Movies</button>
        </div>

        <div id="settings" class="tab-content active">
            <div class="section">
                <h2>Movie Directory Settings</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_moviedir">
                    <div class="form-group">
                        <label for="moviedir">Movie Directory Path:</label>
                        <input type="text" id="moviedir" name="moviedir" value="<?php echo MOVIE_DIR; ?>" required>
                    </div>
                    <button type="submit" class="submit-button">Update Directory</button>
                </form>
            </div>
        </div>

        <div id="users" class="tab-content">
            <div class="section">
                <h2>User Management</h2>
                <table class="user-list">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Created</th>
                            <th>Admin</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                <td><?php echo $user['is_admin'] ? 'Yes' : 'No'; ?></td>
                                <td>
                                    <button class="action-button edit-button" 
                                            onclick="openEditModal(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($user['id'] != $userId): ?>
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_admin">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="is_admin" value="<?php echo $user['is_admin'] ? '0' : '1'; ?>">
                                            <button type="submit" class="action-button admin-toggle">
                                                <i class="fas fa-<?php echo $user['is_admin'] ? 'user' : 'shield-alt'; ?>"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="action-button delete-button" onclick="return confirm('Are you sure you want to delete this user?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="movies" class="tab-content">
            <div class="section">
                <h2>Movie Management</h2>
                <div class="upload-area" id="dropZone">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="upload_movie">
                        <input type="file" name="movie_file" id="movieFile" accept="video/*" style="display: none;">
                        <label for="movieFile" class="submit-button">
                            <i class="fas fa-upload"></i> Select Movie File
                        </label>
                        <p>or drag and drop files here</p>
                    </form>
                </div>
                
                <table class="movie-list">
                    <thead>
                        <tr>
                            <th>Movie Name</th>
                            <th>Size</th>
                            <th>Last Modified</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movies as $movie): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($movie); ?></td>
                                <td><?php echo formatFileSize(filesize(MOVIE_DIR . $movie)); ?></td>
                                <td><?php echo date('Y-m-d H:i:s', filemtime(MOVIE_DIR . $movie)); ?></td>
                                <td>
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_movie">
                                        <input type="hidden" name="movie_name" value="<?php echo htmlspecialchars($movie); ?>">
                                        <button type="submit" class="action-button delete-button" onclick="return confirm('Are you sure you want to delete this movie?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Edit User Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit User</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="user_id" id="editUserId">
                <div class="form-group">
                    <label for="editUsername">Username:</label>
                    <input type="text" id="editUsername" name="username">
                </div>
                <div class="form-group">
                    <label for="editEmail">Email:</label>
                    <input type="email" id="editEmail" name="email">
                </div>
                <div class="form-group">
                    <label for="editPassword">New Password (leave blank to keep current):</label>
                    <input type="password" id="editPassword" name="password">
                </div>
                <button type="submit" class="submit-button">Update User</button>
            </form>
        </div>
    </div>

    <script>
        // Tab switching
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                tab.classList.add('active');
                document.getElementById(tab.dataset.tab).classList.add('active');
            });
        });

        // Edit modal functionality
        const modal = document.getElementById('editModal');
        const closeBtn = document.querySelector('.close');

        function openEditModal(user) {
    const modal = document.getElementById('editModal');
    const editForm = modal.querySelector('form');
    
    document.getElementById('editUserId').value = user.id;
    document.getElementById('editUsername').value = user.username;
    document.getElementById('editEmail').value = user.email;
    document.getElementById('editPassword').value = '';
    
    modal.style.display = 'block';
    
    editForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const response = await fetch('admin.php', {
            method: 'POST',
            body: formData
        });
        
        if (response.ok) {
            location.reload();
        }
    });
}

        closeBtn.onclick = function() {
            modal.style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Drag and drop functionality
        const dropZone = document.getElementById('dropZone');
        const movieFile = document.getElementById('movieFile');

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = '#2196F3';
        });

        dropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = '#666';
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = '#666';
            
            if (e.dataTransfer.files.length) {
                movieFile.files = e.dataTransfer.files;
                movieFile.form.submit();
            }
        });

        movieFile.addEventListener('change', () => {
            if (movieFile.files.length) {
                movieFile.form.submit();
            }
        });
    </script>

    <?php
    function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    ?>
</body>
</html>