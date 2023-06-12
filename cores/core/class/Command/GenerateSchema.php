<?php

namespace Core\Command;

use Core\Db\Schema;
use Core\Exception;

class GenerateSchema extends \Core\CommandHandler
{

  public function execute()
  {
    try {
      $schema = new Schema;
      $schema->loadTables();
      $schema->generate();
    } catch (Exception $e) {
      echo "Erreur lors de la génération du schéma : " . $e->getMessage() . PHP_EOL;
    }
    echo "Génération du schéma terminée" . PHP_EOL;
  }
}
