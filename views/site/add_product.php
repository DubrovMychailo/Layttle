<?php
require_once '../../autoload.php';
require_once '../../core/DB.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start();
$header = ob_get_clean();
?>

<!DOCTYPE html>
<html lang="en">
<?php include '../layouts/header.php'; ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Додати товар до магазину</title>
    <link rel="icon" href="/uploads/MiraclesWorld.png" type="image/png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/Add_product.css">
</head>
<body>

<?php echo $header; ?>

<div class="container mt-5">
    <div class="card shadow-lg form-container">
        <div class="card-body">
            <h3 class="card-title text-center">Додати товар до магазину</h3>
            <form action="process_add_product.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Назва:</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Введіть назву товару" required>
                </div>

                <div class="form-group">
                    <label for="description">Опис:</label>
                    <textarea name="description" id="description" class="form-control" rows="4" placeholder="Опис товару" required></textarea>
                </div>

                <div class="form-group">
                    <label for="price">Ціна:</label>
                    <input type="number" name="price" id="price" class="form-control" placeholder="Ціна у гривнях" required>
                </div>

                <div class="form-group">
                    <label for="release_date">Дата випуску:</label>
                    <input type="date" name="release_date" id="release_date" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="image">Зображення:</label>
                    <input type="file" name="image" id="image" class="form-control-file" required>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-dark-green btn-block">Додати продукт</button>
                </div>
            </form>
        </div>
    </div>

    <?php include '../layouts/footer.php'; ?>

</body>
</html>
