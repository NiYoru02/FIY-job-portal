<?php
session_start();
include 'db.php';

// 1. Check if the user is actually an employer
if ($_SESSION['role'] != 'employer') {
    die("Access Denied. Only employers can post jobs.");
}

// 2. Capture the data
$title = $_POST['title'];
$company = $_POST['company'];
$location = $_POST['location'];
$description = $_POST['description'];
$employer_id = $_SESSION['user_id']; // This connects the job to the user who posted it

try {
    // 3. The SQL Blueprint
    $sql = "INSERT INTO jobs (employer_id, title, company, location, description) 
            VALUES (:employer_id, :title, :company, :location, :description)";
    
    $stmt = $conn->prepare($sql);
    
    // 4. The Handover (Execution)
    $stmt->execute([
        ':employer_id' => $employer_id,
        ':title' => $title,
        ':company' => $company,
        ':location' => $location,
        ':description' => $description
    ]);

    echo "Job posted successfully! <a href='dashboard.php'>Back to Dashboard</a>";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>