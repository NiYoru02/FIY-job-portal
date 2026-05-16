<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['receiver_id'])) {
    die("<div style='text-align:center; color:gray; padding:20px;'>Select a contact to start.</div>");
}

$user_id = $_SESSION['user_id'];
$receiver_id = (int)$_GET['receiver_id'];

try {
    $stmt = $conn->prepare("SELECT * FROM messages WHERE 
        (sender_id = :u AND receiver_id = :r) OR 
        (sender_id = :r AND receiver_id = :u) 
        ORDER BY created_at ASC");
    $stmt->execute(['u' => $user_id, 'r' => $receiver_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rows)) {
        echo "<div style='text-align:center; color:gray; padding:20px;'>No messages yet.</div>";
    }

    foreach ($rows as $msg) {
        $is_sent = ($msg['sender_id'] == $user_id);
        $align = $is_sent ? 'flex-end' : 'flex-start';
        $bg = $is_sent ? '#3498db' : '#f1f1f1';
        $color = $is_sent ? 'white' : '#333';
        
        echo "<div style='display:flex; flex-direction:column; align-items:$align; margin-bottom:10px;'>";
        
        if (!empty($msg['message_text'])) {
            echo "<span style='padding:10px; border-radius:15px; background:$bg; color:$color; max-width:75%; word-wrap:break-word;'>" 
                 . htmlspecialchars($msg['message_text']) . "</span>";
        }

        if (!empty($msg['image_path'])) {
            echo "<img src='chat_uploads/".htmlspecialchars($msg['image_path'])."' 
                  style='max-width:200px; border-radius:10px; margin-top:5px; cursor:pointer;' 
                  onclick='window.open(this.src)'>";
        }
        echo "</div>";
    }
} catch (Exception $e) { echo "Error: " . $e->getMessage(); }
?>