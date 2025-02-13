<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/AppConfig.php';
require_once __DIR__ . '/../core/DB.php';

use core\AppConfig;
use core\DB;

$config = AppConfig::get();
$db = new DB($config->dbHost, $config->dbName, $config->dbLogin, $config->dbPassword);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cartId = intval($_POST['cart_id']);
    $quantity = intval($_POST['quantity']);

    if ($quantity > 0) {
        $userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
        $stmt = $db->pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?  AND user_id = ?");
        $stmt->execute([$quantity, $cartId, $userId]);
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Кількість оновлено'];
    } else {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Невірна кількість'];
    }

    header('Location: /views/cart/cart.php');
    exit();
}
