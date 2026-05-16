<?php
session_start();
include 'db.php'; 

$error = "";

// 1. We check if the request is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        // 2. Search 'job_seekers' table (Since that is where your data is)
        $sql = "SELECT * FROM job_seekers WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 3. Verify if user exists and password matches
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['fullname']; // Matches your DB column 'fullname'
            $_SESSION['role'] = 'seeker';
            $_SESSION['email'] = $user['email'];
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid email or password!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | FIY Portal</title>
    <style>
        body { margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #e9ebee; }
        .top-bar { background-color: #3b5998; padding: 10px 0; color: white; }
        .header-content { max-width: 900px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; padding: 0 20px; }
        .logo { font-size: 30px; margin: 0; font-weight: bold; letter-spacing: -1px; text-decoration: none; color: white; }
        .signup-btn { background-color: #42b72a; color: white; border: 1px solid #296119; padding: 5px 10px; font-weight: bold; font-size: 12px; border-radius: 2px; cursor: pointer; text-decoration: none; }
        .login-container { display: flex; justify-content: center; padding-top: 50px; }
        .login-box { background-color: white; padding: 30px; width: 396px; border-radius: 4px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); text-align: center; border: 1px solid #dddfe2; }
        h2 { font-size: 18px; color: #1c1e21; margin-bottom: 20px; font-weight: bold; }
        .error-box { background: #ffebe8; border: 1px solid #dd3c10; padding: 10px; margin-bottom: 15px; color: #1c1e21; font-size: 13px; text-align: left; }
        .input-field { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #dddfe2; border-radius: 3px; box-sizing: border-box; font-size: 14px; }
        .login-btn { width: 100%; background-color: #4267b2; color: white; border: 1px solid #29487d; padding: 10px; font-size: 14px; font-weight: bold; border-radius: 3px; cursor: pointer; margin-top: 10px; }
        .login-btn:hover { background-color: #365899; }
        .footer-links { margin-top: 15px; font-size: 13px; border-top: 1px solid #dddfe2; padding-top: 15px; }
        .footer-links a { color: #385898; text-decoration: none; }
        .footer-links a:hover { text-decoration: underline; }
        .not-now { display: block; margin-top: 15px; }
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
        margin: -40px 0; /* Keeps header slim while logo is big */
    }
</style>

<!-- Update the <header> section -->
<header class="top-bar">
    <div class="header-content">
        <a href="login.php" class="logo">
    
            <img src="1.png" alt="Logo">
        </a>
        <a href="register.php" class="signup-btn">Sign Up</a>
    </div>
</header>

    <main class="login-container">
        <div class="login-box">
            <h2>Log in to FIY Portal</h2>
            
            <?php if(!empty($error)): ?>
                <div class="error-box">
                    <strong>Login Failed:</strong> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form action="login.php" method="POST">
                <input type="text" name="email" placeholder="Email address" class="input-field" required>
                <input type="password" name="password" placeholder="Password" class="input-field" required>
                
                <!-- We removed the name="login" check in PHP so any submit works -->
                <button type="submit" class="login-btn">Log In</button>
            </form>

            <div class="footer-links">
                 <a href="register_employer.php">Sign up as Employer</a>
                <p><a href="index.php" class="not-now">Not now</a></p>
            </div>
        </div>
    </main>

</body>
</html>