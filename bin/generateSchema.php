<?php

use Core\Common\Site;
use Core\Db\Schema;
use Core\Exception;

require __DIR__ . "/../../../autoload.php";

Site::initCli(__DIR__ . "/../../../../");

try {
  $schema = new Schema;
  $schema->loadTables();
  $schema->generate();
} catch (Exception $e) {
  echo "Erreur lors de la génération du schéma : " . $e->getMessage() . PHP_EOL;
  exit(1);
}

echo "Génération du schéma terminée" . PHP_EOL;
