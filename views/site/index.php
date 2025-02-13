<?php
require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../config/AppConfig.php';

use core\DB;
use core\AppConfig;
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$config = AppConfig::get();

$db = new DB($config->dbHost, $config->dbName, $config->dbLogin, $config->dbPassword);
$conn = $db->pdo;

if (!$conn) {
    die("Помилка підключення до бази даних.");
}

if ($conn->errorCode()) {
    die("Помилка підключення: " . $conn->errorInfo()[2]);
}
?>

