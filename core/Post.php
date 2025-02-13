<?php

namespace core;
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
class Post
{
    public function get($key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }
}