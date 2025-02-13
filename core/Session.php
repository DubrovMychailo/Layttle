<?php


namespace core;


class Session
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            error_log('Session started or already active in Session constructor', 3, 'D:\wamp64\domains\Layttle\error_cms.txt');
        }
    }


    public function set($name, $value)
    {
        if (is_array($value)) {
            $value = serialize($value);
        }
        $_SESSION[$name] = $value;

        error_log("Set session variable: $name = " . print_r($value, true), 3, 'D:\wamp64\domains\Layttle\error_cms.txt');
    }

    public function get($name)
    {
        if (isset($_SESSION[$name])) {
            $value = $_SESSION[$name];

            if ($this->isSerialized($value)) {
                $value = unserialize($value);
            }

            error_log("Getting session variable: $name = " . print_r($value, true), 3, 'D:\wamp64\domains\Layttle\error_cms.txt');
            return $value;
        }

        error_log("Session variable: $name does not exist", 3, 'D:\wamp64\domains\Layttle\error_cms.txt');
        return null;
    }

    public function remove($name)
    {
        if (isset($_SESSION[$name])) {
            unset($_SESSION[$name]);

            error_log("Removed session variable: $name", 3, 'D:\wamp64\domains\Layttle\error_cms.txt');
        }
    }

    public function setValues($assocArray)
    {
        foreach ($assocArray as $key => $value) {
            $this->set($key, $value);
        }
    }

    private function isSerialized($data)
    {
        return is_string($data) && ($data == serialize(false) || @unserialize($data) !== false);
    }
}
