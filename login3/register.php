<?php
include "db.php";
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($name) && !empty($email) && !empty($password)) {
        try {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO job_seekers (fullname, email, password) VALUES (:name, :email, :password)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':name' => $name, ':email' => $email, ':password' => $hashed]);
            $message = "Account created! <a href='login.php' style='color: #3b5998; font-weight: bold;'>Login here</a>";
        } catch (PDOException $e) {
            $message = "Error: Email might already be registered.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up | FIY Portal</title>
    <style>
        body { margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #e9ebee; }
        .top-bar { background-color: #3b5998; padding: 10px 0; color: white; }
        .header-content { max-width: 900px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; padding: 0 20px; }
        .logo { font-size: 30px; margin: 0; font-weight: bold; letter-spacing: -1px; text-decoration: none; color: white; }
        .login-container { display: flex; justify-content: center; padding-top: 50px; }
        .login-box { background-color: white; padding: 30px; width: 396px; border-radius: 4px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); text-align: center; border: 1px solid #dddfe2; }
        h2 { font-size: 18px; color: #1c1e21; margin-bottom: 20px; font-weight: bold; }
        .msg-box { background: #f2f2f2; border: 1px solid #dddfe2; padding: 10px; margin-bottom: 15px; color: #1c1e21; font-size: 13px; text-align: center; }
        .input-field { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #dddfe2; border-radius: 3px; box-sizing: border-box; font-size: 14px; }
        .signup-btn { width: 100%; background-color: #42b72a; color: white; border: 1px solid #296119; padding: 10px; font-size: 14px; font-weight: bold; border-radius: 3px; cursor: pointer; margin-top: 10px; }
        .footer-links { margin-top: 15px; font-size: 13px; border-top: 1px solid #dddfe2; padding-top: 15px; }
    </style>
</head>
<body>
    <!-- Update the <style> section -->
<style>
    /* ... existing styles ... */
    .logo { 
        font-size: 30px; 
        margin: 0; 
        font-weight: bold; 
        letter-spacing: -1px; 
        text-decoration: none; 
        color: white; 
        display: flex; 
        align-items: center; 
        gap: 5px; 
    }
    .logo img { 
        height: 120px; 
        width: auto; 
        display: block; 
        margin: -40px 0; 
    }
</style>

<!-- Update the <header> section -->
<header class="top-bar">
    <div class="header-content">
        <a href="login.php" class="logo">
           
            <img src="1.png" alt="Logo">
        </a>
    </div>
</header>
    <main class="login-container">
        <div class="login-box">
            <h2>Create a New Account</h2>
            <?php if($message): ?><div class="msg-box"><?php echo $message; ?></div><?php endif; ?>
            <form method="POST">
                <input type="text" name="full_name" placeholder="Full Name" class="input-field" required>
                <input type="email" name="email" placeholder="Email address" class="input-field" required>
                <input type="password" name="password" placeholder="New password" class="input-field" required>
                <button type="submit" class="signup-btn">Sign Up</button>
            </form>
            <div class="footer-links"><a href="login.php" style="color: #385898; text-decoration: none;">Already have an account?</a></div>
        </div>
    </main>
</body>
</html>