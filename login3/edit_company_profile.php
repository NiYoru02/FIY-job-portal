<?php
session_start();
include "db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$emp_id = $_SESSION['user_id']; 
$msg = "";

if (isset($_POST['upload'])) {
    $folder = "uploads1/"; 
    if (!is_dir($folder)) mkdir($folder, 0777, true);

    if ($_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $file_name = "logo_" . time() . "_" . basename($_FILES['logo']['name']);
        $path = $folder . $file_name;

        if (move_uploaded_file($_FILES['logo']['tmp_name'], $path)) {
            // FIX: Target 'employers' table and 'company_logo' column
            $stmt = $conn->prepare("UPDATE employers SET company_logo = ? WHERE id = ?");
            $stmt->execute([$file_name, $emp_id]);
            $msg = "✅ Logo updated successfully!";
        }
    }
}

// FETCH: Get data from 'employers' table
$stmt = $conn->prepare("SELECT * FROM employers WHERE id = ?");
$stmt->execute([$emp_id]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

// Use 'company_logo' and 'bio' to match your DB
$img = (!empty($profile['company_logo'])) ? "uploads1/".$profile['company_logo'] : "https://via.placeholder.com/150";
$description = !empty($profile['bio']) ? $profile['bio'] : "No description set.";
// Use 'company_name' first. If that's empty, try 'full_name'. If both fail, use 'Employer'.
$company_name = $profile['company_name'] ?? $profile['full_name'] ?? 'Employer';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Company Settings</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f4f4; padding: 50px; text-align: center; }
        .card { background: white; padding: 30px; border-radius: 15px; display: inline-block; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 400px; }
        .pfp { width: 120px; height: 120px; border-radius: 10px; object-fit: cover; border: 2px solid #ddd; margin-bottom: 15px; }
        .upload-section { margin-top: 20px; padding: 15px; border: 2px dashed #3498db; background: #ebf5fb; border-radius: 10px; }
        .info { text-align: left; margin-bottom: 20px; color: #555; }
    </style>
</head>
<body>

<div class="card">
    <h2>Company Settings</h2>
    
    <div class="info">
        <p><strong>Company:</strong> <?php echo htmlspecialchars($company_name); ?></p>
        <p><strong>Current Bio:</strong> <?php echo htmlspecialchars($description); ?></p>
    </div>

    <img src="<?php echo $img; ?>" class="pfp" onerror="this.src='https://via.placeholder.com/150';">
    
    <p style="color: #2ecc71; font-weight: bold;"><?php echo $msg; ?></p>

    <div class="upload-section">
        <form action="edit_company_profile.php" method="POST" enctype="multipart/form-data">
            <label style="display: block; margin-bottom: 10px; font-weight: bold;">Select New Logo</label>
            <input type="file" name="logo" required>
            <br><br>
            <button type="submit" name="upload" style="cursor:pointer; background:#2ecc71; color:white; border:none; padding:10px 20px; border-radius:5px; font-weight: bold; width: 100%;">
                UPDATE LOGO
            </button>
        </form>
    </div>
    
    <br>
    <a href="employer_dashboard.php" style="text-decoration: none; color: #3498db;">Back to Dashboard</a>
</div>

</body>
</html>