<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../../core/DB.php';

use vendor\layttle\core\DB;

$host = 'localhost';
$dbname = 'Layttle';
$username = 'Dubrov';
$password = '2004Dubrov';

$db = new DB($host, $dbname, $username, $password);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $release_date = $_POST['release_date'];

    $uploadsDir = __DIR__ . '/uploads';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0777, true);
    }

    $imageName = basename($_FILES['image']['name']);
    $targetFilePath = $uploadsDir . '/' . $imageName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
        $productData = [
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'image' => $imageName,
            'release_date' => $release_date
        ];

        if ($db->insert('products', $productData)) {
            echo "Продукт успішно доданий.";
            header('Location: /');
            exit;
        } else {
            echo "Помилка при додаванні продукту.";
        }
    } else {
        echo "Не вдалося завантажити зображення.";
    }
}
