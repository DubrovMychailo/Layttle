<?php

namespace core;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

class Config
{
    protected $params;
    public static $instance;
    private $pdo;

    /**
     * @throws \Exception
     */
    private function __construct()
    {
        $directory = __DIR__ . '/../config';

        if (!is_dir($directory)) {
            throw new \Exception("Directory $directory does not exist.");
        }

        $config_files = scandir($directory);

        if ($config_files === false) {
            throw new \Exception("Не вдалося відкрити директорію $directory");
        }

        $Config = [];

        foreach ($config_files as $config_file) {
            if (substr($config_file, -4) === '.php') {
                $path = $directory . '/' . $config_file;
                include($path);
            }
        }

        $this->params = [];
        foreach ($Config as $config) {
            foreach ($config as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    public static function get()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __set($name, $value)
    {
        $this->params[$name] = $value;
    }

    public function __get($name)
    {
        return $this->params[$name];
    }

    // Метод для отримання PDO
    public function getPDO()
    {
        if (!$this->pdo) {

            $dsn = 'mysql:host=localhost;dbname=layttle';
            $username = 'Dubrov';
            $password = '2004Dubrov';
            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            ];

            try {
                $this->pdo = new \PDO($dsn, $username, $password, $options);
            } catch (\PDOException $e) {
                echo 'Connection failed: ' . $e->getMessage();
                return null;  // Повертаємо null, якщо підключення не вдалося
            }
        }
        return $this->pdo;
    }

    public function prepare(string $string)
    {
    }
}
