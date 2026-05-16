<?php
session_start();
include 'db.php';

// 1. Safety Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Cancellation Logic
if (isset($_POST['cancel_app'])) {
    $app_id = $_POST['app_id'];
    $del = $conn->prepare("DELETE FROM applications WHERE id = ? AND user_id = ?");
    $del->execute([$app_id, $user_id]);
    header("Location: my_applications.php?msg=cancelled");
    exit();
}

// 3. Fetch Applications
$stmt = $conn->prepare("SELECT applications.*, jobs.id AS job_id, jobs.job_title, employers.company_name 
                        FROM applications 
                        JOIN jobs ON applications.job_id = jobs.id 
                        JOIN employers ON jobs.employer_id = employers.id 
                        WHERE applications.user_id = ? 
                        ORDER BY applications.applied_at DESC");
$stmt->execute([$user_id]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Applications | FIY</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; margin: 0; padding: 40px; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        h1 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; background: #3498db; color: white; padding: 15px; }
        td { padding: 15px; border-bottom: 1px solid #eee; color: #444; }

        /* Tracker Link Style */
        .tracker-link { 
            text-decoration: none; 
            color: #2c3e50; 
            transition: 0.3s; 
            display: inline-block;
        }
        .tracker-link:hover { color: #3498db; transform: translateX(5px); }
        .tracker-link strong { border-bottom: 1px dashed #ccc; }
        .tracker-link:hover strong { border-bottom: 1px solid #3498db; }

        .status { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-accepted { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .btn-cancel { background: #e74c3c; color: white; border: none; padding: 6px 12px; border-radius: 5px; cursor: pointer; font-size: 11px; font-weight: bold; }
        .btn-cancel:hover { background: #c0392b; }
        .back-link { text-decoration: none; color: #3498db; font-weight: bold; display: inline-block; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="container">
    <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
    <h1>My Job Applications</h1>

    <?php if (isset($_GET['msg'])): ?>
        <p style="color: #e67e22; font-weight: bold; background: #fff4e5; padding: 10px; border-radius: 5px;">⚠️ Application cancelled successfully.</p>
    <?php endif; ?>

    <?php if (count($applications) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Job Title (Click to View)</th>
                    <th>Company</th>
                    <th>Date Applied</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $app): ?>
                <tr>
                    <td>
                        <!-- This is the Tracker Link -->
                        <a href="job_details.php?id=<?php echo $app['job_id']; ?>" class="tracker-link" title="View Job Details">
                            <strong><?php echo htmlspecialchars($app['job_title']); ?></strong> 🔗
                        </a>
                    </td>
                    <td><?php echo htmlspecialchars($app['company_name']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($app['applied_at'])); ?></td>
                    <td>
                        <span class="status status-<?php echo strtolower($app['status']); ?>">
                            <?php echo htmlspecialchars($app['status']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if (strtolower($app['status']) === 'pending'): ?>
                            <form method="POST" onsubmit="return confirm('Cancel this application?');">
                                <input type="hidden" name="app_id" value="<?php echo $app['id']; ?>">
                                <button type="submit" name="cancel_app" class="btn-cancel">CANCEL</button>
                            </form>
                        <?php else: ?>
                            <small style="color: #95a5a6;">N/A</small>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div style="text-align: center; padding: 50px;">
            <p style="color: #7f8c8d; font-size: 18px;">You haven't applied for any jobs yet.</p>
            <a href="available_jobs.php" style="color: #3498db; text-decoration: none; font-weight: bold;">Browse Available Jobs</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>