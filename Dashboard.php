<?php
session_start();
include 'db.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userEmail = isset($_SESSION['email']) ? $_SESSION['email'] : '';

$myInfo = null;
if ($userEmail) {
    // This SELECT * pulls EVERYTHING from the table, including the new columns
    $stmt = $conn->prepare("SELECT * FROM job_seekers WHERE email = ?");
    $stmt->execute([$userEmail]);
    $myInfo = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f7f6; padding: 40px; }
        .profile-card { background: white; max-width: 650px; margin: auto; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .header { border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .info-group { margin-bottom: 20px; }
        .label { font-weight: bold; color: #7f8c8d; font-size: 12px; text-transform: uppercase; margin-bottom: 4px; display: block; }
        
        /* white-space: pre-line; makes sure your "Enter" keys show up as new lines on screen */
        .value { font-size: 16px; color: #2c3e50; line-height: 1.5; white-space: pre-line; } 
        
        .nav-btns { margin-top: 30px; display: flex; gap: 10px; }
        button { padding: 10px 20px; border-radius: 6px; border: none; cursor: pointer; font-weight: bold; }
        .btn-blue { background: #3498db; color: white; }
        .btn-green { background: #2ecc71; color: white; }
        .btn-logout { color: #e74c3c; text-decoration: none; font-weight: bold; }
        hr { border: 0; border-top: 1px solid #eee; margin: 20px 0; }
    </style>
</head>
<body>

<div class="profile-card">
    <div class="header">
        <h2>My Resume / Profile</h2>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>

    <?php if($myInfo): ?>
        <div class="info-group">
            <span class="label">Full Name</span>
            <div class="value"><?= htmlspecialchars($myInfo['fullname']); ?></div>
        </div>

        <div class="info-group">
            <span class="label">About Me</span>
            <div class="value"><?= htmlspecialchars($myInfo['bio'] ?? 'No bio provided.'); ?></div>
        </div>

        <div class="info-group">
            <span class="label">Contact</span>
            <div class="value"><?= htmlspecialchars($myInfo['email']); ?> | <?= htmlspecialchars($myInfo['phone']); ?></div>
        </div>

        <hr>

        <div class="info-group">
            <span class="label">Education</span>
            <div class="value"><?= htmlspecialchars($myInfo['education'] ?? 'No education history added.'); ?></div>
        </div>

        <div class="info-group">
            <span class="label">Work Experience</span>
            <div class="value"><?= htmlspecialchars($myInfo['experience'] ?? 'No work experience added.'); ?></div>
        </div>

        <div class="info-group">
            <span class="label">Technical Skills</span>
            <div class="value"><?= htmlspecialchars($myInfo['skills']); ?></div>
        </div>
        
        <div class="nav-btns">
            <a href="available_jobs.php"><button class="btn-green">🔍 Find Jobs</button></a>
            <a href="add.php"><button class="btn-blue">Edit Resume</button></a>
        </div>

    <?php else: ?>
        <div style="text-align: center; padding: 20px;">
            <p style="color: #7f8c8d;">No resume profile found for <strong><?= htmlspecialchars($userEmail) ?></strong>.</p>
            <p>Please add your professional details to start applying for jobs.</p>
            <div class="nav-btns" style="justify-content: center;">
                <a href="add.php"><button class="btn-blue">Create My Profile</button></a>
                <a href="available_jobs.php"><button class="btn-green">Browse Jobs Only</button></a>
            </div>
        </div>
    <?php endif; ?>
</div>

</body>
</html>