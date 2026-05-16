<?php
$_SESSION['role'] = 'seeker';
session_start();
include "db.php";

// 1. Security Check: Only Job Seekers allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seeker') {
    header("Location: login.php");
    exit();
}

$me = $_SESSION['user_id'];

// 2. Identify who we are talking to
// This grabs the 'employer_id' from the URL (sent by the Message button)
$target_employer_id = isset($_GET['employer_id']) ? $_GET['employer_id'] : 0;
$employer_name = "Select a conversation";

if ($target_employer_id > 0) {
    // Assuming employers are in the 'users' table
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->execute([$target_employer_id]);
    $emp = $stmt->fetch();
    if ($emp) {
        $employer_name = $emp['name'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Messages | FIY</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; margin: 0; display: flex; }
        .sidebar { width: 260px; background: #2c3e50; color: white; height: 100vh; position: fixed; padding: 20px; box-sizing: border-box; }
        .main-content { margin-left: 280px; padding: 40px; width: calc(100% - 280px); }
        
        .chat-panel { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 600px; }
        #chat-display { height: 350px; overflow-y: auto; border: 1px solid #eee; padding: 15px; background: #fafafa; display: flex; flex-direction: column; margin-bottom: 15px; border-radius: 5px; }
        
        /* Message Bubbles - matching your employer dash logic */
        .msg-sent { background: #3498db; color: white; align-self: flex-end; padding: 8px 12px; border-radius: 12px 12px 0 12px; margin: 5px; max-width: 70%; }
        .msg-received { background: #e0e0e0; color: #333; align-self: flex-start; padding: 8px 12px; border-radius: 12px 12px 12px 0; margin: 5px; max-width: 70%; }
        
        .chat-input-area { display: flex; gap: 10px; }
        .chat-input-area input { flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 4px; outline: none; }
        .btn-send { background: #3498db; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        
        .sidebar a { display: block; color: #bdc3c7; text-decoration: none; padding: 10px 0; border-bottom: 1px solid #34495e; transition: 0.3s; }
        .sidebar a:hover { color: white; padding-left: 5px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>FIY Seeker</h2>
    <p style="font-size: 12px; color: #2ecc71;">Online: <?php echo $_SESSION['name']; ?></p>
    <hr style="border: 0; border-top: 1px solid #444; margin: 20px 0;">
    <a href="seeker_dashboard.php">🏠 Dashboard</a>
    
    <h4 style="margin-top: 30px; color: #7f8c8d; font-size: 12px; text-transform: uppercase;">Recent Messages</h4>
    <?php
    // Find any employer who has sent a message to this seeker
    $stmt = $conn->prepare("SELECT DISTINCT sender_id, u.name 
                            FROM messages m 
                            JOIN users u ON m.sender_id = u.id 
                            WHERE m.receiver_id = ?");
    $stmt->execute([$me]);
    $chats = $stmt->fetchAll();
    
    if($chats) {
        foreach($chats as $chat) {
            $activeClass = ($target_employer_id == $chat['sender_id']) ? "style='color:white; font-weight:bold;'" : "";
            echo "<a href='seeker_messages.php?employer_id=".$chat['sender_id']."' $activeClass>👤 ".$chat['name']."</a>";
        }
    } else {
        echo "<p style='font-size: 12px; color: #7f8c8d;'>No chats yet.</p>";
    }
    ?>
</div>

<div class="main-content">
    <h1>Inbox</h1>
    
    <div class="chat-panel">
        <h3>Conversation: <span style="color: #3498db;"><?php echo htmlspecialchars($employer_name); ?></span></h3>
        
        <div id="chat-display">
            <!-- fetch_messages.php will load text here -->
        </div>

        <form id="seeker-chat-form" class="chat-input-area">
            <!-- This is the "receiver" for the send.php script -->
            <input type="hidden" id="target_id" value="<?php echo $target_employer_id; ?>">
            <input type="text" id="seeker_msg" placeholder="Type your reply..." required autocomplete="off">
            <button type="submit" class="btn-send">Send</button>
        </form>
    </div>
</div>

<script>
function refreshChat() {
    let empId = document.getElementById('target_id').value;
    
    if (empId == 0 || empId == "") return;

    // Use the absolute path to be safe
    fetch('/login3/fetch_messages.php?receiver_id=' + empId)
        .then(res => res.text())
        .then(data => {
            const display = document.getElementById('chat-display');
            display.innerHTML = data;
            display.scrollTop = display.scrollHeight;
        });
}
    // Connect to your existing fetch script
    fetch('fetch_messages.php?receiver_id=' + empId)
        .then(res => res.text())
        .then(data => {
            const display = document.getElementById('chat-display');
            display.innerHTML = data;
            display.scrollTop = display.scrollHeight;
        });
}

// Check for new messages every 2 seconds
setInterval(refreshChat, 2000);
refreshChat();

// Send Message logic
const chatForm = document.getElementById('seeker-chat-form');
chatForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const id = document.getElementById('target_id').value;
    const text = document.getElementById('seeker_msg').value;

    if(id == 0) {
        alert("Please select a contact first.");
        return;
    }

    


// To this (Absolute Path):
fetch('/login3/send.php', { 
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    // IMPORTANT: Ensure it uses 'receiver_id' and 'message_text'
    body: `receiver_id=${id}&message_text=${encodeURIComponent(text)}`
})
    .then(res => res.text())
    .then(result => {
        if(result.includes("Success")) {
            document.getElementById('seeker_msg').value = ''; // Clear input
            refreshChat(); // Update UI
        } else {
            console.error("Send failed:", result);
        }
    });
});
</script>

</body>
</html>