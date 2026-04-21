<?php
session_start();
include 'db.php'; 

// 1. Check if the form was actually submitted
if (isset($_POST['id'])) {
    
    // 2. Collect ALL data
    $id = $_POST['id'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email']; // This is the bar you wanted!
    $phone = $_POST['phone'];
    $skills = $_POST['skills'];
    $bio = $_POST['bio'];
    $education = $_POST['education'];
    $experience = $_POST['experience'];

    try {
        // 3. The SQL Query
        $sql = "UPDATE job_seekers 
                SET fullname = :fullname, 
                    email = :email, 
                    phone = :phone, 
                    skills = :skills,
                    bio = :bio,
                    education = :education,
                    experience = :experience
                WHERE id = :id";

        $stmt = $conn->prepare($sql);
        
        // 4. Execution
        $stmt->execute([
            ':fullname'   => $fullname,
            ':email'      => $email,
            ':phone'      => $phone,
            ':skills'     => $skills,
            ':bio'        => $bio,
            ':education'  => $education,
            ':experience' => $experience,
            ':id'         => $id
        ]);

        // 5. Success!
        header("Location: dashboard.php?msg=updated");
        exit();

    } catch(PDOException $e) {
        // This is your "Error Log"
        echo "Update failed: " . $e->getMessage();
    }
} else {
    // If someone tries to access update.php directly without the form
    header("Location: dashboard.php");
    exit();
}
?>