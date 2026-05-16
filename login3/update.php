<?php
session_start();
include 'db.php'; 

if (isset($_POST['id'])) {
    
    $id = $_POST['id'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email']; 
    $phone = $_POST['phone'];
    $skills = $_POST['skills'];
    $bio = $_POST['bio'];
    $education = $_POST['education'];
    $experience = $_POST['experience'];

    try {
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

        // --- NEW CHANGES START HERE ---
        // We must update the Session so the Dashboard knows the new info
        $_SESSION['email'] = $email; 
        $_SESSION['user_id'] = $id; // Just to be safe and stay locked in
        // --- NEW CHANGES END HERE ---

        header("Location: dashboard.php?msg=updated");
        exit();

    } catch(PDOException $e) {
        echo "Update failed: " . $e->getMessage();
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>