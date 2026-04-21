<?php
session_start();
include "db.php";

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header("Location: login_employer.php");
    exit();
}

$message = "";

// 2. Handle Form Submission
if (isset($_POST['post_job'])) {
    $employer_id = $_SESSION['user_id']; // The "Foreign Key" link
    $title = $_POST['job_title'];
    $desc = $_POST['job_description'];
    $salary = $_POST['salary'];
    $location = $_POST['location'];
    $type = $_POST['job_type'];

    // FIRST: Define and Run the SQL (The "Science" part)
    $sql = "INSERT INTO jobs (employer_id, job_title, job_description, salary, location, job_type) 
            VALUES (:eid, :title, :descr, :sal, :loc, :type)";
    
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([
        ':eid' => $employer_id,
        ':title' => $title,
        ':descr' => $desc,
        ':sal' => $salary,
        ':loc' => $location,
        ':type' => $type
    ]);

    // SECOND: Now that $result exists, we check it to redirect
    if ($result) {
        header("Location: employer_dashboard.php?success=1");
        exit();
    } else {
        $message = "<p style='color: red;'>Error posting job.</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Post a New Job</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 50px; }
        .form-container { background: white; padding: 30px; border-radius: 8px; max-width: 600px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input, textarea, select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #2ecc71; color: white; border: none; padding: 12px 20px; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; }
        .back-link { display: block; margin-top: 15px; text-align: center; color: #7f8c8d; text-decoration: none; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Post a New Job</h2>
    <?php echo $message; ?>
    
    <form method="POST">
        <label>Job Title</label>
        <input type="text" name="job_title" placeholder="e.g. Senior Web Developer" required>

        <label>Job Description</label>
        <textarea name="job_description" rows="5" placeholder="Describe the responsibilities..." required></textarea>

        <label>Salary Range</label>
        <input type="text" name="salary" placeholder="e.g. ₱30,000 - ₱40,000">

        <label>Location</label>
        <input type="text" name="location" placeholder="e.g. Taboc">

        <label>Job Type</label>
        <select name="job_type">
            <option value="Full-time">Full-time</option>
            <option value="Part-time">Part-time</option>
            <option value="Freelance">Freelance</option>
            <option value="Contract">Contract</option>
        </select>

        <button type="submit" name="post_job">Publish Job Listing</button>
    </form>

    <a href="employer_dashboard.php" class="back-link">← Cancel and Go Back</a>
</div>

</body>
</html>