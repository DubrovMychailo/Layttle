<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/AppConfig.php';
require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../views/layouts/header.php';

use vendor\layttle\config\AppConfig;
use vendor\layttle\core\DB;

$config = AppConfig::get();
$db = new DB($config->dbHost, $config->dbName, $config->dbLogin, $config->dbPassword);

$userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

if ($userId > 0) {
    $stmt = $db->pdo->prepare("
        SELECT c.id, p.name, p.price, p.sale_price, c.quantity
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$userId]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $cartItems = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Перегляд вашого кошику</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="/css/Cart.css">
</head>
<body>
<div class="container mt-4">
    <h1>Ваш кошик</h1>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['flash_message']['type']) ?>">
            <?= htmlspecialchars($_SESSION['flash_message']['message']) ?>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <?php if (count($cartItems) > 0): ?>
        <div class="row">
            <div class="col-md-12">
                <h4>Вартість за товари</h4>
                <table class="table">
                    <thead>
                    <tr>
                        <th>Товар</th>
                        <th>Кількість</th>
                        <th>Ціна</th>
                        <th>Вартість</th>
                        <th>Видалити</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $totalCost = 0;
                    foreach ($cartItems as $item):
                        $itemTotal = ($item['sale_price'] ? $item['sale_price'] : $item['price']) * $item['quantity'];
                        $totalCost += $itemTotal;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <form action="/scripts_cart/updateCart.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="cart_id" value="<?= htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="number" name="quantity" value="<?= htmlspecialchars($item['quantity'], ENT_QUOTES, 'UTF-8') ?>" min="1" class="form-control" style="width: 80px; display: inline-block;">
                                    <button type="submit" class="btn btn-primary btn-sm">⟳</button>
                                </form>
                            </td>
                            <td>
                                <?php if ($item['sale_price'] && $item['sale_price'] < $item['price']): ?>
                                    <span class="strike-through"><?= htmlspecialchars($item['price'], ENT_QUOTES, 'UTF-8') ?> UAH</span>
                                    <span class="sale-price"><?= htmlspecialchars($item['sale_price'], ENT_QUOTES, 'UTF-8') ?> UAH</span>
                                <?php else: ?>
                                    <?= htmlspecialchars($item['price'], ENT_QUOTES, 'UTF-8') ?> UAH
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($itemTotal, ENT_QUOTES, 'UTF-8') ?> UAH</td>
                            <td>
                                <form action="/scripts_cart/removeFromCart.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="cart_id" value="<?= htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Видалити</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" class="text-end"><strong>До сплати:</strong></td>
                        <td><strong><?= $totalCost ?> UAH</strong></td>
                        <td></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 text-end">
                <a href="/views/cart/checkout.php" class="btn btn-success">Оформити замовлення</a>
            </div>
        </div>
    <?php else: ?>
        <p>Ваш кошик порожній.</p>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>

</body>
</html>
