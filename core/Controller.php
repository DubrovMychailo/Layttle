<?php

namespace core;
require_once 'D:\wamp64\domains\Layttle\core\Session.php';
require_once __DIR__ . '/Template.php';

use JetBrains\PhpStorm\NoReturn;
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
class Controller {
    protected ?Template $template = null;
    protected array $errorMessages = [];
    public bool $isPost = false;
    public bool $isGet = false;
    public Post $post;
    public Get $get;

    public function __construct()
    {
        error_log("Module: " . Core::get()->moduleName);
        error_log("Action: " . Core::get()->actionName);
        $session = new Session();
        $module = Core::get()->moduleName ?: 'users';
        $action = Core::get()->actionName ?: 'login';
        $path = __DIR__ . "/../views/{$module}/{$action}.php";

        if (file_exists($path)) {
            $this->template = new Template($path);
        } else {

            error_log("Template file does not exist: " . $path);
        }

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST':
                $this->isPost = true;
                break;
            case 'GET':
                $this->isGet = true;
                break;
        }

        $this->post = new Post();
        $this->get = new Get();
    }

    /**
     * @throws \Exception
     */
    public function render($pathToView = null, $data = []) : array
    {
        if ($this->template !== null) {
            if (!empty($pathToView)) {
                $fullPath = __DIR__ . '/../views/' . $pathToView;
                $this->template->setTemplateFilePath($fullPath);
            }

            $this->template->post = $this->post;

            foreach ($data as $key => $value) {
                $this->template->setParam($key, $value);
            }

            return [
                'Content' => $this->template->getHTML()
            ];
        } else {
            throw new \Exception("Template object is not initialized.");
        }
    }

    #[NoReturn] public function redirect($path) : void
    {
        header("Location:{$path}");
        die;
    }

    public function addErrorMessage($message = null) : void
    {
        $this->errorMessages[] = $message;
        $this->template->setParam('error_message', implode('<br />', $this->errorMessages));
    }

    public function clearErrorMessage() : void
    {
        $this->errorMessages = [];
        $this->template->setParam('error_message', null);
    }

    public function isErrorMessageExists(): bool
    {
        return count($this->errorMessages) > 0;
    }
}
