<?php
session_start();

// Simple authentication - in production, use proper authentication
$admin_username = 'admin';
$admin_password = 'luxury2024'; // Change this!

if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === $admin_username && $password === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid credentials. Please check your username and password.';
    }
}

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Luxury Watches</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: white;
            padding: 3rem;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo h1 {
            color: #C9A142;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .logo p {
            color: #666;
            font-size: 0.9rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #C9A142;
            box-shadow: 0 0 0 3px rgba(201, 161, 66, 0.1);
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            background: #C9A142;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .btn:hover {
            background: #B8912A;
        }
        
        .error {
            background: #fee;
            color: #c33;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #fcc;
        }
        
        .credentials {
            margin-top: 2rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #666;
        }
        
        .credentials strong {
            color: #333;
        }
        
        .debug-info {
            margin-top: 1rem;
            padding: 1rem;
            background: #e3f2fd;
            border-radius: 8px;
            font-size: 0.8rem;
            color: #1976d2;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>Luxury Watches</h1>
            <p>Administration</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" value="admin" required>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" value="luxury2024" required>
            </div>
            
            <button type="submit" name="login" class="btn">Se connecter</button>
        </form>
        
        <div class="credentials">
            <strong>Identifiants par défaut:</strong><br>
            Utilisateur: <code>admin</code><br>
            Mot de passe: <code>luxury2024</code>
        </div>
        
        <div class="debug-info">
            <strong>Debug Info:</strong><br>
            PHP Version: <?= phpversion() ?><br>
            Session Status: <?= session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive' ?><br>
            Current Time: <?= date('Y-m-d H:i:s') ?>
        </div>
    </div>
</body>
</html>