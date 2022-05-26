<?php
use App\Core\Main;

define('ROOT', dirname(__DIR__));

require_once ROOT.'/vendor/autoload.php';

$app = new Main();

$app->start();