// Fetch messages between the logged-in user and the other person
$sql = "SELECT * FROM messages 
        WHERE (sender_id = :my_id AND receiver_id = :other_id) 
        OR (sender_id = :other_id AND receiver_id = :my_id) 
        ORDER BY sent_at ASC";

$stmt = $conn->prepare($sql);
$stmt->execute([':my_id' => $user_id, ':other_id' => $receiver_id]);
$chats = $stmt->fetchAll();