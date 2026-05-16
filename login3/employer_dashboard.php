<?php
session_start();
include "db.php";

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header("Location: login_employer.php");
    exit();
}

$employer_id = $_SESSION['user_id'];
$employer_name = $_SESSION['full_name'] ?? 'Employer';
$target_seeker_id = isset($_GET['seeker_id']) ? (int)$_GET['seeker_id'] : 0;

// 2. Fetch All Data (Stats + Job List)
try {
    // Stats
    $stmt_jobs = $conn->prepare("SELECT COUNT(*) FROM jobs WHERE employer_id = ?");
    $stmt_jobs->execute([$employer_id]);
    $total_jobs = $stmt_jobs->fetchColumn();

    $stmt_apps = $conn->prepare("SELECT COUNT(a.id) FROM applications a JOIN jobs j ON a.job_id = j.id WHERE j.employer_id = ?");
    $stmt_apps->execute([$employer_id]);
    $total_apps = $stmt_apps->fetchColumn();

    // The actual job rows for the "Storage Box"
   // The actual job rows for the "Storage Box"
try {
    // We changed date_posted to created_at here
    $stmt_list = $conn->prepare("SELECT id, job_title, job_type, created_at FROM jobs WHERE employer_id = ? ORDER BY id DESC");
    $stmt_list->execute([$employer_id]);
    $my_jobs = $stmt_list->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $my_jobs = [];
}

} catch (PDOException $e) {
    die("Error fetching dashboard data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Dashboard | FIY Portal</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root {
            --primary: #2ecc71;
            --secondary: #3498db;
            --dark: #2c3e50;
            --light: #f4f7f6;
            --white: #ffffff;
            --text: #333;
            --success: #27ae60;
        }

        body { font-family: 'Segoe UI', sans-serif; background-color: var(--light); margin: 0; display: flex; }

        .sidebar { width: 260px; background: var(--dark); color: white; height: 100vh; position: fixed; padding: 25px; box-sizing: border-box; }
        .sidebar h2 { color: var(--primary); margin-bottom: 30px; }
        .sidebar a { display: block; color: #bdc3c7; text-decoration: none; padding: 12px; border-radius: 8px; margin-bottom: 10px; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: #34495e; color: white; }
        .sidebar a.active { background: var(--primary); }

        .main-content { margin-left: 260px; padding: 40px; width: calc(100% - 260px); box-sizing: border-box; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: var(--white); padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); text-align: center; }
        .stat-card p { font-size: 32px; font-weight: bold; margin: 10px 0 0; color: var(--dark); }

        .action-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; margin-bottom: 30px;}
        .action-card { background: var(--white); padding: 25px; border-radius: 15px; text-decoration: none; color: var(--text); box-shadow: 0 4px 15px rgba(0,0,0,0.08); border-left: 5px solid var(--primary); }

        .messenger-section { background: var(--white); padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px; }
        
        #chat-display { height: 350px; overflow-y: auto; border: 1px solid #eee; padding: 20px; background: #fafafa; border-radius: 10px; display: flex; flex-direction: column; gap: 12px; }
        .chat-form { display: flex; gap: 12px; margin-top: 15px; }
        .chat-form input[type="text"] { flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 25px; }

        .job-item { background: #fff; border: 1px solid #eee; padding: 15px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .btn-link { padding: 8px 15px; border-radius: 20px; text-decoration: none; font-weight: bold; font-size: 13px; }
        .btn-blue { background: var(--secondary); color: white; }

        /* Inside your existing <style> tag */
.btn-red { 
    background: #e74c3c; 
    color: white; 
    padding: 8px 15px;
    border-radius: 20px;
    text-decoration: none;
    font-weight: bold;
    font-size: 13px;
    transition: 0.3s;
}
.btn-red:hover { 
    background: #c0392b; 
}
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>FIY Portal</h2>
        <a href="employer_dashboard.php" class="active">🏠 Dashboard Home</a>
        <a href="company_profile_view.php">🏢 Company Profile</a>
        <a href="post_job.php">📝 Post a Job</a>
        <a href="view_resume.php">👥 View Applicants</a>
        <a href="logout.php" style="color:#e74c3c; margin-top:50px;">🚪 Logout</a>
    </div>

    <div class="main-content">
        <div class="welcome-header">
            <h1>Welcome back, <?php echo htmlspecialchars($employer_name); ?>!</h1>
        </div>

        <!-- Stats Section -->
        <div class="stats-grid">
            <div class="stat-card"><h3>Active Job Posts</h3><p><?php echo $total_jobs; ?></p></div>
            <div class="stat-card"><h3>Total Applicants</h3><p><?php echo $total_apps; ?></p></div>
        </div>

        <div class="action-grid">
            <a href="post_job.php" class="action-card"><h2>📝 Post Job</h2><p>Start a new listing.</p></a>
            <a href="view_resume.php" class="action-card" style="border-left-color: var(--secondary);"><h2>👥 Applicants</h2><p>Manage submissions.</p></a>
        </div>

        <!-- --- THE JOB STORAGE BOX --- -->
        <div class="messenger-section">
            <h3>📋 Your Posted Jobs</h3>
            <div style="max-height: 300px; overflow-y: auto; margin-top: 15px;">
                <?php if (empty($my_jobs)): ?>
                    <p style="text-align:center; color:gray; padding:20px;">No jobs posted yet.</p>
                <?php else: ?>
                    <?php foreach ($my_jobs as $job): ?>
                       <div class="job-item">
    <div>
        <strong><?php echo htmlspecialchars($job['job_title']); ?></strong>
        <br><small><?php echo htmlspecialchars($job['job_type']); ?> • <?php echo htmlspecialchars($job['created_at']); ?></small>
    </div>
    <div style="display: flex; gap: 10px;">
        <!-- Manage Button -->
        <a href="edit_job.php?id=<?php echo $job['id']; ?>" class="btn-link btn-blue">Manage</a>
        
        <!-- Delete Button -->
        <a href="delete_job.php?id=<?php echo $job['id']; ?>" 
           class="btn-link" 
           style="background: #e74c3c; color: white;" 
           onclick="return confirm('Are you sure you want to delete this job?')">
           Delete
        </a>
    </div>
</div>
                    <?php endforeach; ?>
                <?php endif; ?>
            

        </div>
        <!-- Messenger Section -->
        <div class="section-box">
    <h3>💬 Live Chat</h3>
    <div id="chat-display" style="height:300px; overflow-y:auto; background:#f9fbff; border:1px solid #ddd; padding:15px; border-radius:10px;">
        <!-- Messages load here -->
    </div>

   <?php if($target_seeker_id > 0): ?>
    <form id="chat-form" style="display:flex; gap:10px; margin-top:15px;" enctype="multipart/form-data">
        <input type="hidden" name="receiver_id" id="receiver_id" value="<?= $target_seeker_id ?>">
        
        <!-- Added Image Input -->
        <input type="file" name="chat_image" id="chat_image_emp" accept="image/*" style="display: none;">
        <label for="chat_image_emp" style="cursor: pointer; padding: 10px; font-size: 22px;">🖼️</label>
        
        <input type="text" name="message" id="msg_input" placeholder="Type here..." style="flex:1; padding:10px; border-radius:20px; border:1px solid #ddd;">
        <button type="submit" id="send-btn" style="background:#3498db; color:white; border:none; padding:10px 20px; border-radius:20px; cursor:pointer;">SEND</button>
    </form>
    <div id="file-ready-emp" style="font-size:11px; color:#27ae60; margin-left:45px;"></div>
<?php endif; ?>

<script>
$(document).ready(function() {
    const rid = $('#receiver_id').val();

    $('#chat-form').on('submit', function(e) {
        e.preventDefault();
        
        // Validation: Don't send if both text and image are empty
        if ($('#msg_input').val().trim() === "" && !$('#chat_image_emp')[0].files[0]) return;

        let formData = new FormData(this);
        formData.append('sender_role', 'employer'); // Tell send.php who is sending

        $.ajax({
            url: 'send.php',
            type: 'POST',
            data: formData,
            processData: false, // Required for FormData
            contentType: false, // Required for FormData
            success: function() {
                $('#msg_input').val('');
                $('#chat_image_emp').val('');
                $('#file-ready-emp').text('');
                loadMessages();
            },
            error: function() {
                alert("Error sending message.");
            }
        });
    });

    // Show filename when selected
    $('#chat_image_emp').change(function() {
        if (this.files[0]) $('#file-ready-emp').text('📷 Ready: ' + this.files[0].name);
    });

    function loadMessages() {
        if (!rid) return;
        $.get('fetch_messages.php', { receiver_id: rid }, function(data) {
            $('#chat-display').html(data);
            $('#chat-display').scrollTop($('#chat-display')[0].scrollHeight);
        });
    }

    if (rid > 0) {
        loadMessages();
        setInterval(loadMessages, 2000);
    }
});
</script>
</body>
</html>