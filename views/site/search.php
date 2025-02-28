<?php
use vendor\layttle\models\Users;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/** @var string $Title */
/** @var string $Content */
/** @var string $searchQuery */
if (empty($Title)) $Title = '';
if (empty($Content)) $Content = '';
if (empty($searchQuery)) $searchQuery = '';

if (isset($_SESSION['flash_message'])): ?>
    <div class="container mt-4">
        <div class="alert alert-<?php echo htmlspecialchars($_SESSION['flash_message']['type']); ?>">
            <?php echo htmlspecialchars($_SESSION['flash_message']['message']); ?>
        </div>
    </div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>

<?php
require_once __DIR__ . '/../../config/AppConfig.php';
require_once __DIR__ . '/../../core/DB.php';

use vendor\layttle\config\AppConfig;
use vendor\layttle\core\DB;

$config = AppConfig::get();
$db = new DB($config->dbHost, $config->dbName, $config->dbLogin, $config->dbPassword);

if (!$db->pdo) {
    die('Проблема з підключенням до бази даних.');
}

$searchQuery = isset($_GET['q']) ? $_GET['q'] : '';

if ($searchQuery) {
    $products = $db->select('products', '*', "(name LIKE ? OR description LIKE ?)", ["%$searchQuery%", "%$searchQuery%"]);
} else {
    $products = $db->select('products', '*');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Miracles Store™ — Ваш улюблений інтернет магазин одягу :D</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous"/>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="icon" href="//cms/Miracles World6.png" type="image/png">
</head>
