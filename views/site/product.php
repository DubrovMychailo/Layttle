<?php
use vendor\layttle\models\Users;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/** @var string $Title */
/** @var string $Content */
/** @var string $searchQuery */
if (empty($Title)) $Title = 'Пошук продуктів';
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
    <link rel="icon" href="//cms/Miracles World.png" type="image/png">
    <link rel="stylesheet" href="/css/Product.css">
</head>

<body>
<?php include_once __DIR__ . '/header.php'; ?>
<div class="container">
    <h1><?= htmlspecialchars($Title, ENT_QUOTES, 'UTF-8') ?></h1>
    <?= $Content ?>

    <?php
    $currentUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($currentUrl == '/Layttle/' || $currentUrl == '/' || isset($_GET['q'])):
        ?>
        <div class="container">
            <div class="row">
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $product): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <?php
                                $imagePath = '/views/site/uploads/' . htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8');
                                ?>
                                <img src="<?= $imagePath ?>" class="card-img-top product-image" alt="Product Image" onerror="this.onerror=null; this.src='/Layttle/views/site/uploads/default.png'">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h5>

                                    <?php
                                    $avgRatingResult = $db->select('reviews', 'AVG(rating) as avgRating', 'WHERE product_id = ?', [$product['id']]);
                                    $avgRating = $avgRatingResult ? $avgRatingResult[0]['avgRating'] : 0;
                                    $roundedRating = round($avgRating ?? 0, 1);
                                    ?>

                                    <div class="product-description" id="desc-<?= $product['id'] ?>" style="display: none; color: #404040;">
                                        <p class="card-text"><?= nl2br(htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8')) ?></p>

                                    </div>

                                    <button class="btn btn-toggle" onclick="toggleDescription(<?= $product['id'] ?>)" id="toggle-btn-<?= $product['id'] ?>">Переглянути опис ▼</button>
                                    <div class="rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="star <?= $i <= $roundedRating ? 'filled' : '' ?>">&#9733;</span>
                                        <?php endfor; ?>
                                        (<?= $roundedRating ?>/5)
                                    </div>
                                    <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                                        <p>
                                            <span class="strike-through"><?= htmlspecialchars($product['price'], ENT_QUOTES, 'UTF-8') ?> UAH</span>
                                            <span class="sale-price"><?= htmlspecialchars($product['sale_price'], ENT_QUOTES, 'UTF-8') ?> UAH</span>
                                        </p>
                                    <?php else: ?>
                                        <p>Ціна: <?= htmlspecialchars($product['price'], ENT_QUOTES, 'UTF-8') ?> UAH</p>
                                    <?php endif; ?>

                                    <div class="button-container">
                                        <div class="d-flex gap-2">
                                            <form action="/scripts_cart/addToCart.php" method="POST" class="d-inline flex-grow-1">
                                                <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                                                <button type="submit" class="btn btn-dark-green">Додати до кошику</button>
                                            </form>
                                            <button class="btn btn-dark-green" onclick="location.href='/reviews/write.php?product_id=<?= $product['id'] ?>'">Написати відгук</button>
                                            <button class="btn btn-dark-green" onclick="location.href='/reviews/view.php?product_id=<?= $product['id'] ?>'">Переглянути відгуки</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Не знайдено товарів, що відповідають вашому запиту</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php include_once __DIR__ . '/footer.php'; ?>
<script>
    function toggleDescription(productId) {
        const description = document.getElementById('desc-' + productId);
        const toggleButton = document.getElementById('toggle-btn-' + productId);

        if (description.style.display === 'none') {
            description.style.display = 'block';
            toggleButton.innerHTML = 'Згорнути опис ▲';
            toggleButton.style.color = 'coal';
        } else {
            description.style.display = 'none';
            toggleButton.innerHTML = 'Переглянути опис ▼';
            toggleButton.style.color = 'metal';
        }
    }
</script>
</body>
</html>