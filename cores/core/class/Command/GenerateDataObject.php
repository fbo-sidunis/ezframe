<?php

namespace Core\Command;

use Core\Db\Schema;
use Core\Exception;
use Core\FileGenerator\DataObject;

class GenerateDataObject extends \Core\CommandHandler
{
  public function execute()
  {
    try {
      DataObject::generateFiles();
    } catch (Exception $e) {
      echo "Erreur lors de la génération des dataobjects : " . $e->getMessage() . PHP_EOL;
    }
    echo "Génération du schéma terminée" . PHP_EOL;
  }
}
