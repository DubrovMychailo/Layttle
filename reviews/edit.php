<?php
require_once __DIR__ . '/../config/AppConfig.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/DB.php';

$session = new \core\Session();
$currentUser = $session->get('user');
if (!$currentUser) {
    header('Location: /users/login.php');
    exit();
}

$review_id = $_GET['review_id'];

$config = \core\AppConfig::get();
$db = new \mysqli($config->dbHost, $config->dbLogin, $config->dbPassword, $config->dbName);

if ($db->connect_error) {
    die('Помилка підключення до бази даних: ' . $db->connect_error);
}

$stmt = $db->prepare("SELECT * FROM reviews WHERE id = ? AND user_id = ?");
$stmt->bind_param('ii', $review_id, $currentUser['id']);
$stmt->execute();
$result = $stmt->get_result();
$review = $result->fetch_assoc();

if (!$review) {
    die('Відгук не знайдено або у вас немає доступу до його редагування.');
}

$product_id = $review['product_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = $_POST['rating'];
    $pros = $_POST['pros'];
    $cons = $_POST['cons'];
    $comment = $_POST['comment'];

    $updateStmt = $db->prepare("UPDATE reviews SET rating = ?, pros = ?, cons = ?, comment = ? WHERE id = ?");
    $updateStmt->bind_param('isssi', $rating, $pros, $cons, $comment, $review_id);

    if ($updateStmt->execute()) {
        header('Location: /reviews/view.php?product_id=' . $product_id);
        exit();
    } else {
        echo 'Помилка при оновленні відгуку.';
    }
}

$db->close();
?>


<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редагування відгуку</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php require_once __DIR__ . '/../views/layouts/header.php'; ?>
<div class="container mt-5">
    <h3 class="text-center mb-4">Редагування відгуку</h3>

    <form action="" method="post">
        <div class="mb-3">
            <label for="rating" class="form-label">Оцінка</label>
            <input type="number" class="form-control" id="rating" name="rating" value="<?= $review['rating'] ?>" max="5" min="1" required>
        </div>
        <div class="mb-3">
            <label for="pros" class="form-label">Переваги</label>
            <textarea class="form-control" id="pros" name="pros" required><?= htmlspecialchars($review['pros'], ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
        <div class="mb-3">
            <label for="cons" class="form-label">Недоліки</label>
            <textarea class="form-control" id="cons" name="cons" required><?= htmlspecialchars($review['cons'], ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
        <div class="mb-3">
            <label for="comment" class="form-label">Коментар</label>
            <textarea class="form-control" id="comment" name="comment" required><?= htmlspecialchars($review['comment'], ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Зберегти зміни</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php require_once __DIR__ . '/../views/layouts/footer.php'; ?>
</body>
</html>
