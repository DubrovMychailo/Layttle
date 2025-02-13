<?php

namespace core;

class AppConfig
{
    public static function get()
    {
        return (object)[
            'dbHost' => 'localhost',
            'dbName' => 'layttle',
            'dbLogin' => 'Dubrov',
            'dbPassword' => '2004Dubrov',
        ];
    }
}

$config = AppConfig::get();

$conn = new \mysqli($config->dbHost, $config->dbLogin, $config->dbPassword, $config->dbName);

