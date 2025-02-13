<?php
require_once __DIR__ . '/../config/AppConfig.php';
require_once __DIR__ . '/../core/DB.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
    if (isset($_SESSION['user']) && is_string($_SESSION['user'])) {
        $_SESSION['user'] = unserialize($_SESSION['user']);
    }

    if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
        require_once __DIR__ . '/../views/layouts/header.php';
        echo "
        <script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
        <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        
        <div class='alert alert-warning alert-dismissible fade show' role='alert'>
          <strong>Увага!</strong> Для того щоб залишити відгук, вам потрібно увійти в систему.
          <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Закрити'></button>
        </div>
        
        <script>
          setTimeout(function() {
            window.location.href = '/users/login';
          }, 3000);
        </script>";
        require_once __DIR__ . '/../views/layouts/footer.php';
        error_log("Користувач не увійшов в систему. Сесія: " . print_r($_SESSION, true), 3, 'D:\wamp64\domains\Layttle\error_cms.txt');
        exit();
    }
}

$config = \core\AppConfig::get();
$db = new \mysqli($config->dbHost, $config->dbLogin, $config->dbPassword, $config->dbName);

if ($db->connect_error) {
    die('Помилка підключення до бази даних: ' . $db->connect_error);
}

$product_id = $_GET['product_id'];
$user_id = $_SESSION['user']['id'];

$check_review_stmt = $db->prepare("SELECT COUNT(*) FROM reviews WHERE product_id = ? AND user_id = ?");
$check_review_stmt->bind_param('ii', $product_id, $user_id);
$check_review_stmt->execute();
$check_review_stmt->bind_result($review_count);
$check_review_stmt->fetch();
$check_review_stmt->close();

if ($review_count > 0) {
    require_once __DIR__ . '/../views/layouts/header.php';
    echo "
    <script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    
    <div class='alert alert-warning alert-dismissible fade show' role='alert'>
      <strong>Увага!</strong> Ви вже залишили відгук для цього товару. Перейдіть до інших товарів.
      <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Закрити'></button>
    </div>
    
    <script>
      setTimeout(function() {
        window.location.href = '/';
      }, 3000);
    </script>";
    require_once __DIR__ . '/../views/layouts/footer.php';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = $_POST['rating'];
    $pros = $_POST['pros'];
    $cons = $_POST['cons'];
    $comment = $_POST['comment'];

    if (!empty($product_id) && !empty($user_id) && !empty($rating) && !empty($pros) && !empty($cons) && !empty($comment)) {
        $stmt = $db->prepare("INSERT INTO reviews (product_id, user_id, rating, pros, cons, comment, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");

        $stmt->bind_param('iiisss', $product_id, $user_id, $rating, $pros, $cons, $comment);

        if ($stmt->execute()) {
            header('Location: /reviews/view.php?product_id=' . $product_id);
            exit();
        } else {
            echo '<div class="alert alert-danger">Помилка: ' . $stmt->error . '</div>';
        }

        $stmt->close();
    } else {
        echo '<div class="alert alert-danger">Будь ласка, заповніть всі поля форми.</div>';
    }
}

$db->close();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Залиште відгук</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php require_once __DIR__ . '/../views/layouts/header.php'; ?>
<div class="container mt-5">
    <h3 class="text-center mb-4">Залиште відгук про товар</h3>
    <form method="POST" class="shadow p-4 rounded bg-light">
        <input type="hidden" name="product_id" value="<?= $_GET['product_id'] ?>">

        <div class="mb-3">
            <label for="rating" class="form-label">Оцініть товар (1-5):</label>
            <input type="number" name="rating" class="form-control" min="1" max="5" required>
        </div>

        <div class="mb-3">
            <label for="pros" class="form-label">Переваги:</label>
            <textarea name="pros" class="form-control" rows="3" placeholder="Що вам сподобалось?" required></textarea>
        </div>

        <div class="mb-3">
            <label for="cons" class="form-label">Недоліки:</label>
            <textarea name="cons" class="form-control" rows="3" placeholder="Що вам не сподобалось?" required></textarea>
        </div>

        <div class="mb-3">
            <label for="comment" class="form-label">Коментар:</label>
            <textarea name="comment" class="form-control" rows="4" placeholder="Додайте коментар про товар" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary w-100">Залишити відгук</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php require_once __DIR__ . '/../views/layouts/footer.php'; ?>
</body>
</html>
