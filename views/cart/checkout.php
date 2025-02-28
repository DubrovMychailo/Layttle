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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $deliveryMethod = isset($_POST['delivery_method']) ? $_POST['delivery_method'] : null;
    $paymentMethod = isset($_POST['payment_method']) ? $_POST['payment_method'] : null;
    $recipientSurname = isset($_POST['surname']) ? trim($_POST['surname']) : '';
    $recipientName = isset($_POST['name']) ? trim($_POST['name']) : '';
    $recipientPatronymic = isset($_POST['patronymic']) ? trim($_POST['patronymic']) : '';
    $phoneNumber = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $orderComment = isset($_POST['comment_user']) ? trim($_POST['comment_user']) : '';
    $selectedAddress = isset($_POST['address']) ? trim($_POST['address']) : '';
    $country = isset($_POST['country']) ? trim($_POST['country']) : '';
    $city = isset($_POST['city']) ? trim($_POST['city']) : '';
    $stmt = $db->pdo->prepare("
        SELECT c.product_id, p.name, p.price, c.quantity, (p.price * c.quantity) AS total
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$userId]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($cartItems) > 0) {
        $totalAmount = array_sum(array_column($cartItems, 'total'));

        $stmt = $db->pdo->prepare("
            INSERT INTO orders_history 
            (user_id, address, phone, email, surname, name, patronymic, comment_user, payment_method, country, city, total_amount, created_at, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'pending')
        ");
        $stmt->execute([$userId, $selectedAddress, $phoneNumber, $email, $recipientSurname, $recipientName, $recipientPatronymic, $orderComment, $paymentMethod, $country, $city, $totalAmount]);

        $orderId = $db->pdo->lastInsertId();

        $stmt = $db->pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);

        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Замовлення успішно оформлено!'];

        header('Location: /');
        exit();
    } else {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Твій кошик пустий!'];
        header('Location: /views/cart/cart.php');
        exit();
    }
}

$stmt = $db->pdo->query("SELECT * FROM post_offices");
$warehouses = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оформлення замовлення</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <link rel="stylesheet" href="/css/Checkout.css">
</head>
<body>
<div class="container mt-4">
    <h2>Оформлення замовлення</h2>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['flash_message']['type']) ?>">
            <?= htmlspecialchars($_SESSION['flash_message']['message']) ?>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>
    <form action="checkout.php" method="POST">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                </div>
                <div class="mb-3">
                    <label for="payment_method" class="form-label">Cпосіб оплати</label>
                    <select id="payment_method" name="payment_method" class="form-select" required>
                        <option value="cash_on_delivery">Оплата при отриманні</option>
                        <option value="card">Онлайн на картку</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="country" class="form-label">Країна</label>
                    <input type="text" id="country" name="country" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="city" class="form-label">Місто</label>
                    <input type="text" id="city" name="city" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Е-пошта</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="surname" class="form-label">Прізвище</label>
                    <input type="text" id="surname" name="surname" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label">Ім'я</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="patronymic" class="form-label">По батькові</label>
                    <input type="text" id="patronymic" name="patronymic" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Номер телефону</label>
                    <input type="text" id="phone" name="phone" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="comment" class="form-label">Залишити коментар до товару</label>
                    <textarea id="comment" name="comment_user" class="form-control"></textarea>
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">Вибрати адресу</label>
                    <input type="text" id="address" name="address" class="form-control" required readonly>
                </div>
                <button type="submit" class="btn btn-dark-green">Оформити замовлення</button>
            </div>
            <div class="col-md-6">
                <h4>Вибрати поштове віділленя на мапі</h4>
                <div id="map" style="height: 900px; width: 730px;"></div>
            </div>
        </div>
    </form>
</div>

<script>
    let map = L.map('map').setView([48.3794, 31.1656], 6); // Центр України

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    let warehouses = <?php echo json_encode($warehouses); ?>;
    console.log(warehouses);

    if (warehouses.length > 0) {
        warehouses.forEach(function(warehouse) {
            if (warehouse.latitude && warehouse.longitude) {
                let marker = L.marker([warehouse.latitude, warehouse.longitude]).addTo(map);
                marker.bindPopup(warehouse.name);
                marker.on('click', function() {
                    document.getElementById('address').value = warehouse.name;
                });
            } else {
                console.error("Координати відсутні для відділення: " + warehouse.name);
            }
        });
    } else {
        console.log("Немає доступних відділень.");
    }
</script>
<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
</body>
</html>
