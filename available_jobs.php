<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    // Fetch all active jobs (Assuming your table is called 'jobs')
    $stmt = $conn->prepare("SELECT * FROM jobs ORDER BY created_at DESC");
    $stmt->execute();
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching jobs: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Available Jobs</title>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f7f6; padding: 20px; }
        .container { max-width: 900px; margin: auto; }
        .job-card { 
            background: white; 
            padding: 20px; 
            margin-bottom: 15px; 
            border-radius: 8px; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .job-info h3 { margin: 0; color: #2c3e50; }
        .job-info p { margin: 5px 0; color: #7f8c8d; font-size: 14px; }
        .apply-btn { 
            background: #3498db; 
            color: white; 
            padding: 10px 20px; 
            text-decoration: none; 
            border-radius: 5px; 
            font-weight: bold;
        }
        .apply-btn:hover { background: #2980b9; }
        .badge { background: #e1f5fe; color: #039be5; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
    </style>
</head>
<body>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h2>Explore Opportunities</h2>
        <a href="dashboard.php" style="text-decoration: none; color: #3498db;">← My Dashboard</a>
    </div>

    <?php if (count($jobs) > 0): ?>
        <?php foreach ($jobs as $job): ?>
            <div class="job-card">
                <div class="job-info">
                    <h3><?= htmlspecialchars($job['job_title']); ?></h3>
                    <p><strong>Company:</strong> <?= htmlspecialchars($job['company_name'] ?? 'Direct Hire'); ?></p>
                    <p><span class="badge"><?= htmlspecialchars($job['job_type'] ?? 'Full-time'); ?></span> • <?= htmlspecialchars($job['location'] ?? 'Remote'); ?></p>
                </div>
                <div>
                    <a href="apply_process.php?job_id=<?= $job['id']; ?>" class="apply-btn">Apply Now</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align: center; color: #7f8c8d;">No jobs available at the moment. Check back later!</p>
    <?php endif; ?>
</div>

</body>
</html>