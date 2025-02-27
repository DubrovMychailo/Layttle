<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$userData = $userData ?? null;
$isOwnProfile = $isOwnProfile ?? false;
$friends = $friends ?? [];
$posts = $posts ?? [];
?>

<?php if (!$userData): ?>
    <p class="alert alert-danger">❌ Користувач не знайдений!</p>
<?php else: ?>
    <!DOCTYPE html>
    <html lang="uk">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Чат</title>
        <link rel="stylesheet" href="/css/ProfileController.css">
    </head>
    <body>
    <div class="container mt-4 text-center">
        <img src="/uploads/<?= htmlspecialchars($userData['photo'] ?? 'nophoto.webp') ?>"
             alt="Фото профілю" class="profile-photo">
        <h1 class="mt-3"><?= htmlspecialchars($userData['firstname'] . ' ' . $userData['lastname']) ?></h1>
    </div>


    <div class="container mt-3 text-center">
        <?php if ($isOwnProfile): ?>
            <a href="/views/profile/edit.php" class="edit-btn">Редагувати профіль</a>
            <a href="/posts/create" class="create-btn">Додати допис</a>
        <?php else: ?>
            <a href="/views/chat/chat.php?user_id=<?= $userData['id'] ?>" class="message-btn">Написати повідомлення</a>
        <?php endif; ?>
    </div>



    <div class="container mt-4">
        <h2>Інформація про себе</h2>
        <p><strong>Біографія:</strong> <?= htmlspecialchars($userData['bio'] ?? 'Не вказано') ?></p>
        <p><strong>Адреса:</strong> <?= htmlspecialchars($userData['address'] ?? 'Не вказано') ?></p>
        <p><strong>Телефон:</strong> <?= htmlspecialchars($userData['phone'] ?? 'Не вказано') ?></p>
        <p><strong>Місто:</strong> <?= htmlspecialchars($userData['city'] ?? 'Не вказано') ?></p>
        <p><strong>Країна:</strong> <?= htmlspecialchars($userData['country'] ?? 'Не вказано') ?></p>
        <p><strong>E-mail:</strong> <?= htmlspecialchars($userData['email']) ?></p>
    </div>

    <div class="container mt-4">
        <h2>Список друзів</h2>
        <?php if ($friends): ?>
            <?php foreach ($friends as $friend): ?>
                <p><?= htmlspecialchars($friend['firstname'] . ' ' . $friend['lastname']) ?></p>
            <?php endforeach; ?>
        <?php else: ?>
            <p>У вас поки немає друзів.</p>
        <?php endif; ?>
    </div>

    <div class="container mt-4">
        <h2>Стрічка контенту</h2>
        <?php if ($posts): ?>
            <?php foreach ($posts as $post): ?>
                <div class="post mb-3">
                    <p><?= htmlspecialchars($post['content']) ?></p>
                    <?php if (!empty($post['photo'])): ?>
                        <img src="/uploads/posts/<?= htmlspecialchars($post['photo']) ?>"
                             alt="Фото допису" class="img-fluid">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>У вас поки немає дописів.</p>
        <?php endif; ?>
    </div>
    </body>
    </html>
<?php endif; ?>
