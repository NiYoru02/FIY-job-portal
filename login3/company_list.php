<?php
session_start();
include "db.php";

$stmt = $conn->prepare("SELECT * FROM employers ORDER BY id DESC");
$stmt->execute();
$companies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Company Directory | FIY</title>
    <style>
        /* 1. Reset body margin and padding so the bar touches the top */
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; margin: 0; padding: 0; }
        
        /* 2. Create the Top Bar */
        .top-bar { 
            background-color: #3b5998; 
            padding: 15px 20px; 
            margin-bottom: 30px;
        }

        /* 3. Style the Back Button specifically for the top bar */
        .back-nav { 
            text-decoration: none; 
            color: white; 
            background: rgba(255,255,255,0.2); 
            padding: 8px 15px; 
            border-radius: 4px; 
            font-size: 14px; 
            font-weight: bold;
            transition: 0.3s;
        }
        .back-nav:hover { background: rgba(255,255,255,0.4); }

        /* 4. Keep your grid styles */
        .container { max-width: 1100px; margin: auto; padding: 0 20px; }
        h1 { color: #2c3e50; text-align: center; margin-bottom: 40px; }
        
        .company-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); 
            gap: 20px; 
        }

        .company-card { 
            background: white; 
            padding: 20px; 
            border-radius: 12px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.05); 
            text-align: center;
            transition: 0.3s;
            border-top: 5px solid #3498db;
        }

        .company-card:hover { transform: translateY(-5px); box-shadow: 0 8px 15px rgba(0,0,0,0.1); }

        .company-logo { 
            width: 80px; height: 80px; 
            border-radius: 50%; 
            object-fit: cover; 
            margin-bottom: 15px; 
            border: 2px solid #eee;
            background: #eee;
        }

        .company-name { font-size: 18px; font-weight: bold; color: #2c3e50; margin: 10px 0; }
        .company-loc { color: #7f8c8d; font-size: 13px; margin-bottom: 15px; }
        
        .view-btn { 
            display: inline-block; 
            background: #3498db; 
            color: white; 
            padding: 10px 20px; 
            text-decoration: none; 
            border-radius: 20px; 
            font-weight: bold; 
            font-size: 13px; 
        }
    </style>
</head>
<body>

<!-- This is the top section that moves the button to the very top -->
<header class="top-bar">
    <a href="Dashboard.php" class="back-nav">← Back to Dashboard</a>
</header>

<div class="container">
    <h1>Company Directory</h1>

    <div class="company-grid">
        <?php foreach($companies as $comp): 
            $displayName = !empty($comp['company_name']) ? $comp['company_name'] : $comp['full_name'];
            $imgFile = $comp['company_logo'];
            $img_path = !empty($imgFile) ? "uploads1/" . $imgFile : "https://via.placeholder.com/150"; 
        ?>
            <div class="company-card">
                <img src="<?= $img_path ?>" 
                     onerror="this.src='https://via.placeholder.com/150';" 
                     class="company-logo">
                
                <div class="company-name"><?= htmlspecialchars($displayName) ?></div>
                
                <div class="company-loc">
                    <?= htmlspecialchars($comp['location'] ?? 'No location set') ?>
                </div>
                
                <a href="view_company.php?id=<?= $comp['id'] ?>" class="view-btn">View Profile</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>