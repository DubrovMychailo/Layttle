<?php

namespace core;

use controllers\ProfileController;
use controllers\ProfileEditController;
use controllers\UsersController;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

class Router
{
    protected $route;
    protected $routes = [];

    public function __construct($route)
    {
        $this->route = trim($route, '/');
    }
    function logError($message): void
    {
        $logFile = 'D:/wamp64/domains/layttle/error_cms.txt';

        if (!is_writable($logFile)) {
            die("❌ Помилка: файл логів не доступний для запису!");
        }

        $date = date('Y-m-d H:i:s');
        $logMessage = "[{$date}] " . $message . PHP_EOL;

        file_put_contents($logFile, $logMessage, FILE_APPEND);
        echo "<pre>LOG: {$logMessage}</pre>";
    }


    public function addRoute($uri, $action)
    {
        $this->routes[$uri] = $action;
    }

    public function run()
    {
        $this->route = $this->getRoute();

        if (preg_match('#^profile/([\w\d_-]+)$#', $this->route, $matches)) {
            return (new ProfileController())->view($matches[1]);
        }

        if ($this->route === 'profile/me') {
            $this->logError("🔹 Вхід у /profile/me");

            if (!isset($_SESSION['user']['id'])) {
                $this->logError("❌ У сесії немає ID користувача!");
                die("❌ У сесії немає ID користувача!");
            }

            $this->logError("✅ ID користувача з сесії: " . $_SESSION['user']['id']);

            $db = new DB('localhost', 'Layttle', 'Dubrov', '2004Dubrov');
            $user = $db->selectOne('users', ['id' => $_SESSION['user']['id']]);

            if (!$user || !isset($user['login'])) {
                $this->logError("❌ Користувач з ID {$_SESSION['user']['id']} не знайдений у БД!");
                die("❌ Користувач не знайдений!");
            }

            $this->logError("✅ Користувач знайдений: " . $user['login']);

            return (new ProfileController())->view($user['login']);
        }

        if ($this->route === 'users/login') {
            return (new UsersController())->actionLogin($_POST ?? []);
        } elseif ($this->route === 'users/register') {
            return (new UsersController())->actionRegister($_POST ?? []);
        }

        if (isset($this->routes[$this->route])) {
            list($controller, $method) = explode('@', $this->routes[$this->route]);
            $controller = 'controllers\\' . ucfirst($controller) . 'Controller';

            if (class_exists($controller)) {
                $controllerObject = new $controller();

                if (method_exists('core\Core', 'get')) {
                    Core::get()->controllerObject = $controllerObject;
                }

                if (method_exists($controllerObject, $method)) {
                    return $controllerObject->$method($_POST ?? []);
                } else {
                    $this->error(404);
                }
            } else {
                $this->error(404);
            }
        } else {
            $parts = explode('/', $this->route);
            $id = $parts[count($parts) - 1] ?? null;
            if (strlen($parts[0]) == 0) {
                $parts[0] = 'site';
                $parts[1] = 'index';
            }
            if (count($parts) == 1) {
                $parts[1] = 'index';
            }

            $core = \core\Core::get($this->route);
            $core->moduleName = $parts[0];
            $core->actionName = $parts[1];

            $controller = 'controllers\\' . ucfirst($parts[0]) . 'Controller';
            $method = 'action' . ucfirst($parts[1]);

            if (class_exists($controller)) {
                $controllerObject = new $controller();

                if (method_exists('core\Core', 'get')) {
                    Core::get()->controllerObject = $controllerObject;
                }

                if (method_exists($controllerObject, $method)) {
                    array_splice($parts, 0, 2);
                    return $controllerObject->$method($id);
                } else {
                    $this->error(404);
                }
            } else {
                $this->error(404);
            }
        }
    }


    private function getRoute(): string
    {
        $url = $_SERVER['REQUEST_URI'];
        $path = parse_url($url, PHP_URL_PATH);
        return trim($path, '/');
    }

    public function error($code)
    {
        http_response_code($code);
        if ($code === 404) {
            echo '404 Not Found';
        }
    }
        public function done()
    {

        }
}

$router = new Router(trim($_SERVER['REQUEST_URI'], '/'));
$router->addRoute('users/login', 'UsersController@actionLogin');
$router->addRoute('users/register', 'UsersController@actionRegister');
$router->addRoute('users/registersuccess', 'UsersController@actionRegistersuccess');
$router->addRoute('users/logout', 'UsersController@actionLogout');
$router->addRoute('cart', 'Cart@view');
$router->addRoute('cart/add', 'Cart@add');
$router->addRoute('cart/remove', 'Cart@remove');
$router->addRoute('profile/edit', 'ProfileEditController@edit');
$router->addRoute('profile/me', 'ProfileController@view');
$router->addRoute('profile/update', 'Profile@actionUpdate');
$router->addRoute('profile', 'ProfileController@view');
$router->addRoute('profile/{username}', 'ProfileController@view');
$router->addRoute('chat', 'ChatController@index');
$router->addRoute('chat/chat/{id}', 'ChatController@show');
$router->addRoute('chat/show/{id}', 'ChatController@show');
$router->addRoute('chat/store/{id}', 'ChatController@store');
$router->addRoute('chat/messages/{id}', 'ChatController@messages');
$router->run();
