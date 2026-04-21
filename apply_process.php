<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("Please log in first.");
}

// Ensure job_id is present
if (!isset($_GET['job_id'])) {
    header("Location: available_jobs.php");
    exit();
}

$job_id = $_GET['job_id'];
$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'] ?? ''; // Using the session email we fixed!

try {
    // 1. CRITICAL CHECK: Does the user have a resume?
    $checkResume = $conn->prepare("SELECT id FROM job_seekers WHERE email = :email");
    $checkResume->execute([':email' => $email]);

    if ($checkResume->rowCount() == 0) {
        echo "<script>alert('Please create your resume/profile first before applying!'); window.location.href='add.php';</script>";
        exit();
    }

    // 2. CHECK: Have they already applied?
    $checkApp = $conn->prepare("SELECT * FROM applications WHERE job_id = :job_id AND user_id = :user_id");
    $checkApp->execute([':job_id' => $job_id, ':user_id' => $user_id]);

    if ($checkApp->rowCount() > 0) {
        echo "<script>alert('You already applied for this!'); window.location.href='available_jobs.php';</script>";
    } else {
        // 3. SUCCESS: Insert the application
        $sql = "INSERT INTO applications (job_id, user_id, status) VALUES (:job_id, :user_id, 'Pending')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':job_id' => $job_id, ':user_id' => $user_id]);
        
        echo "<script>alert('Application sent successfully!'); window.location.href='available_jobs.php';</script>";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>