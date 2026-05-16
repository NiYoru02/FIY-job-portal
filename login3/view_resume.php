<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header("Location: login_employer.php");
    exit();
}

$emp_id = $_SESSION['user_id'];
// NEW: Catch the job_id from the dashboard link
$filter_job_id = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;

// Clear notifications
$clear_notif = "UPDATE applications a JOIN jobs j ON a.job_id = j.id SET a.is_read = 1 WHERE j.employer_id = ? AND a.is_read = 0";
$stmt_clear = $conn->prepare($clear_notif);
$stmt_clear->execute([$emp_id]);

// Get applicants (MODIFIED to filter by job if job_id is provided)
$sql = "SELECT a.id as application_id, a.status, a.applied_at, a.user_id as seeker_id, 
               j.job_title, s.fullname as seeker_name, s.email as seeker_email, s.profile_pic
        FROM applications a
        INNER JOIN jobs j ON a.job_id = j.id
        LEFT JOIN job_seekers s ON a.user_id = s.id 
        WHERE j.employer_id = ?";

// Add the extra filter if a specific job was clicked
if ($filter_job_id > 0) {
    $sql .= " AND j.id = " . $filter_job_id;
}

$sql .= " ORDER BY a.applied_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute([$emp_id]);
$applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Applicants | FIY Portal</title>
    <style>
        :root { --primary: #2ecc71; --dark: #2c3e50; --light: #f4f7f6; --danger: #e74c3c; --blue: #3498db; --border: #eee; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--light); margin: 0; display: flex; }
        .sidebar { width: 260px; background: var(--dark); color: white; height: 100vh; position: fixed; padding: 25px; box-sizing: border-box; }
        .sidebar h2 { color: var(--primary); margin-top: 0; }
        .sidebar a { display: block; color: #bdc3c7; text-decoration: none; padding: 12px 15px; border-radius: 5px; margin-bottom: 5px; }
        .sidebar a:hover { background: #34495e; color: white; }
        .sidebar a.active { background: var(--primary); color: white; }
        .main-content { margin-left: 260px; padding: 40px; width: calc(100% - 260px); box-sizing: border-box; }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        #searchBar { padding: 12px 20px; width: 320px; border: 1px solid #ddd; border-radius: 25px; outline: none; }
        .table-card { background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #fdfdfd; padding: 18px; text-align: left; color: #7f8c8d; font-size: 12px; text-transform: uppercase; border-bottom: 2px solid var(--border); }
        td { padding: 20px 18px; border-bottom: 1px solid var(--border); font-size: 14px; }
        .seeker-info { display: flex; align-items: center; gap: 12px; }
        .avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .profile-link { text-decoration: none; color: inherit; }
        .profile-link:hover strong { color: var(--blue); text-decoration: underline; }
        .badge { padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .pending { background: #fff9db; color: #f08c00; }
        .accepted { background: #ebfbee; color: #2b8a3e; }
        .rejected { background: #fff5f5; color: #c92a2a; }
        .btn-group { display: flex; gap: 12px; align-items: center; }
        .action-link { text-decoration: none; font-size: 13px; font-weight: 600; }
        .accept-link { color: var(--primary); }
        .reject-link { color: var(--danger); border: none; background: none; cursor: pointer; padding: 0; }
        .msg-btn { background: var(--blue); color: white; padding: 8px 16px; border-radius: 6px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>FIY Portal</h2>
    <hr style="border: 0; border-top: 1px solid #34495e; margin-bottom: 20px;">
    <a href="employer_dashboard.php">🏠 Dashboard Home</a>
    <a href="company_profile_view.php">🏢 Company Profile</a> 
    <a href="post_job.php">📝 Post a Job</a>
    <a href="view_resume.php" class="active">👥 View Applicants</a>
    <a href="logout.php" style="color: #e74c3c; margin-top: 50px;">🚪 Logout</a>
</div>

<div class="main-content">
    <div class="header-flex">
        <h1>
            <?php echo ($filter_job_id > 0) ? "Applicants for this Position" : "Review All Applicants"; ?>
        </h1>
        <?php if($filter_job_id > 0): ?>
            <a href="view_resume.php" style="font-size:12px; color:var(--blue); text-decoration:none;">Show all applicants instead</a>
        <?php endif; ?>
        <input type="text" id="searchBar" placeholder="Search..." onkeyup="filterTable()">
    </div>

    <div class="table-card">
        <table id="applicantsTable">
            <thead>
                <tr>
                    <th>Applicant</th>
                    <th>Job Title</th>
                    <th>Date Applied</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($applicants) > 0): ?>
                    <?php foreach($applicants as $app): ?>
                    <tr>
                        <td>
                            <a href="view_seeker_profile.php?id=<?php echo $app['seeker_id']; ?>" class="profile-link">
                                <div class="seeker-info">
                                    <img src="uploads/<?php echo !empty($app['profile_pic']) ? $app['profile_pic'] : 'default.png'; ?>" 
                                         class="avatar" onerror="this.src='https://via.placeholder.com/40'">
                                    <div>
                                        <strong><?php echo htmlspecialchars($app['seeker_name'] ?? 'Unknown Seeker'); ?></strong><br>
                                        <small style="color: #999;"><?php echo htmlspecialchars($app['seeker_email']); ?></small>
                                    </div>
                                </div>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($app['job_title']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($app['applied_at'])); ?></td>
                        <td>
                            <span class="badge <?php echo strtolower($app['status']); ?>">
                                <?php echo htmlspecialchars($app['status']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <?php if (strtolower($app['status']) === 'pending'): ?>
                                    <a href="update_status.php?id=<?php echo $app['application_id']; ?>&status=Accepted" class="action-link accept-link" onclick="return confirm('Accept this applicant?')">Accept</a>
                                    <span style="color: #ddd;">|</span>
                                    <button class="action-link reject-link" onclick="handleReject(<?php echo $app['application_id']; ?>)">Reject</button>
                                <?php endif; ?>
                                <a href="employer_dashboard.php?seeker_id=<?php echo $app['seeker_id']; ?>" class="action-link msg-btn">💬 Chat</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align: center; padding: 40px; color: #999;">No applications found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filterTable() {
    let input = document.getElementById("searchBar").value.toUpperCase();
    let rows = document.getElementById("applicantsTable").getElementsByTagName("tr");
    for (let i = 1; i < rows.length; i++) {
        let text = rows[i].innerText.toUpperCase();
        rows[i].style.display = text.indexOf(input) > -1 ? "" : "none";
    }
}
function handleReject(appId) {
    let reason = prompt("Why are you rejecting this applicant?");
    if (reason !== null) {
        window.location.href = "update_status.php?id=" + appId + "&status=Rejected&reason=" + encodeURIComponent(reason);
    }
}
</script>
</body>
</html>