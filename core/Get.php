<?php

namespace core;
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
use core\RequestMethod;

class Get
{
    public function get($key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }
}