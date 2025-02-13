<?php
/** @var string $error_message Повідомлення про помилку */
$this->Title = 'Реєстрація нового користувача';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login']);
    $password = trim($_POST['password']);
    $password2 = trim($_POST['password2']);
    $lastname = trim($_POST['lastname']);
    $firstname = trim($_POST['firstname']);

    if (empty($login) || empty($password) || empty($password2) || empty($lastname) || empty($firstname)) {
        $error_message = 'Будь ласка, заповніть усі обов\'язкові поля.';
    } elseif ($password !== $password2) {
        $error_message = 'Паролі не збігаються.';
    } else {

        $pdo = new PDO('mysql:host=localhost;dbname=layttle', 'Dubrov', '2004Dubrov');
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE login = :login OR email = :email");
        $stmt->execute(['login' => $login, 'email' => $login]); // Використання одного поля для логіну та email
        $exists = $stmt->fetchColumn();

        if ($exists) {
            $error_message = 'Логін або пошта вже існують на сайті, змініть на щось інше.';
        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (login, password, lastname, firstname) VALUES (:login, :password, :lastname, :firstname)");
            $stmt->execute([
                'login' => $login,
                'password' => $hashed_password,
                'lastname' => $lastname,
                'firstname' => $firstname
            ]);

            header("Location: /Layttle/login.php");
            exit;
        }
    }
}
?>

    <!DOCTYPE html>
    <html lang="uk">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Реєстрація</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    </head>
    <body>
    <div class="container">
        <h3 class="text-center mt-5 mb-4">Реєстрація нового користувача</h3>
        <form method="POST" action="">
            <?php if (!empty($error_message)) : ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>
            <div class="mb-3">
                <label for="login" class="form-label">Логін</label>
                <input name="login" type="text" class="form-control" id="login" value="<?= htmlspecialchars($_POST['login'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Пароль</label>
                <input name="password" type="password" class="form-control" id="password" required>
            </div>
            <div class="mb-3">
                <label for="password2" class="form-label">Пароль (ще раз)</label>
                <input name="password2" type="password" class="form-control" id="password2" required>
            </div>
            <div class="mb-3">
                <label for="lastname" class="form-label">Прізвище</label>
                <input name="lastname" type="text" class="form-control" id="lastname" value="<?= htmlspecialchars($_POST['lastname'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div class="mb-3">
                <label for="firstname" class="form-label">Ім'я</label>
                <input name="firstname" type="text" class="form-control" id="firstname" value="<?= htmlspecialchars($_POST['firstname'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Зареєструватися</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
<?php
