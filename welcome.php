<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config.php';
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'] ?? '';
    $signup_code = $_POST['signup_code'] ?? '';

    if (isset($_POST['login'])) {
        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid email or password';
        }
    } elseif (isset($_POST['signup'])) {
        if ($signup_code !== 'TRADING2025') {
            $error = 'Invalid signup code';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            try {
                $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
                $stmt->execute([$email, $hashed_password]);
                $_SESSION['user_id'] = $pdo->lastInsertId();
                header('Location: index.php');
                exit;
            } catch (PDOException $e) {
                $error = 'Email already exists';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DAX Trading Helper - Welcome</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #1a1a1a;
            color: #e0e0e0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: #2c2c2c;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .logo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 20px;
        }
        h2 {
            color: #00ccff;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #b0b0b0;
        }
        input[type="email"], input[type="password"], input[type="text"] {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background: #3a3a3a;
            color: #e0e0e0;
        }
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background: #00ccff;
            color: #1a1a1a;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        input[type="submit"]:hover {
            background: #00aaff;
        }
        .error {
            color: #ff5555;
            margin-bottom: 15px;
        }
        .toggle-link {
            color: #00ccff;
            text-decoration: none;
            display: block;
            margin-top: 10px;
        }
        .toggle-link:hover {
            text-decoration: underline;
        }
    </style>
    <script>
        function toggleForm() {
            const loginForm = document.getElementById('loginForm');
            const signupForm = document.getElementById('signupForm');
            loginForm.style.display = loginForm.style.display === 'none' ? 'block' : 'none';
            signupForm.style.display = signupForm.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</head>
<body>
    <div class="container">
        <img src="images/logo.jpg" alt="DAX Trading Helper Logo" class="logo">
        <h2>DAX Trading Helper</h2>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <div id="loginForm">
            <form method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <input type="submit" name="login" value="Login">
            </form>
            <a href="#" class="toggle-link" onclick="toggleForm()">Need an account? Sign up</a>
        </div>
        <div id="signupForm" style="display: none;">
            <form method="POST">
                <div class="form-group">
                    <label for="signup_email">Email</label>
                    <input type="email" id="signup_email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="signup_password">Password</label>
                    <input type="password" id="signup_password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <div class="form-group">
                    <label for="signup_code">Signup Code</label>
                    <input type="text" id="signup_code" name="signup_code" required>
                </div>
                <input type="submit" name="signup" value="Sign Up">
            </form>
            <a href="#" class="toggle-link" onclick="toggleForm()">Already have an account? Login</a>
        </div>
    </div>
</body>
</html>