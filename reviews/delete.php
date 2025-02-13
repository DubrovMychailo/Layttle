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

$stmt = $db->prepare("SELECT product_id FROM reviews WHERE id = ? AND user_id = ?");
$stmt->bind_param('ii', $review_id, $currentUser['id']);
$stmt->execute();
$result = $stmt->get_result();
$review = $result->fetch_assoc();

if (!$review) {
    die('Відгук не знайдено або у вас немає прав для його видалення.');
}

$product_id = $review['product_id'];

$deleteStmt = $db->prepare("DELETE FROM reviews WHERE id = ? AND user_id = ?");
$deleteStmt->bind_param('ii', $review_id, $currentUser['id']);
$deleteStmt->execute();

if ($deleteStmt->affected_rows > 0) {
    header('Location: /reviews/view.php?product_id=' . $product_id);
    exit();
} else {
    die('Помилка при видаленні відгуку.');
}

$deleteStmt->close();
$db->close();
?>
