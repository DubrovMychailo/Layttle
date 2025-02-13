<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$_SESSION = [];

session_destroy();

header("Location: /login.php");
exit;

/** @var string $error_message Повідомлення про помилку */
$this->Title = 'Logout';
?>
<div class="alert alert-success" role="alert">
    You have successfully logged out.
</div>
