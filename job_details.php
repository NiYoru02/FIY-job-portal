<?php
session_start();
include 'db.php';

$job_id = $_GET['id'] ?? null;

if (!$job_id) {
    header("Location: available_jobs.php");
    exit();
}

try {
    // Fetch ONLY the specific job clicked
    $sql = "SELECT j.*, e.full_name AS official_company 
            FROM jobs j
            JOIN employers e ON j.employer_id = e.id 
            WHERE j.id = :id";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $job_id]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$job) {
        die("Job not found.");
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($job['job_title']); ?> | Details</title>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f7f6; padding: 40px; }
        .details-container { max-width: 700px; background: white; margin: auto; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; margin-bottom: 5px; }
        .meta { color: #7f8c8d; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
        .description { line-height: 1.8; color: #34495e; }
        .btn-group { margin-top: 30px; }
        .apply-now { background: #27ae60; color: white; padding: 12px 25px; text-decoration: none; border-radius: 6px; font-weight: bold; }
    </style>
</head>
<body>

<div class="details-container">
    <a href="available_jobs.php" style="text-decoration: none; color: #3498db;">← Back to List</a>
    
    <h1><?= htmlspecialchars($job['job_title']); ?></h1>
    <p class="meta">
        🏢 <strong><?= htmlspecialchars($job['official_company']); ?></strong> | 
        📍 <?= htmlspecialchars($job['location']); ?> | 
        💰 <?= htmlspecialchars($job['salary']); ?>
    </p>

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