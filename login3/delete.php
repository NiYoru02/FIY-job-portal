<?php
include 'db.php'; // 1. Use the correct connection file

// 2. Get the ID from the URL link
$id = $_GET['id'];

try {
    // 3. Use a Prepared Statement (PDO style)
    // This is safer than putting $id directly in the query
    $stmt = $conn->prepare("DELETE FROM job_seekers WHERE id = :id");
    
    // 4. Run the command
    $stmt->execute([':id' => $id]);

    // 5. Go back to the dashboard immediately
    header("Location: dashboard.php");
    exit();

} catch(PDOException $e) {
    echo "Delete failed: " . $e->getMessage();
}
?>