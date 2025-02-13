<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../../core/Config.php';

use core\Config;

$pdo = Config::get()->getPDO();

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT id, firstname, lastname FROM users WHERE id != :current_user_id";
if ($search) {
    $sql .= " AND (firstname LIKE :search OR lastname LIKE :search)";
}

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':current_user_id', PDO::PARAM_INT);
if ($search) {
    $stmt->bindValue(':search', '%' . $search . '%');
}
$stmt->execute();

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <title>Пошук користувачів</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous"/>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/IndexMain.css">
    <link rel="stylesheet" href="/css/Header.css">
    <link rel="stylesheet" href="/css/Chat_index.css">
    <?php require_once __DIR__ . '/../../views/layouts/header.php'; ?>
</head>
<body>
<div class="search">
    <h1>Пошук користувачів</h1>

    <form method="get" action="/views/chat">
        <input type="text" name="search" class="search-box" placeholder="Пошук за іменем або прізвищем" value="<?= htmlspecialchars($search) ?>" />
    </form>

    <?php if (empty($users)): ?>
        <p>Користувачів не знайдено.</p>
    <?php else: ?>
        <ul class="user-list">
            <?php foreach ($users as $user): ?>
                <li class="user-item">
                    <a href="/views/chat/chat.php?user_id=<?= $user['id'] ?>">
                        <?= htmlspecialchars($user['firstname']) ?> <?= htmlspecialchars($user['lastname']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
</body>
<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
</html>
