<?php
session_start();
include "db.php";

if (isset($_GET['id'])) {
    $job_id = $_GET['id'];
    $emp_id = $_SESSION['user_id'];

    try {
        // 1. Delete the "Child" records first (The Applications)
        $sql1 = "DELETE FROM applications WHERE job_id = ?";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->execute([$job_id]);

        // 2. Now you can safely delete the "Parent" record (The Job)
        $sql2 = "DELETE FROM jobs WHERE id = ? AND employer_id = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->execute([$job_id, $emp_id]);

    } catch (PDOException $e) {
        // If something goes wrong, it won't crash the whole site
        die("Error: " . $e->getMessage());
    }
}

header("Location: employer_dashboard.php");
exit();
?>