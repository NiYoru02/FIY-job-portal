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
        :root { --primary: #3498db; --dark: #2c3e50; --light: #f4f7f6; --success: #2ecc71; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--light); margin: 0; padding: 40px; display: flex; justify-content: center; }
        
        .resume-container { 
            background: white; 
            width: 100%; 
            max-width: 800px; 
            padding: 50px; 
            border-radius: 12px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
            position: relative; 
        }

        .back-btn { 
            text-decoration: none; 
            color: #7f8c8d; 
            font-weight: bold; 
            font-size: 14px; 
            display: inline-block; 
            margin-bottom: 25px;
        }
        .back-btn:hover { color: var(--dark); }

        .resume-header { 
            display: flex; 
            align-items: center; 
            border-bottom: 2px solid #eee; 
            padding-bottom: 30px; 
            margin-bottom: 30px;
        }

        .photo-2x2 { 
            width: 140px; 
            height: 140px; 
            object-fit: cover; 
            border: 4px solid var(--primary); 
            border-radius: 8px;
            margin-right: 30px;
            background: #fafafa;
        }

        .header-info h1 { margin: 0; color: var(--dark); font-size: 32px; }
        .header-info p { margin: 5px 0; color: #7f8c8d; font-size: 16px; }
        
        .section { margin-bottom: 30px; }
        .section-title { 
            font-weight: bold; 
            color: var(--primary); 
            text-transform: uppercase; 
            font-size: 13px; 
            letter-spacing: 1.5px;
            margin-bottom: 12px;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 5px;
        }
        
        .content { 
            line-height: 1.7; 
            font-size: 16px; 
            color: #444; 
            background: #fafafa; 
            padding: 15px; 
            border-radius: 6px;
        }

        .chat-btn-container {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .chat-btn {
            background: var(--success);
            color: white;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: bold;
            transition: 0.3s;
            display: inline-block;
        }
        .chat-btn:hover { background: #27ae60; transform: translateY(-2px); }

        @media print { .back-btn, .chat-btn-container { display: none; } }
    </style>
</head>
<body>

    <div class="resume-container">
        <a href="view_applicants.php" class="back-btn">← Back to Applicants</a>

        <div class="resume-header">
            <?php 
                $picName = !empty($resume['profile_pic']) ? $resume['profile_pic'] : 'default.png';
                $picPath = "uploads/" . $picName;
            ?>
            <img src="<?php echo $picPath; ?>" alt="Profile Photo" class="photo-2x2" onerror="this.src='uploads/default.png'">
            <!-- Inside your while or foreach loop -->
<tr>
    <td>
        <a href="view_resume.php?id=<?php echo $row['id']; ?>" 
           style="text-decoration: none; color: #3498db; font-weight: bold;">
            <?php echo htmlspecialchars($row['fullname']); ?>
        </a>
    </td>
    <td><?php echo htmlspecialchars($row['email']); ?></td>
    <!-- Other columns -->
</tr>
            <div class="header-info">
                <h1><?php echo htmlspecialchars($resume['fullname']); ?></h1>
                <p>📧 <?php echo htmlspecialchars($resume['email']); ?></p>
                <p>📞 <?php echo htmlspecialchars($resume['phone']); ?></p>
                
            </div>
        </div>

        <div class="section">
            <div class="section-title">About Me</div>
            <div class="content"><?php echo nl2br(htmlspecialchars($resume['bio'] ?? 'Not provided')); ?></div>
        </div>

        <div class="section">
            <div class="section-title">Educational Background</div>
            <div class="content"><?php echo nl2br(htmlspecialchars($resume['education'] ?? 'Not provided')); ?></div>
        </div>

        <div class="section">
            <div class="section-title">Work & Projects</div>
            <div class="content"><?php echo nl2br(htmlspecialchars($resume['experience'] ?? 'Not provided')); ?></div>
        </div>

        <div class="section">
            <div class="section-title">Skills & Expertise</div>
            <div class="content">
                <strong>Technical:</strong> <?php echo htmlspecialchars($resume['skills'] ?? 'Not provided'); ?>
            </div>
        </div>

        <div class="chat-btn-container">
            <a href="employer_dashboard.php?seeker_id=<?php echo $resume['id']; ?>" class="chat-btn">
                💬 Send Message to Applicant
            </a>
        </div>
    </div>

</body>
</html>