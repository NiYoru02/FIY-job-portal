<?php
ob_start(); 
session_start();
include 'db.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) { 
    header("Location: login.php"); 
    exit(); 
}

// 1. Fetch current data
$stmt = $conn->prepare("SELECT * FROM job_seekers WHERE id = ?");
$stmt->execute([$user_id]);
$existing = $stmt->fetch() ?: [];

// --- SAFETY CHECK FOR OLD DATA ---
$current_exp = json_decode($existing['experience'] ?? '[]', true);
// If it's not an array (old data), wrap the old string in an array
if (!is_array($current_exp)) {
    $current_exp = !empty($existing['experience']) ? [$existing['experience']] : [];
}

$current_edu = json_decode($existing['education'] ?? '[]', true);
if (!is_array($current_edu)) {
    $current_edu = !empty($existing['education']) ? [$existing['education']] : [];
}
// ---------------------------------

if (isset($_POST['submit_profile'])) {
    $profile_pic = $existing['profile_pic'] ?? 'default.png';
    $resume_file = $existing['resume_file'] ?? null;

    if (!is_dir('uploads/')) { mkdir('uploads/', 0777, true); }

    if (!empty($_FILES['profile_pic']['name'])) {
        $pic_ext = pathinfo($_FILES["profile_pic"]["name"], PATHINFO_EXTENSION);
        $profile_pic = "thumb_" . $user_id . "_" . time() . "." . $pic_ext;
        move_uploaded_file($_FILES["profile_pic"]["tmp_name"], "uploads/" . $profile_pic);
    }

    if (!empty($_FILES['resume_file']['name'])) {
        $file_ext = strtolower(pathinfo($_FILES["resume_file"]["name"], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx'];
        if (in_array($file_ext, $allowed)) {
            if ($resume_file && file_exists("uploads/" . $resume_file)) { unlink("uploads/" . $resume_file); }
            $resume_file = "cv_" . $user_id . "_" . time() . "." . $file_ext;
            move_uploaded_file($_FILES["resume_file"]["tmp_name"], "uploads/" . $resume_file);
        }
    }
// 1. Combine Title and Date into your array
$combined_exp = [];
if (isset($_POST['experience'])) {
    foreach ($_POST['experience'] as $key => $title) {
        $date = $_POST['exp_date'][$key] ?? '';
        $combined_exp[] = $title . ($date ? " | " . $date : "");
    }
}

// 2. Encode it
$experience_json = json_encode($combined_exp);

$education_json = json_encode($_POST['education'] ?? []);

    $sql = "UPDATE job_seekers SET 
            fullname = ?, phone = ?, skills = ?, bio = ?, 
            education = ?, experience = ?, profile_pic = ?, resume_file = ? 
            WHERE id = ?";
    
  $stmt = $conn->prepare($sql);
    $stmt->execute([
        $_POST['fullname'], $_POST['phone'], $_POST['skills'], $_POST['bio'],
        $education_json, $experience_json, $profile_pic, $resume_file, $user_id
    ]);
    
    // REDIRECT MUST STAY HERE
    header("Location: dashboard.php"); 
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit My Profile</title>
    <style>
        :root { --blue: #3498db; --green: #2ecc71; --dark: #2c3e50; --red: #e74c3c; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; padding: 20px; }
        .editor-container { background: white; max-width: 700px; margin: 20px auto; padding: 40px; border-radius: 15px; box-shadow: 0 5px 25px rgba(0,0,0,0.1); }
        .current-pic-container { text-align: center; margin-bottom: 20px; }
        .profile-preview { width: 150px; height: 150px; object-fit: cover; border-radius: 50%; border: 4px solid var(--blue); padding: 5px; }
        .upload-card { background: #f8f9fa; padding: 20px; border: 2px dashed #cbd5e0; border-radius: 10px; margin: 20px 0; }
        label { display: block; margin-top: 15px; font-weight: bold; color: #4a5568; }
        input[type="text"], textarea { width: 100%; padding: 12px; margin-top: 5px; border: 1px solid #e2e8f0; border-radius: 8px; box-sizing: border-box; }
        
        /* New Styles for the rows */
        .dynamic-row { display: flex; gap: 10px; margin-bottom: 10px; align-items: center; }
        .btn-remove { background: var(--red); color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; }
        .btn-add { background: var(--blue); color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; margin-bottom: 20px; }
        .btn-save { width: 100%; padding: 15px; background: var(--green); color: white; border: none; border-radius: 8px; font-size: 18px; font-weight: bold; cursor: pointer; margin-top: 25px; }
    </style>
</head>
<body>

<div class="editor-container">
    <h2>Edit Your Profile</h2>
    <form method="POST" enctype="multipart/form-data">
        
        <div class="current-pic-container">
            <?php $picPath = !empty($existing['profile_pic']) ? "uploads/".$existing['profile_pic'] : "uploads/default.png"; ?>
            <img src="<?php echo $picPath; ?>" class="profile-preview">
            <label>Update Profile Photo</label>
            <input type="file" name="profile_pic" accept="image/*">
        </div>

        <label>Full Name</label>
        <input type="text" name="fullname" value="<?php echo htmlspecialchars($existing['fullname'] ?? ''); ?>" required>

        <div class="upload-card">
            <label>📄 Upload CV/Resume (PDF/DOCX)</label>
            <input type="file" name="resume_file" accept=".pdf,.doc,.docx">
            <?php if (!empty($existing['resume_file'])): ?>
                <div style="margin-top:10px; font-size:13px;">Current: <?php echo $existing['resume_file']; ?></div>
            <?php endif; ?>
        </div>

        <!-- EXPERIENCE SECTION -->
<!-- EXPERIENCE SECTION -->
<label>Work Experience</label>
<div id="exp-container">
    <?php if(empty($current_exp)): ?>
        <div class="dynamic-row">
            <input type="text" name="experience[]" placeholder="Job Title" style="flex: 2;">
            <input type="text" name="exp_date[]" placeholder="Years (e.g. 2022-2024)" style="flex: 1;">
        </div>
    <?php else: foreach($current_exp as $exp): 
        $parts = explode(" | ", $exp);
        $title = $parts[0];
        $date = $parts[1] ?? ''; 
    ?>
        <div class="dynamic-row">
            <input type="text" name="experience[]" value="<?php echo htmlspecialchars($title); ?>" style="flex: 2;">
            <input type="text" name="exp_date[]" value="<?php echo htmlspecialchars($date); ?>" style="flex: 1;">
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()">✖</button>
        </div>
    <?php endforeach; endif; ?>
</div> <!-- THIS IS WHERE THE DIV ENDS -->

<button type="button" class="btn-add" onclick="addRow('exp-container', 'experience[]')">+ Add More Experience</button>
        <!-- EDUCATION SECTION -->
        <label>Education</label>
        <div id="edu-container">
            <?php if(empty($current_edu)): ?>
                <div class="dynamic-row"><input type="text" name="education[]" placeholder="e.g. BS in Computer Engineering"></div>
            <?php else: foreach($current_edu as $edu): ?>
                <div class="dynamic-row">
                    <input type="text" name="education[]" value="<?php echo htmlspecialchars($edu); ?>">
                    <button type="button" class="btn-remove" onclick="this.parentElement.remove()">✖</button>
                </div>
            <?php endforeach; endif; ?>
        </div>
        <button type="button" class="btn-add" onclick="addRow('edu-container', 'education[]')">+ Add More Education</button>

        <label>Technical Skills</label>
        <textarea name="skills" rows="3"><?php echo htmlspecialchars($existing['skills'] ?? ''); ?></textarea>
        
        <label>Phone Number</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($existing['phone'] ?? ''); ?>">

        <label>Short Bio</label>
        <textarea name="bio" rows="3"><?php echo htmlspecialchars($existing['bio'] ?? ''); ?></textarea>

        <button type="submit" name="submit_profile" class="btn-save">Save & Return to Dashboard</button>
    </form>
</div>

<script>
function addRow(containerId, fieldName) {
    const container = document.getElementById(containerId);
    const div = document.createElement('div');
    div.className = 'dynamic-row';
    
    // If it's experience, we give it two inputs. 
    // Otherwise (for education), we keep it as one.
    if(containerId === 'exp-container') {
        div.innerHTML = `
            <input type="text" name="experience[]" placeholder="Job Title" style="flex: 2;">
            <input type="text" name="exp_date[]" placeholder="Years (e.g. 2022-2024)" style="flex: 1;">
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()">✖</button>
        `;
    } else {
        div.innerHTML = `
            <input type="text" name="${fieldName}" placeholder="Add more...">
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()">✖</button>
        `;
    }
    container.appendChild(div);
}
</script>

</body>
</html>