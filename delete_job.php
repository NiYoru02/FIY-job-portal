<?php
session_start();
include "db.php";

if (isset($_GET['id'])) {
    $job_id = $_GET['id'];
    $emp_id = $_SESSION['user_id'];

    // We check employer_id to make sure you can't delete someone else's job!
    $sql = "DELETE FROM jobs WHERE id = ? AND employer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$job_id, $emp_id]);
}

header("Location: employer_dashboard.php");
exit();
?>