<?php
session_start();
include "db.php";

if (!isset($_GET['id'])) {
    die("Company ID not specified.");
}

$company_id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT * FROM employers WHERE id = ?");
$stmt->execute([$company_id]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company) {
    die("Company not found.");
}

// 1. DATA SYNCHRONIZATION
$display_name = !empty($company['company_name']) ? $company['company_name'] : $company['full_name'];
$description  = !empty($company['bio']) ? $company['bio'] : "No bio available for this company.";
$location     = !empty($company['location']) ? $company['location'] : "Location not listed";
$website      = !empty($company['website']) ? $company['website'] : "#";

// 2. IMAGE LOGIC (Point to uploads1)
$img_src = !empty($company['company_logo']) ? "uploads1/" . $company['company_logo'] : "https://via.placeholder.com/150";
$cover_src = !empty($company['cover_photo']) ? "uploads1/" . $company['cover_photo'] : "https://via.placeholder.com/1200x400?text=Welcome+to+Our+Company";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($display_name); ?> | Profile</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; margin: 0; padding: 0; display: flex; justify-content: center; align-items: flex-start; min-height: 100vh; }
        .profile-container { width: 100%; max-width: 800px; margin: 40px 20px; background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        
        /* Banner & Logo */
        .cover-wrapper { position: relative; width: 100%; height: 250px; background: #dfe6e9; }
        .cover-photo { width: 100%; height: 100%; object-fit: cover; }
        .logo-wrapper { position: absolute; bottom: -60px; left: 40px; }
        .logo { width: 130px; height: 130px; border-radius: 15px; object-fit: cover; border: 6px solid white; background: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        
        /* Content Section */
        .content { padding: 80px 40px 40px 40px; }
        h1 { margin: 0; color: #2c3e50; font-size: 32px; }
        
        /* Quick Facts Grid */
        .facts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 30px; background: #f8f9fa; padding: 25px; border-radius: 12px; }
        .fact-item { display: flex; flex-direction: column; }
        .fact-item label { font-size: 12px; color: #7f8c8d; text-transform: uppercase; font-weight: bold; margin-bottom: 5px; }
        .fact-item span { color: #2c3e50; font-weight: 600; font-size: 15px; }

        .website-link { display: inline-block; margin-top: 15px; color: #3498db; text-decoration: none; font-weight: bold; }
        .website-link:hover { text-decoration: underline; }

        .bio-section { margin-top: 35px; border-top: 1px solid #eee; pt: 25px; }
        .bio-text { line-height: 1.8; color: #444; font-size: 16px; }
        
        .back-btn { display: inline-block; margin-top: 40px; text-decoration: none; color: #95a5a6; font-size: 14px; font-weight: bold; }
    </style>
</head>
<body>

<div class="profile-container">
    <div class="cover-wrapper">
        <img src="<?php echo htmlspecialchars($cover_src); ?>" class="cover-photo">
        <div class="logo-wrapper">
            <img src="<?php echo htmlspecialchars($img_src); ?>" class="logo">
        </div>
    </div>

    <div class="content">
        <h1><?php echo htmlspecialchars($display_name); ?></h1>
        
        <?php if($website !== "#" && !empty($website)): ?>
            <a href="<?php echo htmlspecialchars($website); ?>" target="_blank" class="website-link">🔗 Visit Official Website</a>
        <?php endif; ?>

        <!-- NEW QUICK FACTS SECTION -->
        <div class="facts-grid">
            <div class="fact-item">
                <label>Industry</label>
                <span>🏢 <?php echo htmlspecialchars($company['industry'] ?? 'Not Specified'); ?></span>
            </div>
            <div class="fact-item">
                <label>Company Size</label>
                <span>👥 <?php echo htmlspecialchars($company['company_size'] ?? 'Not Specified'); ?></span>
            </div>
            <div class="fact-item">
                <label>Office Hours</label>
                <span>⏰ <?php echo htmlspecialchars($company['working_hours'] ?? 'Not Specified'); ?></span>
            </div>
            <div class="fact-item">
                <label>Location</label>
                <span>📍 <?php echo htmlspecialchars($location); ?></span>
            </div>
        </div>

        <div class="bio-section">
            <h3>About Us</h3>
            <div class="bio-text">
                <?php echo nl2br(htmlspecialchars($description)); ?>
            </div>
        </div>

        <a href="javascript:history.back()" class="back-btn">← Back to Listings</a>
    </div>
</div>

</body>
</html>