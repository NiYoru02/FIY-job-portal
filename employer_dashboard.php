<?php
session_start();
include "db.php";

// SECURITY CHECK
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header("Location: login_employer.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employer Dashboard</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f7f6; margin: 0; display: flex; }
        
        /* Sidebar Styles */
        .sidebar { width: 260px; background: #2c3e50; color: white; height: 100vh; position: fixed; padding: 20px; }
        .sidebar h2 { color: #2ecc71; font-size: 22px; margin-bottom: 30px; }
        .sidebar a { display: block; color: #bdc3c7; text-decoration: none; padding: 12px 0; border-bottom: 1px solid #34495e; transition: 0.3s; }
        .sidebar a:hover { color: white; padding-left: 10px; }
        
        /* Main Content */
        .main-content { margin-left: 300px; padding: 40px; width: 100%; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        
        /* Dashboard Cards */
        .stats-container { display: flex; gap: 20px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); flex: 1; border-top: 4px solid #2ecc71; }
        .card h3 { margin: 0; color: #7f8c8d; font-size: 14px; text-transform: uppercase; }
        .card p { font-size: 28px; font-weight: bold; margin: 10px 0; color: #2c3e50; }
        
        .logout-btn { background: #e74c3c; color: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; font-size: 14px; }

        /* Table Style */
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; margin-top: 20px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #f8f9fa; color: #7f8c8d; text-transform: uppercase; font-size: 12px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>FIY</h2>
        <p>Logged in as: <br><strong><?php echo $_SESSION['name']; ?></strong></p>
        <hr style="border: 0.5px solid #34495e;">
        <a href="employer_dashboard.php">Dashboard Home</a>
        <a href="post_job.php">Post a New Job</a>
        <a href="manage_jobs.php">Manage Job Posts</a>
        <a href="view_applicants.php">View Applicants</a>
        <a href="logout.php" style="color: #e74c3c; margin-top: 50px;">Logout</a>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Employer Panel</h1>
            <a href="logout.php" class="logout-btn">Log Out</a>
        </div>

        <div class="stats-container">
            <div class="card">
                <h3>Active Job Posts</h3>
                <p>
                    <?php
                    $emp_id = $_SESSION['user_id'];
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM jobs WHERE employer_id = ?");
                    $stmt->execute([$emp_id]);
                    echo $stmt->fetchColumn(); 
                    ?>
                </p>
            </div>
            <div class="card">
                <h3>Total Applicants</h3>
                <p>0</p>
            </div>
            <div class="card">
                <h3>Messages</h3>
                <p>0</p>
            </div>
        </div>

        <?php if(isset($_GET['success'])): ?>
            <p style="color: green; font-weight: bold; margin-top: 20px;">✓ Job posted successfully!</p>
        <?php endif; ?>

        <div style="margin-top: 40px; background: white; padding: 30px; border-radius: 8px;">
            <h2>Quick Start</h2>
            <p>Welcome to your hiring dashboard. Post openings and find your next team member.</p>
            <a href="post_job.php" style="text-decoration: none;">
                <button style="background: #2ecc71; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">
                    + Post a New Job
                </button>
            </a>
        </div>

        <div style="margin-top: 40px;">
            <h2>Your Recent Job Listings</h2>
            <table>
                <thead>
                    <tr>
                        <th>Job Title</th>
                        <th>Location</th>
                        <th>Type</th>
                        <th>Posted Date</th>
                        <th>Actions</th> </tr>
                </thead>
                <tbody>
                    <?php
                    $getJobs = $conn->prepare("SELECT * FROM jobs WHERE employer_id = ? ORDER BY created_at DESC");
                    $getJobs->execute([$emp_id]);
                    $jobs = $getJobs->fetchAll();

                    if($jobs) {
                        foreach($jobs as $job) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($job['job_title']) . "</td>";
                            echo "<td>" . htmlspecialchars($job['location']) . "</td>";
                            echo "<td>" . $job['job_type'] . "</td>";
                            echo "<td>" . date('M d, Y', strtotime($job['created_at'])) . "</td>";
                            echo "<td>
                                    <a href='edit_job.php?id=" . $job['id'] . "' style='color: #3498db; text-decoration: none; margin-right: 10px;'>Edit</a>
                                    <a href='delete_job.php?id=" . $job['id'] . "' style='color: #e74c3c; text-decoration: none;' onclick='return confirm(\"Are you sure?\")'>Delete</a>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        // Changed colspan to 5 to match the new "Actions" column
                        echo "<tr><td colspan='5' style='text-align:center;'>No jobs found. Post one to get started!</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>