<?php

use core\AppConfig;

require_once '../config/AppConfig.php';
require_once '../core/DB.php';

$product_id = $_POST['product_id'];
$sale_price = $_POST['sale_price'];

$config = AppConfig::get();
$db = new mysqli($config->dbHost, $config->dbLogin, $config->dbPassword, $config->dbName);

$stmt = $db->prepare("UPDATE products SET sale_price = ? WHERE id = ?");
$stmt->bind_param('di', $sale_price, $product_id);

header("Location: /");

$stmt->close();
$db->close();

