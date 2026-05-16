<?php
session_start();
include 'db.php';

// 1. IMPROVED SECURITY: Just check if they are logged in.
// We block them only if they are an 'employer'.
if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'employer')) {
    echo "<script>alert('Only job seekers can apply for jobs!'); window.location.href='job_list.php';</script>";
    exit();
}

// 2. Sync the ID (Matching the ?id= in your link)
$job_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'] ?? ''; 

if (!$job_id) {
    header("Location: job_list.php");
    exit();
}

try {
    // 3. Duplicate Check: Prevent double applications
    $checkApp = $conn->prepare("SELECT id FROM applications WHERE job_id = :job_id AND user_id = :user_id");
    $checkApp->execute([':job_id' => $job_id, ':user_id' => $user_id]);

   if ($checkApp->rowCount() > 0) {
        echo "<script>alert('You have already applied for this position!'); window.history.back();</script>";
    } else {
        // 4. Success: Save the application
        $sql = "INSERT INTO applications (job_id, user_id, status) VALUES (:job_id, :user_id, 'Pending')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':job_id' => $job_id, ':user_id' => $user_id]);
        
        // This is the only one you need! 
        echo "<script>
            alert('Application sent successfully!');
            window.history.back(); 
        </script>";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

?>