<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/Header.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Редагування профілю</title>
    <?php require_once __DIR__ . '/../../views/layouts/header.php'; ?>
    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 800px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 50px;
        }
        h2 {
            font-weight: bold;
            color: #1877f2;
            text-align: center;
        }
        .btn-success {
            background-color: #1877f2;
            border: none;
        }
        .btn-success:hover {
            background-color: #145dbf;
        }
        .form-label {
            font-weight: 600;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>Редагувати профіль</h2>

    <?php if (isset($response['errors']) && !empty($response['errors'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars(implode('<br>', $response['errors'])) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($response['success']) && $response['success']): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($response['message'] ?? 'Профіль оновлено!') ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" action="/profile/me">
        <div class="mb-3 text-center">
            <label for="photo" class="form-label">Фото профілю:</label>
            <input type="file" name="photo" id="photo" class="form-control">
        </div>
        <div class="mb-3">
            <label for="firstname" class="form-label">Ім’я:</label>
            <input type="text" name="firstname" id="firstname" class="form-control" value="<?= htmlspecialchars($userData['firstname'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="lastname" class="form-label">Прізвище:</label>
            <input type="text" name="lastname" id="lastname" class="form-control" value="<?= htmlspecialchars($userData['lastname'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Електронна пошта:</label>
            <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($userData['email'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Телефон:</label>
            <input type="text" name="phone" id="phone" class="form-control" value="<?= htmlspecialchars($userData['phone'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Адреса:</label>
            <input type="text" name="address" id="address" class="form-control" value="<?= htmlspecialchars($userData['address'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="city" class="form-label">Місто:</label>
            <input type="text" name="city" id="city" class="form-control" value="<?= htmlspecialchars($userData['city'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="country" class="form-label">Країна:</label>
            <input type="text" name="country" id="country" class="form-control" value="<?= htmlspecialchars($userData['country'] ?? '') ?>">
        <button type="submit" class="btn btn-success w-100">Зберегти зміни</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
</html>
