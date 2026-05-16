<?php
session_start();
include "db.php";

try {
    // We are pulling 'full_name' from the employers table (e)
    $sql = "SELECT j.*, e.full_name AS official_company 
            FROM jobs j
            JOIN employers e ON j.employer_id = e.id 
            ORDER BY j.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $jobs = [];
    echo "<div style='color:red; padding:20px;'>Connection failed: " . $e->getMessage() . "</div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Jobs | FIY Portal</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; margin: 0; padding: 40px; }
        .container { max-width: 900px; margin: auto; }
        .job-card { 
            background: white; 
            padding: 25px; 
            margin-bottom: 20px; 
            border-radius: 12px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border-left: 5px solid #2c3e50;
        }
        .job-header { display: flex; justify-content: space-between; align-items: flex-start; }
        .job-title { color: #2c3e50; margin: 0; font-size: 1.5rem; }
        .company { color: #7f8c8d; font-weight: 600; margin-top: 5px; }
        .details { display: flex; gap: 20px; margin: 15px 0; color: #95a5a6; font-size: 0.9rem; }
        .salary { color: #2ecc71; font-weight: bold; }
        .description { line-height: 1.6; color: #34495e; margin: 15px 0; }
        .apply-btn { 
            display: inline-block; 
            background: #2c3e50; 
            color: white; 
            padding: 10px 25px; 
            text-decoration: none; 
            border-radius: 6px; 
            transition: background 0.3s;
        }
        .apply-btn:hover { background: #34495e; }
    </style>
</head>
<body>

<body>
    <div class="container">
        <div style="margin-bottom: 30px;">
            <a href="available_jobs.php" class="back-btn">← Back to browsing jobs</a>
        </div>

    <div class="container">
        <h1>Available Opportunities</h1>
        
        <?php foreach ($jobs as $job): ?>
            <div class="job-card">
                <div class="job-header">
                    <div>
                        <h2 class="job-title"><?php echo htmlspecialchars($job['job_title']); ?></h2>
                       <div class="company">🏢 <?php echo htmlspecialchars($job['official_company']); ?></div>
                    </div>
                   <div class="salary">₱<?php echo htmlspecialchars($job['salary']); ?></div>
                </div>

                <div class="details">
                    <span>📍 <?php echo htmlspecialchars($job['location']); ?></span>
                    <span>🕒 <?php echo htmlspecialchars($job['job_type']); ?></span>
                </div>

                <div class="description">
                    <?php echo nl2br(htmlspecialchars($job['job_description'])); ?>
                </div>

                <a href="apply_process.php?id=<?php echo $job['id']; ?>" class="apply-btn">Apply Now</a>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>