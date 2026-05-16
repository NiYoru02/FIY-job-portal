<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


// 1. Get the search term from the URL
// 1. Get search and type from URL
$search = isset($_GET['search']) ? $_GET['search'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';

try {
    // Start building the query
    $query = "SELECT j.*, e.full_name AS official_company 
              FROM jobs j
              JOIN employers e ON j.employer_id = e.id WHERE 1=1";
    $params = [];

    // If there is a text search
    if ($search !== '') {
        $query .= " AND (j.job_title LIKE ? OR j.location LIKE ? OR e.full_name LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    // If a specific job type chip is clicked
    if ($type !== '') {
        $query .= " AND j.job_type = ?";
        $params[] = $type;
    }

    $query .= " ORDER BY j.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Jobs | FIY Portal</title>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f7f6; padding: 20px; }
        .container { max-width: 900px; margin: auto; }
        
        /* Search Bar Styling */
        .search-section { 
            background: white; 
            padding: 20px; 
            border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
            margin-bottom: 25px;
        }
        .search-form { display: flex; gap: 10px; }
        .search-input { flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 6px; outline: none; }
        
        /* Job Card Styling */
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
        
        /* Buttons */
        .btn-blue { background: #3498db; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; border: none; cursor: pointer; }
        .btn-blue:hover { background: #2980b9; }
        .btn-clear { background: #eee; color: #333; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-size: 14px; }
        
        .badge { background: #e1f5fe; color: #039be5; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
    </style>
</head>
<body>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Explore Opportunities</h2>
        
        <a href="dashboard.php" style="text-decoration: none; color: #3498db; font-weight: bold;">← Back to Dashboard</a>
        <!-- SEARCH & FILTER SECTION -->
<div class="search-section">
    <form action="available_jobs.php" method="GET" class="search-form">
        <input type="text" name="search" class="search-input" 
               placeholder="Search job title or city..." 
               value="<?= htmlspecialchars($search); ?>">
        
        <!-- Keep the current type hidden so it doesn't reset when searching text -->
        <input type="hidden" name="type" value="<?= htmlspecialchars($type); ?>">
        
        <button type="submit" class="btn-blue">Search</button>
        
        <?php if($search !== '' || $type !== ''): ?>
            <a href="available_jobs.php" class="btn-clear">Clear All</a>
        <?php endif; ?>
    </form>

    <!-- Filter Chips -->
    <div style="margin-top: 15px; display: flex; gap: 8px; flex-wrap: wrap;">
        <span style="font-size: 13px; color: #7f8c8d; align-self: center;">Quick Filter:</span>
        <a href="available_jobs.php?type=Full-time&search=<?= urlencode($search) ?>" 
           style="text-decoration:none; font-size:12px; padding: 6px 12px; border-radius: 20px; background: <?= $type == 'Full-time' ? '#3498db' : '#eee' ?>; color: <?= $type == 'Full-time' ? 'white' : '#333' ?>;">Full-time</a>
        
        <a href="available_jobs.php?type=Part-time&search=<?= urlencode($search) ?>" 
           style="text-decoration:none; font-size:12px; padding: 6px 12px; border-radius: 20px; background: <?= $type == 'Part-time' ? '#3498db' : '#eee' ?>; color: <?= $type == 'Part-time' ? 'white' : '#333' ?>;">Part-time</a>
        
        <a href="available_jobs.php?type=Contract&search=<?= urlencode($search) ?>" 
           style="text-decoration:none; font-size:12px; padding: 6px 12px; border-radius: 20px; background: <?= $type == 'Contract' ? '#3498db' : '#eee' ?>; color: <?= $type == 'Contract' ? 'white' : '#333' ?>;">Contract</a>
           
        <a href="available_jobs.php?type=Freelance&search=<?= urlencode($search) ?>" 
           style="text-decoration:none; font-size:12px; padding: 6px 12px; border-radius: 20px; background: <?= $type == 'Freelance' ? '#3498db' : '#eee' ?>; color: <?= $type == 'Freelance' ? 'white' : '#333' ?>;">Freelance</a>
    </div>
</div>
    </div>
    

    <!-- SEARCH BAR SECTION -->
    <div class="search-section">
        <form action="available_jobs.php" method="GET" class="search-form">
            <input type="text" name="search" class="search-input" 
                   placeholder="Search job title, city, or company..." 
                   value="<?= htmlspecialchars($search); ?>">
            
            <button type="submit" class="btn-blue">Search</button>
            
            <?php if($search !== ''): ?>
                <a href="available_jobs.php" class="btn-clear">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- JOBS LIST -->
    <?php if (count($jobs) > 0): ?>
        <?php foreach ($jobs as $job): ?>
            <div class="job-card">
                <div class="job-info">
                    <h3><?= htmlspecialchars($job['job_title']); ?></h3>
                    <p><strong>Company:</strong> <?= htmlspecialchars($job['official_company'] ?? 'Direct Hire'); ?></p>
                    <p>
                        <span class="badge"><?= htmlspecialchars($job['job_type'] ?? 'Full-time'); ?></span> 
                        • 📍 <?= htmlspecialchars($job['location'] ?? 'Remote'); ?>
                    </p>
                </div>
                <div>
                    <a href="job_details.php?id=<?= $job['id']; ?>" class="btn-blue">View Details</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; background: white; border-radius: 8px;">
            <p style="color: #7f8c8d;">No jobs found matching "<strong><?= htmlspecialchars($search); ?></strong>".</p>
            <a href="available_jobs.php" style="color: #3498db;">Show all jobs</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>