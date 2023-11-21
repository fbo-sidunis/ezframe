<?php

namespace Core\Command;

use Core\Db\Schema;
use Core\Exception;
use Core\FileGenerator\Model;

class GenerateModel extends \Core\CommandHandler
{
  public function execute()
  {
    try {
      Model::generateFiles();
    } catch (Exception $e) {
      echo "Erreur lors de la génération du schéma : " . $e->getMessage() . PHP_EOL;
    }
    echo "Génération du schéma terminée" . PHP_EOL;
  }
}
