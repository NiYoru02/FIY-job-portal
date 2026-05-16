<?php
session_start();
include 'db.php'; 

$_SESSION['role'] = 'seeker';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. Fetch Seeker Info
$stmt = $conn->prepare("SELECT * FROM job_seekers WHERE id = ?");
$stmt->execute([$user_id]);
$myInfo = $stmt->fetch(PDO::FETCH_ASSOC);

// 2. Logic for viewing a specific company profile or starting a chat
$target_employer_id = isset($_GET['employer_id']) ? (int)$_GET['employer_id'] : 0;
$view_all_companies = isset($_GET['view']) && $_GET['view'] == 'all_companies';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Dashboard</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root { --primary: #3498db; --success: #27ae60; --dark: #2c3e50; --bg: #f4f7f6; }
        body { margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; background: var(--bg); }
        
        .top-bar { background-color: #000; padding: 10px 0; color: white; width: 100%; position: fixed; top: 0; z-index: 100; }
        .header-content { max-width: 1000px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; padding: 0 20px; }
        .logo img { height: 100px; width: auto; display: block; margin: -30px 0; }
        
        .page-container { display: flex; justify-content: center; padding: 100px 20px 40px; max-width: 1100px; margin: 0 auto; gap: 25px; }
        
        .sidebar { width: 280px; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); height: fit-content; position: sticky; top: 100px; }
        .sidebar h3 { font-size: 16px; color: var(--dark); margin-bottom: 15px; border-bottom: 2px solid var(--bg); padding-bottom: 5px; }
        .nav-item { display: block; padding: 10px; text-decoration: none; color: #555; font-size: 14px; border-radius: 6px; margin-bottom: 5px; transition: 0.2s; }
        .nav-item:hover, .nav-item.active { background: #f0f2f5; color: var(--primary); font-weight: bold; }
        
        .contact-btn { display: block; width: 100%; padding: 12px; margin-bottom: 8px; background: #f8f9fa; border: 1px solid #eee; border-radius: 8px; text-decoration: none; color: #333; font-size: 13px; transition: 0.3s; box-sizing: border-box; }
        .contact-btn:hover, .contact-btn.active { background: var(--primary); color: white; }

        .profile-card { background: white; flex: 1; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .dashboard-pic { width: 110px; height: 110px; object-fit: cover; border-radius: 10px; border: 3px solid var(--primary); margin-bottom: 15px; }
        
        .btn-link { padding: 10px 20px; border-radius: 30px; border: none; cursor: pointer; font-weight: bold; text-decoration: none; font-size: 14px; display: inline-block; transition: 0.3s; }
        .btn-blue { background: var(--primary); color: white; }
        .btn-green { background: var(--success); color: white; }

        .company-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 15px; margin-top: 20px; }
        .company-item { padding: 20px; border: 1px solid #eee; border-radius: 10px; text-align: center; transition: 0.3s; background: #fff; }

        #chat-display { height: 400px; overflow-y: auto; border: 1px solid #eee; padding: 20px; background: #fafafa; border-radius: 10px; display: flex; flex-direction: column; gap: 12px; }
        .chat-form { display: flex; gap: 12px; margin-top: 15px; align-items: center; }
        .chat-form input[type="text"] { flex: 1; padding: 12px 20px; border: 1px solid #ddd; border-radius: 25px; outline: none; }
        
        #messenger-zoom-box { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); z-index: 999999; justify-content: center; align-items: center; flex-direction: column; }
        #zoomed-img { max-width: 90%; max-height: 75%; border: 2px solid white; border-radius: 5px; }
    </style>
</head>
<body>

<header class="top-bar">
    <div class="header-content">
        <a href="dashboard.php" class="logo"><img src="2.png" alt="Logo"></a>
        <a href="logout.php" style="color:white; text-decoration:none; font-weight:bold; font-size: 14px;">Logout</a>
    </div>
</header>

<div class="page-container">
    <aside class="sidebar">
        <h3>Menu</h3>
        <a href="dashboard.php" class="nav-item <?= (!$view_all_companies && $target_employer_id == 0) ? 'active' : '' ?>">🏠 My Dashboard</a>
        <a href="available_jobs.php" class="nav-item">🔍 Browse All Jobs</a>
        <a href="dashboard.php?view=all_companies" class="nav-item <?= ($view_all_companies) ? 'active' : '' ?>">🏢 Browse All Companies</a>
        <a href="my_applications.php" class="nav-item">📄 My Applications</a>
        
        <hr style="border:0; border-top:1px solid #eee; margin: 20px 0;">
        
        <h3>My Contacts</h3>
        <?php
        $contactStmt = $conn->prepare("SELECT DISTINCT employers.* FROM applications 
                                       JOIN jobs ON applications.job_id = jobs.id 
                                       JOIN employers ON jobs.employer_id = employers.id 
                                       WHERE applications.user_id = ?");
        $contactStmt->execute([$user_id]);
        while ($c = $contactStmt->fetch()) {
            $activeClass = ($target_employer_id == $c['id']) ? 'active' : '';
            $name = !empty($c['company_name']) ? $c['company_name'] : $c['full_name'];
            echo "<a href='dashboard.php?employer_id={$c['id']}' class='contact-btn $activeClass'>💬 " . htmlspecialchars($name) . "</a>";
        }
        ?>
    </aside>

    <div class="profile-card">
        <?php if($view_all_companies): ?>
            <h2>🏢 Registered Companies</h2>
            <div class="company-grid">
                <?php
                $compStmt = $conn->query("SELECT id, company_name, full_name, email, company_logo FROM employers ORDER BY company_name ASC");
                while($comp = $compStmt->fetch()):
                    $cName = !empty($comp['company_name']) ? $comp['company_name'] : $comp['full_name'];
                    $cLogo = !empty($comp['company_logo']) ? 'uploads1/'.$comp['company_logo'] : 'default_company.png';
                ?>
                    <div class="company-item">
                        <img src="<?= htmlspecialchars($cLogo) ?>" style="width:70px; height:70px; object-fit:contain; margin-bottom:10px;">
                        <strong style="display:block;"><?= htmlspecialchars($cName) ?></strong>
                        <a href="view_company.php?id=<?= $comp['id'] ?>" class="btn-link" style="font-size:11px; background:#eee;">Details</a>
                        <a href="dashboard.php?employer_id=<?= $comp['id'] ?>" class="btn-link btn-blue" style="font-size:11px;">Message</a>
                    </div>
                <?php endwhile; ?>
            </div>

        <?php else: ?>
            <?php if($myInfo): ?>
                <div style="display: flex; gap: 25px; align-items: flex-start; margin-bottom: 25px;">
                    <img src="uploads/<?= htmlspecialchars($myInfo['profile_pic'] ?: 'default.png'); ?>" class="dashboard-pic">
                    <div style="flex: 1;">
                        <h2 style="margin:0;"><?= htmlspecialchars($myInfo['fullname']); ?></h2>
                        <p style="color:gray;">📧 <?= htmlspecialchars($myInfo['email']); ?></p>
                        <a href="add.php" class="btn-link btn-blue">Edit Profile</a>
                    </div>
                </div>

                <div style="margin-top: 40px; border-top: 2px solid #eee; padding-top: 20px;">
                    <h3>Messages</h3>
                    <div id="chat-display">
                        <?php if($target_employer_id == 0) echo "<p style='text-align:center; color:gray; margin-top:100px;'>Select a contact to chat.</p>"; ?>
                    </div>

                    <?php if($target_employer_id > 0): ?>
                        <form id="seeker-chat-form" class="chat-form" enctype="multipart/form-data">
                            <input type="hidden" name="receiver_id" id="employer_id_val" value="<?= $target_employer_id; ?>">
                            <input type="file" name="chat_image" id="chat_image_seeker" accept="image/*" style="display: none;">
                            <label for="chat_image_seeker" style="cursor: pointer; padding: 10px; font-size: 22px;">🖼️</label>
                            <input type="text" name="message" id="seeker_msg_input" placeholder="Write a message..." autocomplete="off">
                            <button type="submit" class="btn-link btn-blue">Send</button>
                        </form>
                        <div id="file-ready" style="font-size:11px; color:var(--success); margin-left:45px;"></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Image Zoom Box -->
<div id="messenger-zoom-box">
    <img id="zoomed-img" src="">
    <div style="margin-top: 20px;">
        <button id="close-zoom-btn" class="btn-link" style="background: #444; color: white;">Close</button>
    </div>
</div>

<script>
$(document).ready(function() {
    const empId = $('#employer_id_val').val();
    let isZoomed = false;

    function loadChat() {
        if (isZoomed || !empId) return;
        $.get('fetch_messages.php', { receiver_id: empId }, function(data) {
            const chatBox = $('#chat-display');
            if (chatBox.html() !== data) {
                chatBox.html(data);
                chatBox.scrollTop(chatBox[0].scrollHeight);
            }
        });
    }

    if (empId > 0) { 
        setInterval(loadChat, 2000); 
        loadChat(); 
    }

    $('#seeker-chat-form').on('submit', function(e) {
        e.preventDefault();
        
        if ($('#seeker_msg_input').val().trim() === "" && !$('#chat_image_seeker')[0].files[0]) return;

        let formData = new FormData(this);
        formData.append('sender_role', 'seeker'); 

        $.ajax({
            url: 'send.php', 
            type: 'POST', 
            data: formData, 
            processData: false, 
            contentType: false,
            success: function() {
                $('#seeker_msg_input').val('');
                $('#chat_image_seeker').val('');
                $('#file-ready').text('');
                loadChat();
            }
        });
    });

    $('#chat_image_seeker').change(function() {
        if (this.files[0]) $('#file-ready').text('📷 Ready to send: ' + this.files[0].name);
    });

    $(document).on('click', '.chat-img-zoom', function() {
        isZoomed = true;
        $('#zoomed-img').attr('src', $(this).attr('src'));
        $('#messenger-zoom-box').fadeIn(200).css('display', 'flex');
    });

    $('#close-zoom-btn').on('click', function() {
        $('#messenger-zoom-box').fadeOut(200, function() { isZoomed = false; });
    });
});
</script>
</body>
</html>