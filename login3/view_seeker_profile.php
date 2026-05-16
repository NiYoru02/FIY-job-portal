<?php
session_start();
include "db.php";

// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_GET['id'] ?? null;

if (!$user_id) {
    die("No applicant selected. Please go back to the applicants list.");
}

try {
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

// Helper function to handle the new JSON boxes vs old Text data
function displayList($data) {
    if (empty($data) || $data == 'Not provided') {
        return "Not provided";
    }

    $decoded = json_decode($data, true);

    // If it's valid JSON (new box format)
    if (is_array($decoded)) {
        if (empty($decoded)) return "Not provided";
        $html = '<ul style="margin: 0; padding-left: 20px;">';
        foreach ($decoded as $item) {
            if (!empty(trim($item))) {
                $html .= "<li>" . htmlspecialchars($item) . "</li>";
            }
        }
        $html .= '</ul>';
        return $html;
    }

    // Fallback: If it's just old plain text
    return nl2br(htmlspecialchars($data));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($resume['fullname']); ?> - Profile</title>
    <style>
        :root { --primary: #3498db; --dark: #2c3e50; --light: #f4f7f6; --success: #2ecc71; --warning: #f39c12; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--light); margin: 0; padding: 40px; display: flex; justify-content: center; }
        
        .resume-container { 
            background: white; 
            width: 100%; 
            max-width: 800px; 
            padding: 50px; 
            border-radius: 12px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
        }

        .back-btn { text-decoration: none; color: #7f8c8d; font-weight: bold; font-size: 14px; margin-bottom: 25px; display: inline-block; }
        
        .resume-header { display: flex; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 30px; margin-bottom: 30px; }
        .photo-2x2 { width: 140px; height: 140px; object-fit: cover; border: 4px solid var(--primary); border-radius: 8px; margin-right: 30px; }

        .section { margin-bottom: 30px; }
        .section-title { 
            font-weight: bold; color: var(--primary); text-transform: uppercase; 
            font-size: 13px; letter-spacing: 1.5px; margin-bottom: 12px; border-bottom: 1px solid #f0f0f0; padding-bottom: 5px;
        }
        
        .content { line-height: 1.7; font-size: 16px; color: #444; background: #fafafa; padding: 15px; border-radius: 6px; }

        .cv-link { display: inline-flex; background: var(--dark); color: white; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; margin-top: 10px; }
        .chat-btn { background: var(--success); color: white; text-decoration: none; padding: 12px 25px; border-radius: 25px; font-weight: bold; display: inline-block; }
    </style>
</head>
<body>

    <div class="resume-container">
        <a href="view_resume.php" class="back-btn">← Back to Applicants</a>

        <div class="resume-header">
            <?php 
                $picPath = "uploads/" . (!empty($resume['profile_pic']) ? $resume['profile_pic'] : 'default.png');
            ?>
            <img src="<?php echo $picPath; ?>" alt="Profile Photo" class="photo-2x2">
            
            <div class="header-info">
                <h1><?php echo htmlspecialchars($resume['fullname']); ?></h1>
                <p>📧 <?php echo htmlspecialchars($resume['email']); ?></p>
                <p>📞 <?php echo htmlspecialchars($resume['phone']); ?></p>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Attached CV / Resume File</div>
            <div class="content">
                <?php if (!empty($resume['resume_file'])): ?>
                    <a href="uploads/<?php echo $resume['resume_file']; ?>" class="cv-link" target="_blank">📄 View Applicant CV</a>
                <?php else: ?>
                    <p style="color: #e74c3c;">No document file uploaded.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="section">
            <div class="section-title">About Me</div>
            <div class="content"><?php echo nl2br(htmlspecialchars($resume['bio'] ?? 'Not provided')); ?></div>
        </div>

        <div class="section">
            <div class="section-title">Educational Background</div>
            <div class="content">
                <?php echo displayList($resume['education']); ?>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Work & Projects</div>
            <div class="content">
                <?php echo displayList($resume['experience']); ?>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Skills & Expertise</div>
            <div class="content">
                <strong>Technical:</strong> <?php echo htmlspecialchars($resume['skills'] ?? 'Not provided'); ?>
            </div>
        </div>

        <div style="text-align:center; margin-top:40px;">
            <a href="employer_dashboard.php?seeker_id=<?php echo $resume['id']; ?>" class="chat-btn">💬 Send Message</a>
        </div>
    </div>

</body>
</html>