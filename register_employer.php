<?php
include "db.php";
$message = "";

if (isset($_POST['register'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    // Hashing the password is a "Science Guy" security must!
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    
    // We hard-code the role as 'employer' so there's no mistake
    $role = 'employer'; 

    // Check if email already exists
    $checkEmail = $conn->prepare("SELECT * FROM employers WHERE email = :email");
    $checkEmail->execute([':email' => $email]);

    if ($checkEmail->rowCount() > 0) {
        $message = "This employer email is already registered!";
    } else {
        // Insert into database
        $sql = "INSERT INTO employers (full_name, email, password, role) VALUES (:name, :email, :pass, :role)";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            ':name' => $full_name,
            ':email' => $email,
            ':pass' => $password,
            ':role' => $role
        ]);

        if ($result) {
            $message = "Employer account created! <a href='login_employer.php'>Login here</a>";
        } else {
            $message = "Registration failed. Check your database connection.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Employer Registration</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .reg-box { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 350px; border-top: 5px solid #28a745; }
        h2 { text-align: center; color: #333; }
        input { width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #28a745; color: white; border: none; cursor: pointer; border-radius: 4px; font-size: 16px; }
        button:hover { background: #218838; }
        .msg { text-align: center; color: green; font-weight: bold; }
    </style>
</head>
<body>
    <div class="reg-box">
        <h2>Employer Signup</h2>
        <p class="msg"><?php echo $message; ?></p>
        <form method="POST">
            <input type="text" name="full_name" placeholder="Company or Manager Name" required>
            <input type="email" name="email" placeholder="Business Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="register">Register as Employer</button>
        </form>
        <p style="text-align:center;">Already have an employer account? <a href="login_employer.php">Login here</a></p>
    </div>
</body>
</html>