<?php
require_once __DIR__ . '/../config/AppConfig.php';
require_once __DIR__ . '/../core/DB.php';
require_once __DIR__ . '/../core/Session.php';

$config = \core\AppConfig::get();

$db = new \mysqli($config->dbHost, $config->dbLogin, $config->dbPassword, $config->dbName);

if ($db->connect_error) {
    die('Помилка підключення до бази даних: ' . $db->connect_error);
}

$product_id = $_GET['product_id'];

$session = new \core\Session();
$currentUser = $session->get('user');

$stmt = $db->prepare("SELECT r.*, u.firstname, u.lastname FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ?");
$stmt->bind_param('i', $product_id);
$stmt->execute();
$result = $stmt->get_result();
$reviews = $result->fetch_all(MYSQLI_ASSOC);


$stmt->close();
$db->close();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Відгуки про товар</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php require_once __DIR__ . '/../views/layouts/header.php'; ?>
<div class="container mt-5">
    <h3 class="text-center mb-4">Відгуки про товар</h3>

    <?php if (count($reviews) > 0): ?>
        <div class="list-group">
            <?php foreach ($reviews as $review): ?>
                <div class="list-group-item list-group-item-action mb-3 shadow-sm p-4 rounded">
                    <h5 class="mb-3">Оцінка:
                        <span class="badge bg-primary"><?= $review['rating'] ?>/5</span>
                    </h5>
                    <p class="mb-2"><strong>Користувач:</strong> <?= htmlspecialchars($review['firstname'], ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($review['lastname'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="mb-2"><strong>Дата:</strong> <?= date('d-m-Y H:i', strtotime($review['created_at'])) ?></p>
                    <p class="mb-2"><strong>Переваги:</strong> <?= htmlspecialchars($review['pros'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="mb-2"><strong>Недоліки:</strong> <?= htmlspecialchars($review['cons'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>Коментар:</strong> <?= htmlspecialchars($review['comment'], ENT_QUOTES, 'UTF-8') ?></p>

                    <?php if ($currentUser && $currentUser['id'] == $review['user_id']): ?>
                        <div class="d-flex justify-content-end mt-3">
                            <a href="/reviews/edit.php?review_id=<?= $review['id'] ?>" class="btn btn-warning me-2">Редагувати</a>
                            <a href="/reviews/delete.php?review_id=<?= $review['id'] ?>" class="btn btn-danger">Видалити</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center">Немає відгуків про цей товар.</div>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php require_once __DIR__ . '/../views/layouts/footer.php'; ?>
</body>
</html>
