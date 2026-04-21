<?php
include "db.php";

$message = "";
// Set this to 'candidate' or 'employer'
$page_role = "candidate";

if (isset($_POST['register'])) {
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $role = $_POST['role']; 
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        // The labels here (:name, :email, etc.) must match the array below exactly
        $sql = "INSERT INTO users (full_name, email, password, role)
                VALUES (:name, :email, :password, :role)";
       
        $stmt = $conn->prepare($sql);
        
        // This is where the fix happens. 
        // We ensure the keys match the placeholders in the SQL string.
        $stmt->execute([
            ':name'     => $name,
            ':email'    => $email,
            ':password' => $password,
            ':role'     => $role
        ]);

        $message = "Account created! <a href='login.php'>Login here</a>";
    } catch (PDOException $e) {
        // If the database has a missing column, this will tell you exactly which one
        $message = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f4f7f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .register-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 90%; 
            max-width: 400px; 
        }
        h2 { text-align: center; margin-bottom: 20px; color: #333; }
        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box; 
        }
        button {
            width: 100%;
            padding: 12px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        button:hover { background: #218838; }
        .footer-text { text-align: center; margin-top: 20px; font-size: 14px; }
    </style>
</head>
<body>

<div class="register-container">
    <h2>Join as <?php echo ucfirst($page_role); ?></h2>

    <?php if($message): ?>
        <p style="color: green; text-align: center;"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="role" value="<?php echo $page_role; ?>">

        <input type="text" name="full_name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
       
        <button type="submit" name="register">Create Account</button>
    </form>

    <p class="footer-text">
        Already have an account? <a href="login.php">Login here</a>
    </p>
</div>

</body>
</html>