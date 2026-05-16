<?php
session_start();
include 'db.php';

$job_id = $_GET['id'] ?? null;

if (!$job_id) {
    header("Location: available_jobs.php");
    exit();
}

try {
    // We join 'employers' to get both the name and the specific employer ID
    $sql = "SELECT j.*, e.full_name AS official_company, e.company_name, j.employer_id 
            FROM jobs j
            JOIN employers e ON j.employer_id = e.id 
            WHERE j.id = :id";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $job_id]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$job) {
        die("Job not found.");
    }

    // Determine which name to show (Company Name preferred over Full Name)
    $display_company = !empty($job['company_name']) ? $job['company_name'] : $job['official_company'];

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($job['job_title']); ?> | Details</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f7f6; padding: 40px; margin: 0; }
        .details-container { max-width: 750px; background: white; margin: auto; padding: 40px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .back-link { text-decoration: none; color: #3498db; font-size: 14px; font-weight: bold; margin-bottom: 20px; display: inline-block; }
        
        h1 { color: #2c3e50; margin: 10px 0; font-size: 28px; }
        
        .meta { 
            background: #f9f9f9; 
            padding: 15px; 
            border-radius: 8px; 
            color: #7f8c8d; 
            margin-bottom: 25px; 
            display: flex; 
            flex-wrap: wrap; 
            gap: 15px;
            font-size: 14px;
        }

        /* Company Link Aesthetic */
        .comp-tag {
            background: #e8f4fd;
            color: #3498db;
            padding: 4px 10px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
        }
        .comp-tag:hover {
            background: #3498db;
            color: white;
        }

        .description { line-height: 1.8; color: #34495e; border-top: 1px solid #eee; padding-top: 20px; }
        .description h3 { color: #2c3e50; }
        
        .btn-group { margin-top: 40px; text-align: center; }
        .apply-now { 
            background: #2ecc71; 
            color: white; 
            padding: 15px 40px; 
            text-decoration: none; 
            border-radius: 30px; 
            font-weight: bold; 
            font-size: 18px;
            box-shadow: 0 4px 10px rgba(46, 204, 113, 0.3);
            transition: 0.3s;
        }
        .apply-now:hover { background: #27ae60; transform: translateY(-2px); }
    </style>
</head>
<body>

<div class="details-container">
    <a href="available_jobs.php" class="back-link">← Back to Job Postings</a>
    
    <h1><?= htmlspecialchars($job['job_title']); ?></h1>
    
    <div class="meta">
        <span>🏢 <strong><a href="view_company.php?id=<?= $job['employer_id']; ?>" class="comp-tag"><?= htmlspecialchars($display_company); ?></a></strong></span>
        <span>📍 <?= htmlspecialchars($job['location']); ?></span>
        <span>💰 <?= htmlspecialchars($job['salary']); ?></span>
        <span>⏱ <?= htmlspecialchars($job['job_type']); ?></span>
    </div>

    <div class="description">
        <h3>Job Description</h3>
        <p><?= nl2br(htmlspecialchars($job['job_description'])); ?></p>
    </div>

    <div class="btn-group">
        <a href="apply_process.php?id=<?= $job['id']; ?>" class="apply-now">Confirm Application</a>
    </div>
</div>

</body>
</html>