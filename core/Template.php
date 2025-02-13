<?php
namespace core;
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
class Template
{
    protected $templateFilePath;
    protected $params = [];
    public $Title;
    public $post;

    public function __construct($templateFilePath)
    {
        $this->setTemplateFilePath($templateFilePath);
    }

    public function setTemplateFilePath($templateFilePath)
    {
        $this->templateFilePath = __DIR__ . '/../views/' . $templateFilePath . '.php';
        if (!file_exists($templateFilePath)) {
            throw new \Exception("Template file not found: " . $templateFilePath);
        }

        $this->templateFilePath = $templateFilePath;
    }

    public function setParams(array $params)
    {
        $this->params = $params;
    }

    public function setParam($key, $value)
    {
        $this->params[$key] = $value;
    }

    public function getHTML()
    {
        extract($this->params);
        ob_start();
        include $this->templateFilePath;
        return ob_get_clean();
    }

    public function display()
    {
        echo $this->getHTML();
    }

}
