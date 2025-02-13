<?php

namespace core;
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/AppConfig.php';
require_once __DIR__ . '/Template.php';
require_once __DIR__ . '/Session.php';
require_once __DIR__ . '/DB.php';



class Core
{
    public string $defaultLayoutPath = 'views/layouts/index.php';
    public $moduleName;
    public $actionName;
    public $router;
    public $template;
    public $db;
    public Controller $controllerObject;
    public $session;
    private static $instance;

    private function __construct($route)
    {
        $this->template = new Template(__DIR__ . '/../views/layouts/index.php');
        $host = AppConfig::get()->dbHost;
        $name = AppConfig::get()->dbName;
        $login = AppConfig::get()->dbLogin;
        $password = AppConfig::get()->dbPassword;
        $this->db = new DB($host, $name, $login, $password);
        $this->session = new Session();

    }


    public function run($route)
    {
        $this->router = new Router($route);
        $params = $this->router->run();
        if (!empty($params)) {
            $this->template->setParams($params);
        }
    }

    public function done()
    {
        $this->template->display();
        $this->router->done();
    }

    public static function get($route = null)
    {

        if (empty(self::$instance)) {
            if ($route === null) {
                throw new \InvalidArgumentException("Route parameter is required for Core::get()");
            }
            self::$instance = new Core($route);
        }
        return self::$instance;
    }
}
