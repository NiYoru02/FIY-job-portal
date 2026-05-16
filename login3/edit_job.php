<?php
session_start();
include "db.php";

// 1. Security & ID Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header("Location: login_employer.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: employer_dashboard.php");
    exit();
}

$job_id = $_GET['id'];
$employer_id = $_SESSION['user_id'];
$message = "";

// 2. Fetch the existing job data to fill the form
$stmt = $conn->prepare("SELECT * FROM jobs WHERE id = ? AND employer_id = ?");
$stmt->execute([$job_id, $employer_id]);
$job = $stmt->fetch();

if (!$job) {
    die("Job not found or access denied.");
}

// 3. Handle the Update (The "Push")
if (isset($_POST['update_job'])) {
    $title = $_POST['job_title'];
    $desc = $_POST['job_description'];
    $salary = $_POST['salary'];
    $location = $_POST['location'];
    $type = $_POST['job_type'];

    $sql = "UPDATE jobs SET job_title = :title, job_description = :descr, 
            salary = :sal, location = :loc, job_type = :type 
            WHERE id = :id AND employer_id = :eid";
    
    $updateStmt = $conn->prepare($sql);
    $result = $updateStmt->execute([
        ':title' => $title,
        ':descr' => $desc,
        ':sal' => $salary,
        ':loc' => $location,
        ':type' => $type,
        ':id' => $job_id,
        ':eid' => $employer_id
    ]);

    if ($result) {
        header("Location: employer_dashboard.php?updated=1");
        exit();
    } else {
        $message = "<p style='color: red;'>Error updating job.</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Job Listing</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 50px; }
        .form-container { background: white; padding: 30px; border-radius: 8px; max-width: 600px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input, textarea, select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #3498db; color: white; border: none; padding: 12px 20px; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; }
        .back-link { display: block; margin-top: 15px; text-align: center; color: #7f8c8d; text-decoration: none; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Edit Job Listing</h2>
    <?php echo $message; ?>
    
    <form method="POST">
        <label>Job Title</label>
        <input type="text" name="job_title" value="<?php echo htmlspecialchars($job['job_title']); ?>" required>

        <label>Job Description</label>
        <textarea name="job_description" rows="5" required><?php echo htmlspecialchars($job['job_description']); ?></textarea>

        <label>Salary Range</label>
        <input type="text" name="salary" value="<?php echo htmlspecialchars($job['salary']); ?>">

        <label>Location</label>
        <input type="text" name="location" value="<?php echo htmlspecialchars($job['location']); ?>">

        <label>Job Type</label>
        <select name="job_type">
            <option value="Full-time" <?php if($job['job_type'] == 'Full-time') echo 'selected'; ?>>Full-time</option>
            <option value="Part-time" <?php if($job['job_type'] == 'Part-time') echo 'selected'; ?>>Part-time</option>
            <option value="Freelance" <?php if($job['job_type'] == 'Freelance') echo 'selected'; ?>>Freelance</option>
            <option value="Contract" <?php if($job['job_type'] == 'Contract') echo 'selected'; ?>>Contract</option>
        </select>

        <button type="submit" name="update_job">Save Changes</button>
    </form>

    <a href="employer_dashboard.php" class="back-link">← Back to Dashboard</a>
</div>

</body>
</html>