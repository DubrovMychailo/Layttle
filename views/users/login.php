<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/AppConfig.php';
require_once __DIR__ . '/../../core/DB.php';
require_once 'D:\wamp64\domains\layttle\core\Session.php';

use core\AppConfig;
use core\DB;
use core\Session;

$config = AppConfig::get();
$db = new DB($config->dbHost, $config->dbName, $config->dbLogin, $config->dbPassword);
$conn = $db->pdo;

$error_message = '';
$session = new Session();

// Логування старту
error_log("====== START LOGIN PROCESS ======\n", 3, 'D:\wamp64\domains\layttle\error_cms.txt');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    error_log("POST запит отримано\n", 3, 'D:\wamp64\domains\layttle\error_cms.txt');

    $username = $_POST['login'];
    $password = $_POST['password'];

    error_log("Введені дані: Логін: $username\n", 3, 'D:\wamp64\domains\layttle\error_cms.txt');

    if (!$conn) {
        error_log("З'єднання з базою даних НЕ встановлено\n", 3, 'D:\wamp64\domains\layttle\error_cms.txt');
        die('З\'єднання не встановлено');
    } else {
        error_log("З'єднання з базою даних встановлено\n", 3, 'D:\wamp64\domains\layttle\error_cms.txt');
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE login = ?");
    if (!$stmt) {
        error_log("Помилка підготовки SQL-запиту\n", 3, 'D:\wamp64\domains\layttle\error_cms.txt');
        die('Помилка запиту');
    }

    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        error_log("Користувач знайдений: ID={$user['id']}, Login={$user['login']}\n", 3, 'D:\wamp64\domains\layttle\error_cms.txt');

        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'login' => $user['login'],
                'firstname' => $user['firstname'],
                'lastname' => $user['lastname'],
                'photo' => $user['photo']
            ];

            error_log("Сесія встановлена: " . print_r($_SESSION, true) . "\n", 3, 'D:\wamp64\domains\layttle\error_cms.txt');

            echo "<pre>SESSION SET: " . print_r($_SESSION, true) . "</pre>";

            header("Location: ../site/index.php");
            exit();
        } else {
            $error_message = "Невірний пароль";
            error_log("Невірний пароль для користувача: $username\n", 3, 'D:\wamp64\domains\layttle\error_cms.txt');
        }
    } else {
        $error_message = "Невірний логін або пароль";
        error_log("Користувач не знайдений: $username\n", 3, 'D:\wamp64\domains\layttle\error_cms.txt');
    }
}

// Лог сесії після всіх дій
error_log("SESSION після виконання login.php: " . print_r($_SESSION, true) . "\n", 3, 'D:\wamp64\domains\layttle\error_cms.txt');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вхід</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <h3 class="text-center mt-5 mb-4">Увійти в систему</h3>
    <form method="POST" action="">
        <?php if (!empty($error_message)) : ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>
        <div class="mb-3">
            <label for="login" class="form-label">Логін</label>
            <input name="login" type="text" class="form-control" id="login" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Пароль</label>
            <input name="password" type="password" class="form-control" id="password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Увійти</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
