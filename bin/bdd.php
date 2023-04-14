<?php

use Core\Common\Site;

require __DIR__ . "/../../../autoload.php";

Site::initCli(__DIR__ ."/../../../../");

$siteConfig = json_decode(file_get_contents(ROOT_DIR."config/site_config.json"));
$bddConfigs = json_decode(file_get_contents(ROOT_DIR."config/bdd.json"));
$bddFile = __DIR__."/../cores/framework.sql";
$parameters = [
  "password" => $bddConfigs->{$siteConfig->env}->password,
  "user" => $bddConfigs->{$siteConfig->env}->username,
  "host" => $bddConfigs->{$siteConfig->env}->host,
  "port" => $bddConfigs->{$siteConfig->env}->port,
  "database" => $bddConfigs->{$siteConfig->env}->dbname,
];
$parameters = implode(" ",array_map(fn($k,$v) => "--".$k."=\"".$v."\"",array_keys($parameters),$parameters));
print("Début création tables en BDD".PHP_EOL);
shell_exec("mysql ".$parameters." < ".$bddFile);
print("FIN".PHP_EOL);