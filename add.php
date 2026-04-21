<?php
session_start();
include 'db.php';

$message = "";
// 1. Get the 'Bridge' (email) from the session
$email = $_SESSION['email'] ?? ''; 

if (isset($_POST['submit_profile'])) {
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $skills = $_POST['skills'];
    $bio = $_POST['bio'];
    $education = $_POST['education'];
    $experience = $_POST['experience'];

    try {
        // 2. We check if they already have a row or need a new one
        $check = $conn->prepare("SELECT id FROM job_seekers WHERE email = ?");
        $check->execute([$email]);
        
        if ($check->rowCount() > 0) {
            // Update existing
            $sql = "UPDATE job_seekers SET fullname = :fullname, phone = :phone, skills = :skills, bio = :bio, education = :education, experience = :experience WHERE email = :email";
        } else {
            // Create NEW - Make sure :email is included here!
            $sql = "INSERT INTO job_seekers (fullname, email, phone, skills, bio, education, experience) 
                    VALUES (:fullname, :email, :phone, :skills, :bio, :education, :experience)";
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':fullname'   => $fullname,
            ':email'      => $email, // <--- This fills the blank column!
            ':phone'      => $phone,
            ':skills'     => $skills,
            ':bio'        => $bio,
            ':education'  => $education,
            ':experience' => $experience
        ]);

        header("Location: dashboard.php");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Resume</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; padding: 40px; }
        .form-card { background: white; max-width: 600px; margin: auto; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        input, textarea { width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #3498db; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        label { font-weight: bold; color: #555; font-size: 14px; }
    </style>
</head>
<body>
<div class="form-card">
    <h2>Build Your Professional Resume</h2>
    <?php if($message) echo "<p style='color: green;'>$message</p>"; ?>
    <form method="POST">
        <label>Full Name</label>
        <input type="text" name="fullname" placeholder="John Doe" required>

        <label>Professional Bio</label>
        <textarea name="bio" rows="3" placeholder="Briefly describe yourself..."></textarea>
        
        <label>Phone Number</label>
        <input type="text" name="phone" placeholder="0912..." required>
        
        <label>Education</label>
        <textarea name="education" rows="3" placeholder="School Name, Degree, Years"></textarea>

        <label>Work Experience</label>
        <textarea name="experience" rows="4" placeholder="Previous jobs or projects..."></textarea>

        <label>Technical Skills</label>
        <textarea name="skills" rows="3" placeholder="e.g. PHP, MySQL, Arduino" required></textarea>
        
        <button type="submit" name="submit_profile">Save Changes</button>
    </form>
    <br>
    <a href="dashboard.php" style="text-decoration: none; color: #7f8c8d;">Cancel</a>
</div>
</body>
</html>