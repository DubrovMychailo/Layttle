<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/AppConfig.php';
require_once __DIR__ . '/../core/DB.php';

use vendor\layttle\config\AppConfig;
use vendor\layttle\core\DB;

$config = AppConfig::get();
$db = new DB($config->dbHost, $config->dbName, $config->dbLogin, $config->dbPassword);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cartId = intval($_POST['cart_id']);
    // Ensure the item belongs to the logged-in user
    $userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
    $stmt = $db->pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cartId,$userId]);
    $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Товар видалено з кошика'];

    header('Location: /views/cart/cart.php');
    exit();
}
