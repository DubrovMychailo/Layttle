<?php
spl_autoload_register(function ($class) {

    $class = ltrim(str_replace('\\', DIRECTORY_SEPARATOR, $class), DIRECTORY_SEPARATOR);

    $directories = [
        __DIR__,
        __DIR__ . '/models',
        __DIR__ . '/controllers',
    ];

    foreach ($directories as $directory) {
        $file = $directory . '/' . $class . '.php';

        echo "Шукаємо файл: $file <br>";

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }

    throw new Exception("Файл для класу $class не знайдено.");
});
