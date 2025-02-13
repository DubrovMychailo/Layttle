<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'D:\wamp64\domains\Layttle\core\Session.php';
use core\Session;
$session = new Session();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
spl_autoload_register(static function ($className) {
    $path = str_replace('\\', '/', $className . '.php');
    $path = __DIR__ . '/' . $path;
    if (file_exists($path)) {
        include_once($path);
    } else {
    }
});

require 'core/Config.php';
require 'core/DB.php';

$config = \core\Config::get();
$dbHost = $config->dbHost;
$dbName = $config->dbName;
$dbLogin = $config->dbLogin;
$dbPassword = $config->dbPassword;

try {
    $db = new \core\DB($dbHost, $dbName, $dbLogin, $dbPassword);
} catch (PDOException $e) {
}

$route = trim($_GET['route'] ?? '', '/');

$core = \core\Core::get($route);
$core->run($route);
$core->done();
