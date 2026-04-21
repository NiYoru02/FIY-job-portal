<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header("Location: login_employer.php");
    exit();
}

$emp_id = $_SESSION['user_id'];

// s.fullname matches your job_seekers table
// a.user_id matches your applications table
$sql = "SELECT a.*, j.job_title, s.fullname as applicant_name, s.email as applicant_email 
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        JOIN job_seekers s ON a.user_id = s.id 
        WHERE j.employer_id = ?
        ORDER BY a.applied_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute([$emp_id]);
$applicants = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Applicants</title>
    <style>
        /* Same sidebar and layout CSS as above */
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f7f6; margin: 0; display: flex; }
        .sidebar { width: 260px; background: #2c3e50; color: white; height: 100vh; position: fixed; padding: 20px; }
        .main-content { margin-left: 300px; padding: 40px; width: 100%; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; color: #7f8c8d; }
        .status-badge { background: #f1c40f; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>FIY</h2>
        <a href="employer_dashboard.php" style="color: #bdc3c7; text-decoration: none;">← Back to Dashboard</a>
    </div>

    <div class="main-content">
        <h1>Applicants</h1>
        <table>
            <thead>
                <tr>
                    <th>Applicant</th>
                    <th>Email</th>
                    <th>Applied For</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if($applicants): ?>
                    <?php foreach($applicants as $app): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($app['applicant_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($app['applicant_email']); ?></td>
                            <td><?php echo htmlspecialchars($app['job_title']); ?></td>
                            <td><?php echo date('M d', strtotime($app['applied_at'])); ?></td>
                            <td><span class="status-badge"><?php echo $app['status']; ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center;">No one has applied yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>