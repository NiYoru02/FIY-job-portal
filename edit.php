<?php
session_start();
include 'db.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'] ?? '';

// Fetch existing data so the boxes aren't empty
$stmt = $conn->prepare("SELECT * FROM job_seekers WHERE email = :email");
$stmt->execute([':email' => $email]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die("No profile found to edit! Please go to <a href='add.php'>Add Profile</a> first.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit My Resume</title>
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
    <h2>Edit Your Resume</h2>

    <form method="POST" action="update.php">
    <input type="hidden" name="id" value="<?= $row['id']; ?>">

    <label>Full Name</label>
    <input type="text" name="fullname" value="<?= htmlspecialchars($row['fullname']); ?>" required>

    <label>Professional Bio</label>
    <textarea name="bio" rows="3"><?= htmlspecialchars($row['bio']); ?></textarea>
    
    <label>Phone Number</label>
    <input type="text" name="phone" value="<?= htmlspecialchars($row['phone']); ?>" required>
    
    <label>Education</label>
    <textarea name="education" rows="3"><?= htmlspecialchars($row['education']); ?></textarea>

    <label>Work Experience</label>
    <textarea name="experience" rows="4"><?= htmlspecialchars($row['experience']); ?></textarea>

    <label>Technical Skills</label>
    <textarea name="skills" rows="3" required><?= htmlspecialchars($row['skills']); ?></textarea>
    
    <button type="submit" name="update_profile">Update Record</button>
</form>
    <br>
    <a href="dashboard.php" style="text-decoration: none; color: #7f8c8d;">Cancel</a>
</div>
</body>
</html>