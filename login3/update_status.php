<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    die("Unauthorized access.");
}

$app_id = $_GET['id'] ?? null;
$new_status = $_GET['status'] ?? null;
$custom_reason = $_GET['reason'] ?? null; // Catch the reason from the URL

if ($app_id && $new_status) {
    try {
        // 1. Update the application status
        $sql = "UPDATE applications SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$new_status, $app_id]);

        // 2. Automated Chat Notification logic
        $infoStmt = $conn->prepare("
            SELECT applications.user_id, jobs.job_title 
            FROM applications 
            JOIN jobs ON applications.job_id = jobs.id 
            WHERE applications.id = ?
        ");
        $infoStmt->execute([$app_id]);
        $appData = $infoStmt->fetch(PDO::FETCH_ASSOC);

        if ($appData) {
            $seeker_id = $appData['user_id'];
            $job_title = $appData['job_title'];
            $employer_id = $_SESSION['user_id'];

            if ($new_status === 'Accepted') {
                $msg_text = "🎉 Congratulations! Your application for '$job_title' has been ACCEPTED. We will contact you soon for the next steps.";
            } else {
                // Use the custom reason if provided, otherwise use a default
                $reason_text = !empty($custom_reason) ? $custom_reason : "We have decided to move forward with other candidates.";
                $msg_text = "Status Update for '$job_title': We regret to inform you that your application has been REJECTED. \n\nReason: " . $reason_text;
            }

            // Insert into chat table
            $msgStmt = $conn->prepare("
                INSERT INTO messages (sender_id, sender_role, receiver_id, receiver_role, message_text, is_read) 
                VALUES (?, 'employer', ?, 'seeker', ?, 0)
            ");
            $msgStmt->execute([$employer_id, $seeker_id, $msg_text]);
        }

        header("Location: view_applicants.php?msg=updated");
        exit();

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>