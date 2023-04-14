<?php
use Core\Common\Site;

//check if console
PHP_SAPI === 'cli' or die('not allowed');

require_once __DIR__ . "/../vendor/autoload.php";

Site::initCli(__DIR__."/../");