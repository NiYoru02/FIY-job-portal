<?php
session_start();
include "db.php";

// 1. Get the ID
if (isset($_GET['id'])) {
    $company_id = $_GET['id'];
} 
elseif (isset($_SESSION['user_id']) && $_SESSION['role'] === 'employer') {
    $company_id = $_SESSION['user_id'];
} 
else {
    die("Error: Company ID not specified.");
}

// 2. Fetch the data
$stmt = $conn->prepare("SELECT * FROM employers WHERE id = ?");
$stmt->execute([$company_id]);
$employer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employer) {
    die("Error: Company not found.");
}

// 3. DEFINE THE MISSING VARIABLES HERE
$display_name = $employer['company_name'] ?? $employer['full_name'] ?? "Company Profile";
$img_src = !empty($employer['company_logo']) ? "uploads1/".$employer['company_logo'] : "https://via.placeholder.com/150";
$cover_src = !empty($employer['cover_photo']) ? "uploads1/".$employer['cover_photo'] : "https://via.placeholder.com/1200x300";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($display_name); ?> | Profile</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; padding: 40px; color: #333; }
        .container { max-width: 850px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .header-preview { position: relative; margin-bottom: 80px; }
        .banner-view { width: 100%; height: 250px; object-fit: cover; border-radius: 10px; }
        .logo-view { position: absolute; bottom: -50px; left: 30px; width: 120px; height: 120px; border-radius: 12px; border: 5px solid white; object-fit: cover; background: white; }
        .info-section { margin-top: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .info-box { background: #f9f9f9; padding: 15px; border-radius: 8px; border-left: 4px solid #3498db; }
        .label { font-size: 12px; color: #7f8c8d; text-transform: uppercase; font-weight: bold; }
        .value { font-size: 16px; color: #2c3e50; margin-top: 5px; }
        .bio-section { margin-top: 30px; line-height: 1.6; }
        .btn-message { display: inline-block; background: #3498db; color: white; padding: 12px 25px; border-radius: 6px; text-decoration: none; margin-top: 20px; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
  

    <div class="header-preview">
        <img src="<?php echo $cover_src; ?>" class="banner-view">
        <img src="<?php echo $img_src; ?>" class="logo-view">
    </div>

    <h1><?php echo htmlspecialchars($display_name); ?></h1>
    
    <div class="info-section">
        <div class="info-box">
            <div class="label">Industry</div>
            <div class="value"><?php echo htmlspecialchars($employer['industry'] ?? 'Not Specified'); ?></div>
        </div>
        <div class="info-box">
            <div class="label">Location</div>
            <div class="value"><?php echo htmlspecialchars($employer['location'] ?? 'Not Specified'); ?></div>
        </div>
        <div class="info-box">
            <div class="label">Company Size</div>
            <div class="value"><?php echo htmlspecialchars($employer['company_size'] ?? 'Not Specified'); ?></div>
        </div>
        <div class="info-box">
            <div class="label">Website</div>
            <div class="value">
                <?php if(!empty($employer['website'])): ?>
                    <a href="<?php echo htmlspecialchars($employer['website']); ?>" target="_blank">Visit Site</a>
                <?php else: ?>
                    None
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="bio-section">
        <h3>About Us</h3>
        <p><?php echo nl2br(htmlspecialchars($employer['bio'] ?? 'No description provided.')); ?></p>
    </div>

    <!-- This links back to the chat on the seeker dashboard -->
    <a href="employer_dashboard.php?employer_id=<?php echo $company_id; ?>" class="btn-message"> <-- back to dashboard</a>
</div>

</body>
</html>