<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header("Location: login_employer.php");
    exit();
}

$emp_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Job Posts</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f7f6; margin: 0; display: flex; }
        .sidebar { width: 260px; background: #2c3e50; color: white; height: 100vh; position: fixed; padding: 20px; }
        .sidebar h2 { color: #2ecc71; }
        .sidebar a { display: block; color: #bdc3c7; text-decoration: none; padding: 12px 0; border-bottom: 1px solid #34495e; }
        .main-content { margin-left: 300px; padding: 40px; width: 100%; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; color: #7f8c8d; text-transform: uppercase; font-size: 12px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>FIY</h2>
        <p>Logged in as: <br><strong><?php echo $_SESSION['name']; ?></strong></p>
        <hr style="border: 0.5px solid #34495e;">
        <a href="employer_dashboard.php">Dashboard Home</a>
        <a href="post_job.php">Post a New Job</a>
        <a href="manage_jobs.php" style="color: white;">Manage Job Posts</a>
        <a href="view_applicants.php">View Applicants</a>
        <a href="logout.php" style="color: #e74c3c; margin-top: 50px;">Logout</a>
    </div>

    <div class="main-content">
        <h1>Manage Your Listings</h1>
        <table>
            <thead>
                <tr>
                    <th>Job Title</th>
                    <th>Location</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->prepare("SELECT * FROM jobs WHERE employer_id = ? ORDER BY created_at DESC");
                $stmt->execute([$emp_id]);
                $jobs = $stmt->fetchAll();

                foreach($jobs as $job) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($job['job_title']) . "</td>";
                    echo "<td>" . htmlspecialchars($job['location']) . "</td>";
                    echo "<td>" . $job['job_type'] . "</td>";
                    echo "<td>
                            <a href='edit_job.php?id=" . $job['id'] . "' style='color: #3498db; text-decoration: none; margin-right: 10px;'>Edit</a>
                            <a href='delete_job.php?id=" . $job['id'] . "' style='color: #e74c3c; text-decoration: none;' onclick='return confirm(\"Delete this post?\")'>Delete</a>
                          </td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>