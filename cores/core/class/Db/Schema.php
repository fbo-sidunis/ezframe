<?php

namespace Core\Db;

use Core\Db\Schema\Colonne;
use Core\Db\Schema\Table;
use Exception;

class Schema
{
  private const MAIN_FOLDER = ROOT_DIR . "config/schema/";
  protected array $_tables = [];

  public function __construct()
  {
    $this->loadTables();
  }

  /**
   * Get the value of tables
   * @return Table[]
   */
  public function getTables(): array
  {
    return $this->_tables;
  }

  /**
   * Get the value of table
   */
  public function getTable(string $name): ?Table
  {
    return $this->_tables[$name] ?? null;
  }

  /**
   * Get the value of table
   */
  public function addTable(string $name, Table $table): self
  {
    $this->_tables[$name] = $table;
    return $this;
  }

  /**
   * Set the value of tables
   */

  public function loadTables(): void
  {
    $filesPaths = [];
    $files = assetsMap(self::MAIN_FOLDER, 1);
    foreach ($files as $file) {
      $filePath = self::MAIN_FOLDER . $file;
      if (pathinfo($filePath, PATHINFO_EXTENSION) !== "json") continue;
      $filesPaths[basename($filePath, ".json")] = $filePath;
    }
    $modulesFolders = assetsMap(ROOT_DIR . "app/", 3);
    foreach ($modulesFolders as $moduleName => $modulesFolder) {
      if (!is_array($modulesFolder)) continue;
      $schemaFolder = $modulesFolder["schema"] ?? null;
      if (!$schemaFolder) continue;
      foreach ($schemaFolder as $file) {
        $filePath = ROOT_DIR . "app/" . $moduleName . "/schema/" . $file;
        if (pathinfo($filePath, PATHINFO_EXTENSION) !== "json") continue;
        $filesPaths[basename($filePath, ".json")] = $filePath;
      }
    }

    //Pour tous les fichiers .json, on crée une table et ses colonnes
    foreach ($filesPaths as $filePath) {
      try {
        $content = $this->getContent($filePath);
      } catch (\TypeError $e) {
        throw new Exception("Erreur lors de la lecture du fichier $filePath : " . $e->getMessage());
      }
      try {
        $content["schema"] = $this;
        $table = Table::createFromArray($content);
        if (basename($filePath, ".json") !== $table->getName()) throw new Exception("Le nom de la table `" . $table->getName() . "` ne correspond pas au nom du fichier `$file`");
        $this->addTable($table->getName(), $table);
      } catch (\Exception $e) {
        throw new Exception("Erreur lors de la création de la table du fichier $filePath : " . $e->getMessage());
      }
    }
  }

  public function generate(): void
  {
    $tables = $this->getTables();
    //We order the tables by dependencies, so that the foreign keys are created after the tables they depend on
    usort($tables, function (Table $a, Table $b) {
      $aDeps = $a->getDependencies();
      if (empty($aDeps)) return -1;
      $bDeps = $b->getDependencies();
      if (empty($bDeps)) return 1;
      $aName = $a->getName();
      $bName = $b->getName();
      $aDeps = array_map(function (Table $table) {
        return $table->getName();
      }, $aDeps);
      $bDeps = array_map(function (Table $table) {
        return $table->getName();
      }, $bDeps);
      if (in_array($aName, $bDeps)) return -1;
      if (in_array($bName, $aDeps)) return 1;
      return 0;
    });
    foreach ($tables as $table) {
      $table->update();
    }
  }

  private function getContent(string $file): array
  {
    $content = file_get_contents($file);
    $content = json_decode($content, true);
    return $content;
  }
}
