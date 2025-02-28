<?php
require_once __DIR__ . '/../config/AppConfig.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/DB.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

use vendor\layttle\config\AppConfig;
use vendor\layttle\core\Session;
use vendor\layttle\core\DB;

$session = new Session();
require_once __DIR__ . '/../views/layouts/header.php';

error_log("Session data: " . print_r($_SESSION, true));

$user = $session->get('user');
$user_id = $user['id'] ?? null;

$session->set('user_id', $user_id);
error_log("User ID after getting from session: " . print_r($user_id, true));

if (empty($user_id)) {
    echo "
    <script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    
    <div class='alert alert-warning alert-dismissible fade show' role='alert'>
      <strong>Увага!</strong> Для того щоб додати товар до кошику, вам потрібно увійти в систему.
      <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Закрити'></button>
    </div>
    
    <script>
      setTimeout(function() {
        window.location.href = '/users/login';
      }, 3000);
    </script>";
    require_once __DIR__ . '/../views/layouts/footer.php';
    exit();
}

$config = AppConfig::get();
$db = new DB($config->dbHost, $config->dbName, $config->dbLogin, $config->dbPassword);
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);

if (!empty($product_id)) {
    $stmt = $db->pdo->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $existingProduct = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingProduct) {
        $newQuantity = $existingProduct['quantity'] + 1;
        $updateStmt = $db->pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $updateStmt->execute([$newQuantity, $user_id, $product_id]);
    } else {
        $stmt = $db->pdo->prepare("SELECT price, sale_price FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        $insertStmt = $db->pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
        $insertStmt->execute([$user_id, $product_id]);
    }
    header('Location: /views/cart/cart.php');
    exit();
} else {
    echo "Error: Не вдалося додати товар до кошика.";
}
?>
