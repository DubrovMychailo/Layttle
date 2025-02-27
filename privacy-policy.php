<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Політика конфіденційності</title>
</head>
<body>

<div class="container">
    <main class="policy-container">
        <h1>Політика конфіденційності</h1>
        <div class="policy-content">
            <?php
            $markdownText = file_get_contents(__DIR__ . '/privacy-policy.md');
            echo nl2br(htmlspecialchars($markdownText));
            ?>
        </div>
    </main>
</div>
</body>
</html>
