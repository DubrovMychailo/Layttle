<?php
require_once '../config/AppConfig.php';
require_once '../core/DB.php';

$config = \core\AppConfig::get();
$db = new mysqli($config->dbHost, $config->dbLogin, $config->dbPassword, $config->dbName);
$result = $db->query("SELECT id, name, price FROM products");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Встановити знижку для товару</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/SetSale.css">
    <link rel="stylesheet" href="/css/Header.css">
</head>
<?php require_once __DIR__ . '/../views/layouts/header.php'; ?>
<body>
<div class="container mt-5">
    <div class="card shadow-lg form-container">
        <div class="card-body">
            <h3 class="card-title text-center">Встановити знижку для товару</h3>
            <form action="setSalePrice.php" method="POST">
                <div class="form-group">
                    <label for="product_id">Оберіть бажаний товар:</label>
                    <select name="product_id" class="form-control">
                        <?php while ($product = $result->fetch_assoc()) { ?>
                            <option value="<?= $product['id'] ?>">
                                <?= $product['name'] ?> (Поточна ціна: <?= $product['price' ]  ?> UAH)
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="sale_price">Нова акційна ціна:</label>
                    <input type="number" step="0.01" name="sale_price" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-dark-green btn-block">Встановити нову ціну</button>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
<?php require_once __DIR__ . '/../views/layouts/footer.php'; ?>
</html>