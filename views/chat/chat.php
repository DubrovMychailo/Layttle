<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../../core/Config.php';
use core\Config;

$pdo = Config::get()->getPDO();
if (empty($_SESSION['user'])) {
    die('Неавторизований доступ');
}

if (is_string($_SESSION['user'])) {
    $_SESSION['user'] = unserialize($_SESSION['user']);
}

if (!is_array($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    die('Помилка авторизації');
}

$userId = $_SESSION['user']['id'];
$receiverId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
if ($receiverId <= 0) {
    die('Некоректний ідентифікатор користувача');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = $_POST['message'] ?? '';
    $mediaPath = null;
    $mediaType = null;

    if (!empty($_FILES['media']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'video/mp4'];
        $fileType = $_FILES['media']['type'];
        $extension = pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION);

        if (in_array($fileType, $allowedTypes)) {
            $uploadDir = __DIR__ . '/../../uploads/';
            $newFileName = uniqid() . '.' . $extension;
            $uploadFile = $uploadDir . $newFileName;

            if (move_uploaded_file($_FILES['media']['tmp_name'], $uploadFile)) {
                $mediaPath = '/uploads/' . $newFileName;
                $mediaType = strpos($fileType, 'image') !== false ? 'image' : 'video';
            }
        }
    }

    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message, media, media_type) VALUES (:sender, :receiver, :message, :media, :media_type)");
    $stmt->execute([
        ':sender' => $userId,
        ':receiver' => $receiverId,
        ':message' => $message,
        ':media' => $mediaPath,
        ':media_type' => $mediaType
    ]);
    exit;
}

function getMessages($pdo, $userId, $receiverId) {
    $stmt = $pdo->prepare("SELECT m.*, u.firstname AS username, u.photo FROM messages m 
                            JOIN users u ON m.sender_id = u.id 
                            WHERE (m.sender_id = :user_id AND m.receiver_id = :receiver_id) 
                            OR (m.sender_id = :receiver_id AND m.receiver_id = :user_id) 
                            ORDER BY m.created_at ASC");
    $stmt->execute([':user_id' => $userId, ':receiver_id' => $receiverId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
$messages = getMessages($pdo, $userId, $receiverId);
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Чат</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous"/>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/IndexMain.css">
    <link rel="stylesheet" href="/css/Header.css">
    <link rel="stylesheet" href="/css/Chat.css">
    <?php require_once __DIR__ . '/../../views/layouts/header.php'; ?>
</head>
<body>
<div class="container mt-4">
    <div class="chat-box" id="chat-box">
        <?php foreach ($messages as $msg): ?>
            <?php
            $avatar = !empty($msg['photo']) ? '/uploads/' . htmlspecialchars($msg['photo']) : '/uploads/nophoto.webp';
            $isCurrentUser = ($msg['sender_id'] == $userId);
            ?>
            <div class="message <?= $isCurrentUser ? 'right' : 'left' ?>">
                <?php if (!$isCurrentUser): ?>
                    <img src="<?= $avatar ?>" class="photo" alt="Профільне фото">
                <?php endif; ?>

                <div class="message-content">
                    <strong><?= htmlspecialchars($msg['username']) ?>:</strong>
                    <div class="message-text"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
                    <?php if ($msg['media'] && $msg['media_type'] === 'image'): ?>
                        <img src="<?= htmlspecialchars($msg['media']) ?>" class="content mt-1" alt="Зображення">
                    <?php elseif ($msg['media'] && $msg['media_type'] === 'video'): ?>
                        <video controls class="mt-1">
                            <source src="<?= htmlspecialchars($msg['media']) ?>" type="video/mp4">
                        </video>
                    <?php endif; ?>
                </div>

                <?php if ($isCurrentUser): ?>
                    <img src="<?= $avatar ?>" class="photo" alt="Профільне фото">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<div>
    <form id="chat-form" enctype="multipart/form-data">
        <label for="media" class="file-label">
            <i class="fas fa-paperclip"></i>
        </label>
        <input type="file" name="media" id="media">

        <input type="text" name="message" id="message" placeholder="Напишіть повідомлення...">

        <button type="submit">
            <i class="fas fa-paper-plane"></i>
        </button>
    </form>
</div>
</body>
<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
</html>
<script>

    $(document).ready(function() {
        let chatBox = $("#chat-box");
        function isScrolledToBottom() {
            return chatBox[0].scrollHeight - chatBox.scrollTop() === chatBox.outerHeight();
        }

        $("#chat-form").submit(function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajax({
                url: "",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function() {
                    $("#message").val("");
                    $("#media").val("");
                    loadMessages();
                }
            });
        });

        function loadMessages() {
            chatBox.load(" #chat-box > *", function() {
                if (isScrolledToBottom()) {
                    chatBox.scrollTop(chatBox[0].scrollHeight);
                }
            });
        }

        $(window).on('load', function() {
            chatBox.scrollTop(chatBox[0].scrollHeight);
        });

        $(chatBox).on('DOMSubtreeModified', function() {
            if (isScrolledToBottom()) {
                chatBox.animate({ scrollTop: chatBox[0].scrollHeight }, 500);
            }
        });
    });

</script>
<div id="cookie-popup">
    <p>Цей сайт використовує файли cookie для покращення вашого досвіду. <a href="/privacy-policy.php">Дізнатися більше</a></p>
    <button id="accept-cookies">Прийняти</button>
</div>
<script>
    document.getElementById("accept-cookies").addEventListener("click", function () {
        document.cookie = "cookieConsent=true; path=/; max-age=31536000";
        document.getElementById("cookie-popup").style.display = "none";
    });
</script>
</body>
</html>