<?php
session_start();

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // For testing/defense: Username 'admin' and Password 'cit2025'
    if ($username === "admin" && $password === "cit2025") {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin.php");
        exit();
    } else {
        $error = "Invalid Username or Password!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - CIT</title>
    <style>
        body { font-family: sans-serif; background: #003366; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); width: 320px; }
        h2 { text-align: center; color: #003366; margin-bottom: 25px; }
        
        /* Container for password and button */
        .password-container { position: relative; width: 100%; }
        
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; font-size: 16px; }
        
        /* The Show/Hide Toggle Button */
        .toggle-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #003366;
            font-size: 14px;
            font-weight: bold;
            padding: 5px;
        }

        button[type="submit"] { 
            width: 100%; 
            padding: 12px; 
            background: #003366; 
            color: white; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-weight: bold;
            margin-top: 10px;
        }
        
        .error { color: #e74c3c; font-size: 14px; text-align: center; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>CIT ADMIN</h2>
        <?php if($error): ?> <p class="error"><?php echo $error; ?></p> <?php endif; ?>
        
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            
            <div class="password-container">
                <input type="password" name="password" id="passwordInput" placeholder="Password" required>
                <button type="button" id="toggleBtn" class="toggle-btn" onclick="togglePassword()">SHOW</button>
            </div>
            
            <button type="submit">Login</button>
        </form>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('passwordInput');
            const toggleBtn = document.getElementById('toggleBtn');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.textContent = 'HIDE';
            } else {
                passwordInput.type = 'password';
                toggleBtn.textContent = 'SHOW';
            }
        }
    </script>
</body>
</html>