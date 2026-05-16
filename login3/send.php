<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $sender_id = $_SESSION['user_id'];
    $receiver_id = $_POST['receiver_id'];
    $message_text = $_POST['message'] ?? '';
    $sender_role = $_SESSION['role']; 
    $receiver_role = ($sender_role === 'employer') ? 'seeker' : 'employer';

    $image_path = null;
    // 1. Updated folder name to chat_uploads
    $target_dir = "chat_uploads/"; 

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // 2. Checking for 'chat_image' (the name used in your dashboard form)
    if (isset($_FILES["chat_image"]) && $_FILES["chat_image"]["error"] === 0) {
        $file_ext = pathinfo($_FILES["chat_image"]["name"], PATHINFO_EXTENSION);
        $file_name = time() . "_" . uniqid() . "." . $file_ext;
        
        if (move_uploaded_file($_FILES["chat_image"]["tmp_name"], $target_dir . $file_name)) {
            $image_path = $file_name;
        }
    }

    // 3. Save to database
    if (!empty($message_text) || !empty($image_path)) {
        // NOTE: Ensure these column names (message_text, image_path) match your database exactly
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message_text, image_path, sender_role, receiver_role) 
                                VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$sender_id, $receiver_id, $message_text, $image_path, $sender_role, $receiver_role]);
        echo "success";
    }
}
?>