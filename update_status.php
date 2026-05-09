<?php
session_start();
include "db.php";

// 1. Security Check
// If you are testing in one browser, this is why it says "Unauthorized"
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    // Pro-tip: If this keeps failing during testing, 
    // temporarily comment out the line below with // to finish your work.
    die("Unauthorized access. Your current role is: " . ($_SESSION['role'] ?? 'None'));
}

// 2. Get the data from the URL 
$app_id = $_GET['id'] ?? null;
$new_status = $_GET['status'] ?? null;

if ($app_id && $new_status) {
    try {
        // 3. Update the specific application
        $sql = "UPDATE applications SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$new_status, $app_id]);

        // 4. Redirect back to the list
        header("Location: view_applicants.php?msg=updated");
        exit();

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Missing information.";
}
?>