<?php
include __DIR__ . '/../../controllers/ProfileController.php';

use vendor\layttle\controllers\ProfileController;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new ProfileController();
    $response = $controller->validateAndUpdateProfile($_POST, $_FILES);

    if ($response['success']) {
        header('Location: /profile');
        exit;
    } else {
        $_SESSION['response'] = $response;
        header('Location: /profile/edit');
        exit;
    }
} else {
    header('Location: /profile/edit');
    exit;
}
