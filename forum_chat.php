<?php
// Connect to the database
$servername = "localhost";
$username = "root";
$password = ""; // Adjust accordingly
$dbname = "forum_chat";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Insert a new message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'];
    $message = $_POST['message'];

    $stmt = $conn->prepare("INSERT INTO messages (username, message) VALUES (?, ?)");
    $stmt->bind_param("ss", $user, $message);
    $stmt->execute();
    $stmt->close();
    exit();
}

// Retrieve messages
$sql = "SELECT * FROM messages ORDER BY created_at DESC";
$result = $conn->query($sql);
$messages = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum Chat</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .chat-box { width: 100%; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ccc; }
        .chat-messages { height: 300px; overflow-y: scroll; border: 1px solid #ddd; margin-bottom: 20px; padding: 10px; }
        .message { margin-bottom: 10px; }
        .message strong { color: #333; }
    </style>
</head>
<body>

<div class="chat-box">
    <div class="chat-messages" id="chat-messages">
        <?php foreach($messages as $message): ?>
            <div class="message">
                <strong><?= htmlspecialchars($message['username']) ?>:</strong>
                <?= htmlspecialchars($message['message']) ?>
            </div>
        <?php endforeach; ?>
    </div>

    <form id="chat-form">
        <input type="text" id="username" placeholder="Enter your name" required><br><br>
        <textarea id="message" placeholder="Enter your message" required></textarea><br><br>
        <button type="submit">Send</button>
    </form>
</div>

<script>
    const form = document.getElementById('chat-form');
    const chatMessages = document.getElementById('chat-messages');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const username = document.getElementById('username').value;
        const message = document.getElementById('message').value;

        if (username && message) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'forum_chat.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (this.status === 200) {
                    const newMessage = document.createElement('div');
                    newMessage.classList.add('message');
                    newMessage.innerHTML = `<strong>${username}:</strong> ${message}`;
                    chatMessages.prepend(newMessage);
                    document.getElementById('message').value = '';
                }
            };
            xhr.send(`username=${username}&message=${message}`);
        }
    });
</script>

</body>
</html>
