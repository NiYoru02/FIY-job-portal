<?php
session_start();
include "db.php";

// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    die("No applicant selected.");
}

try {
    // Fetch the detailed resume info
    $sql = "SELECT * FROM job_seekers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $resume = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$resume) {
        die("Applicant has not set up their profile yet.");
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($resume['fullname']); ?> - Resume</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; padding: 40px; color: #333; }
        .resume-container { 
            max-width: 800px; 
            margin: auto; 
            background: white; 
            padding: 50px; 
            border-radius: 8px; 
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        .resume-header { 
            display: flex; 
            align-items: center; 
            border-bottom: 2px solid #3498db; 
            padding-bottom: 30px; 
            margin-bottom: 30px;
        }
        /* The 2x2 Photo Styling */
        .photo-2x2 { 
            width: 150px; 
            height: 150px; 
            object-fit: cover; 
            border: 3px solid #eee; 
            border-radius: 4px;
            margin-right: 30px;
        }
        .header-info h1 { margin: 0; color: #2c3e50; font-size: 32px; }
        .header-info p { margin: 5px 0; color: #7f8c8d; font-size: 18px; }
        
        .section { margin-bottom: 25px; }
        .section-title { 
            font-weight: bold; 
            color: #3498db; 
            text-transform: uppercase; 
            font-size: 14px; 
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        .content { line-height: 1.6; font-size: 16px; color: #444; }
        
        .back-btn { 
            display: inline-block; 
            margin-bottom: 20px; 
            text-decoration: none; 
            color: #3498db; 
            font-weight: 600; 
        }
        @media print { .back-btn { display: none; } } /* Hide back button if printing */
    </style>
</head>
<body>

    <div class="resume-container">
        <a href="view_applicants.php" class="back-btn">← Back to Applicants</a>

        <div class="resume-header">
            <?php 
                $picPath = "uploads/" . (!empty($resume['profile_pic']) ? $resume['profile_pic'] : 'default.png');
                // Check if file actually exists, otherwise use default
                if (!file_exists($picPath)) { $picPath = "uploads/default.png"; }
            ?>
            <img src="<?php echo $picPath; ?>" alt="Profile Photo" class="photo-2x2">
            
            <div class="header-info">
                <h1><?php echo htmlspecialchars($resume['fullname']); ?></h1>
                <p><?php echo htmlspecialchars($resume['email']); ?></p>
                <p>📞 <?php echo htmlspecialchars($resume['phone']); ?></p>
            </div>
        </div>

        <div class="section">
            <div class="section-title">About Me</div>
            <div class="content"><?php echo nl2br(htmlspecialchars($resume['bio'])); ?></div>
        </div>

        <div class="section">
            <div class="section-title">Educational Background</div>
            <div class="content"><?php echo nl2br(htmlspecialchars($resume['education'])); ?></div>
        </div>

        <div class="section">
            <div class="section-title">Work & Projects</div>
            <div class="content"><?php echo nl2br(htmlspecialchars($resume['experience'])); ?></div>
        </div>

        <div class="section">
            <div class="section-title">Skills & Expertise</div>
            <div class="content">
                <strong>Technical:</strong> <?php echo htmlspecialchars($resume['skills']); ?>
            </div>
        </div>
    </div>

</body>
</html>