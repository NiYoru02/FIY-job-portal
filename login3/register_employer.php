<?php
include "db.php";
$message = "";

if (isset($_POST['register'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    $role = 'employer'; 

    $checkEmail = $conn->prepare("SELECT * FROM employers WHERE email = :email");
    $checkEmail->execute([':email' => $email]);

    if ($checkEmail->rowCount() > 0) {
        $message = "This employer email is already registered!";
    } else {
        $sql = "INSERT INTO employers (full_name, email, password, role) VALUES (:name, :email, :pass, :role)";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            ':name' => $full_name,
            ':email' => $email,
            ':pass' => $password,
            ':role' => $role
        ]);

        if ($result) {
            $message = "Account created! <a href='login_employer.php'>Login here</a>";
        } else {
            $message = "Registration failed.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Signup | FIY Portal</title>
    <style>
        body { margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #e9ebee; }
        .top-bar { background-color: #218838; padding: 10px 0; color: white; }
        .header-content { max-width: 900px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; padding: 0 20px; }
        .logo { font-size: 30px; margin: 0; font-weight: bold; letter-spacing: -1px; text-decoration: none; color: white; }
        .login-btn-top { background-color: #4267b2; color: white; border: 1px solid #29487d; padding: 5px 10px; font-weight: bold; font-size: 12px; border-radius: 2px; cursor: pointer; text-decoration: none; }
        .reg-container { display: flex; justify-content: center; padding-top: 50px; }
        .reg-box { background-color: white; padding: 30px; width: 396px; border-radius: 4px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); text-align: center; border: 1px solid #dddfe2; }
        h2 { font-size: 18px; color: #1c1e21; margin-bottom: 20px; font-weight: bold; }
        .input-field { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #dddfe2; border-radius: 3px; box-sizing: border-box; font-size: 14px; }
        .reg-btn { width: 100%; background-color: #28a745; color: white; border: 1px solid #1e7e34; padding: 10px; font-size: 14px; font-weight: bold; border-radius: 3px; cursor: pointer; margin-top: 10px; }
        .footer-links { margin-top: 15px; font-size: 13px; border-top: 1px solid #dddfe2; padding-top: 15px; }
        .footer-links a { color: #385898; text-decoration: none; }
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
        <a href="login_employer.php" class="logo">
           
            <img src="1.png" alt="Logo">
        </a>
        <a href="login_employer.php" class="login-btn-top">Log In</a>
    </div>
</header>

    <main class="reg-container">
        <div class="reg-box">
            <h2>Create Employer Account</h2>
            <?php if($message): ?>
                <p style="color: green; font-size: 13px;"><?php echo $message; ?></p>
            <?php endif; ?>
            <form method="POST">
                <input type="text" name="full_name" placeholder="Company or Manager Name" class="input-field" required>
                <input type="email" name="email" placeholder="Business Email" class="input-field" required>
                <input type="password" name="password" placeholder="New Password" class="input-field" required>
                <button type="submit" name="register" class="reg-btn">Sign Up</button>
            </form>
            <div class="footer-links">
                <a href="login.php">Register as Job Seeker</a>
            </div>
        </div>
    </main>
</body>
</html>