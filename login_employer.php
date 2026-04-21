<?php
session_start();
include "db.php"; // No dots, just the file name since it's in the same folder

$error = "";

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // We only look for employers here
    $sql = "SELECT * FROM employers WHERE email = :email AND role = 'employer'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['full_name'];
        $_SESSION['role'] = 'employer';

        header("Location: employer_dashboard.php"); // Goes to employer dashboard
        exit();
    } else {
        $error = "Employer account not found or wrong password!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Employer Login</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 320px; }
        h2 { text-align: center; color: #333; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #28a745; color: white; border: none; cursor: pointer; } /* Green for Employer */
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Employer Login</h2>
        <?php if($error) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Employer Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login as Employer</button>
        </form>
        <p><a href="login.php">Back to Job Seeker Login</a></p>
        <p class="footer-text">
        Don't have an account? <a href="register_employer.php">Register here</a>
    </p>
    </div>
    
    
</body>
</html>