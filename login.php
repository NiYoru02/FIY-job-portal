<?php
session_start(); 
include "db.php";

$error = ""; 

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':email' => $email]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC); 

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        
        // --- ADD THIS LINE RIGHT HERE ---
        $_SESSION['email'] = $user['email']; 
        // ---------------------------------

        header("Location: dashboard.php"); 
        exit();
    } else {
        $error = "Invalid email or password!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Job Portal</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
            width: 320px;
        }
        h2 { text-align: center; color: #333; }
        input {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #007bff; /* Different color from Register (Blue) */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover { background: #0056b3; }
        .footer-text { text-align: center; font-size: 14px; margin-top: 15px; }
        .error-msg { color: red; text-align: center; font-size: 14px; font-weight: bold; }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Login</h2>

    <!-- Show the error if login fails -->
    <?php if($error): ?>
        <p class="error-msg"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
        
      
    </form>

    <p class="footer-text">
        Don't have an account? <a href="register.php">Register here</a>
    </p>
     
    <div style="margin-top: 20px; border-top: 1px solid #ccc; padding-top: 10px; text-align: center;">
    <p>Are you hiring?</p>
    <a href="login_employer.php" style="text-decoration: none;">
        <button type="button" style="background: #28a745; width: 100%;">Login as Employer</button>
    </a>
</div>
</div>

</body>
</html>