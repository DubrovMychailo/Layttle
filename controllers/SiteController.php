<?php

namespace controllers;
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

use core\AppConfig;
use core\Controller;

class SiteController extends Controller
{
    public function actionIndex()
    {
        $config = AppConfig::get();
        $conn = new \mysqli($config->dbHost, $config->dbLogin, $config->dbPassword, $config->dbName);
        $this->render('site/index.php', ['conn' => $conn]);
    }
}
