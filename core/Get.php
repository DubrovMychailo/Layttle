<?php

namespace core;
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
use vendor\layttle\core\RequestMethod;

class Get
{
    public function get($key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }
}