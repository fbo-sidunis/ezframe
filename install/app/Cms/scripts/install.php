<?php
$siteConfig = json_decode(file_get_contents(__DIR__ . '/../../../config/site_config.json'));
$bddConfigs = json_decode(file_get_contents(__DIR__ . '/../../../config/bdd.json'));
$bddFile = __DIR__."/cms.sql";
$parameters = [
  "--password=".$bddConfigs->{$siteConfig->env}->password,
  "--user=".$bddConfigs->{$siteConfig->env}->username,
  "--host=".$bddConfigs->{$siteConfig->env}->host,
  "--port=".$bddConfigs->{$siteConfig->env}->port,
  "--database=".$bddConfigs->{$siteConfig->env}->dbname,
];
print("Début création tables en BDD".PHP_EOL);
shell_exec("mysql ".implode(" ",$parameters)." < ".$bddFile);
print("FIN".PHP_EOL);