<?php
require_once __DIR__ . '/../../models/Users.php';

use models\Users;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$isUserLogged = Users::IsUserLogged();
$userRole = $isUserLogged ? Users::getUserRole() : '';
$userPhoto = '/uploads/nophoto.webp';

if ($isUserLogged) {
    $route = '/Layttle';
    $user = \core\Core::get($route)->session->get('user');
    $userId = $user['id'];
    $userFromDb = Users::getUserById($userId);
    if ($userFromDb && !empty($userFromDb['photo'])) {
        $userPhoto = '/uploads/' . $userFromDb['photo'];
    }
}

$currentPage = $_SERVER['REQUEST_URI'];
?>
<head>
    <link rel="stylesheet" href="/css/Header.css">
    <link rel="icon" href="//Layttle/uploads/MiraclesWorld.png" type="image/png">
</head>
<header class="p-3 mb-3 border-bottom shadow-sm bg-light">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-between">
            <a href="/" class="d-flex align-items-center mb-2 mb-lg-0 text-dark text-decoration-none">
                <svg class="bi me-2" width="40" height="32" role="img" aria-label="Bootstrap">
                    <use xlink:href="#bootstrap"></use>
                </svg>
                <img src="//Layttle/uploads/Layttle.png" alt="" class="logo-img" style="width: 80px; height: 80px; margin-right: 8px;">
                <span class="fw-bold fs-3 text-uppercase text-dark ">Layttle</span>
            </a>

            <ul class="nav col-12 col-lg-auto ms-auto mb-2 justify-content-end mb-md-0">
                <?php if (!$isUserLogged) : ?>
                    <li><a href="/users/login" class="nav-link px-2 link-dark">
                            <img src="//Layttle/uploads/entry.png" alt="" class="logo-img" style="width: 40px; height: 40px; margin-right: 12px;">
                        </a></li>
                    <li><a href="/users/register" class="nav-link px-2 link-dark">
                            <img src="//Layttle/uploads/registration.png" alt="" class="logo-img" style="width: 40px; height: 40px; margin-right: 12px;">
                        </a></li>
                <?php endif; ?>

                <?php if ($userRole === 'admin') : ?>
                    <li>
                        <a href="/admin/SetSale" class="nav-link px-2 link-dark">
                            <img src="//Layttle/uploads/Discount.png" alt="" class="logo-img" style="width: 40px; height: 40px; margin-right: 12px;">
                        </a>
                    </li>
                    <li>
                        <a href="/views/site/add_product.php" class="nav-link px-2 link-dark">
                            <img src="//Layttle/uploads/Downloadclothes.png" alt="" class="logo-img" style="width: 40px; height: 40px; margin-right: 12px;">
                        </a>
                    </li>
                <?php endif; ?>

                <li>
                    <a href="/views/cart/cart" class="nav-link px-2 link-dark">
                        <img src="//Layttle/uploads/Cart.png" alt="" class="logo-img" style="width: 40px; height: 40px; margin-right: 12px;">
                    </a>
                </li>

                <?php if (strpos($currentPage, 'SetSale') === false && strpos($currentPage, 'add_product.php') === false) : ?>
                    <form id="searchForm" class="me-3">
                        <input type="text" id="searchInput" placeholder="Пошук бажаних товарів" required>
                        <button type="submit">Пошук</button>
                    </form>
                <?php endif; ?>

                <script>
                    document.getElementById('searchForm').onsubmit = function(event) {
                        event.preventDefault();
                        const input = document.getElementById('searchInput').value;
                        const terms = input.split(' ');
                        const queryString = terms.map(term => `q=${encodeURIComponent(term)}`).join('&');
                        window.location.href = `search.php?${queryString}`;
                    };
                </script>

                <?php if ($isUserLogged && strpos($currentPage, 'SetSale') === false && strpos($currentPage, 'add_product.php') === false) : ?>
                <div class="dropdown text-end">
                    <a href="#" class="d-block link-dark text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?= htmlspecialchars($userPhoto, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($user['login'], ENT_QUOTES, 'UTF-8') ?>" width="40" height="40" class="rounded-circle border border-dark">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end text-small">
                        <li><a class="dropdown-item" href="/profile/me">Профіль</a></li>
                        <li><a class="dropdown-item" href="/views/chat">Чат</a></li>
                        <li><a class="dropdown-item" href="http://Layttle/views/cart/cart">Кошик</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/users/logout">Вийти</a></li>
                    </ul>
            </ul>
        </div>
        <?php endif; ?>
    </div>
</header>
