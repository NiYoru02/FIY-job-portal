<?php
include 'db.php'; // 1. Use the correct connection file

// 2. Collect the data from your "Add New" form
$fullname = $_POST['fullname'];
$email    = $_POST['email'];
$phone    = $_POST['phone'];
$skills   = $_POST['skills'];

try {
    // 3. Use Prepared Statements (Modern/Safe way)
    $sql = "INSERT INTO job_seekers (fullname, email, phone, skills) 
            VALUES (:fullname, :email, :phone, :skills)";

    $stmt = $conn->prepare($sql);
    
    // 4. Execute and "Bind" the data to the placeholders
    $stmt->execute([
        ':fullname' => $fullname,
        ':email'    => $email,
        ':phone'    => $phone,
        ':skills'   => $skills
    ]);

    // 5. Success! Redirect back to the dashboard
    header("Location: dashboard.php");
    exit();

} catch(PDOException $e) {
    // If something goes wrong (like a database error), it shows here
    echo "Insert failed: " . $e->getMessage();
}
?>