<?php

namespace Core\Db;

use Db\Schema\Colonne;
use Db\Schema\Table;
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
    $files = assetsMap(self::MAIN_FOLDER, 1);
    //Pour tous les fichiers .json, on crée une table et ses colonnes
    foreach ($files as $file) {
      if (pathinfo($file, PATHINFO_EXTENSION) !== "json") continue;
      $content = $this->getContent(self::MAIN_FOLDER . $file);
      try {
        $table = Table::createFromArray($content);
        if (basename($file, ".json") !== $table->getName()) throw new Exception("Le nom de la table `" . $table->getName() . "` ne correspond pas au nom du fichier `$file`");
        $this->addTable($table->getName(), $table);
      } catch (\Exception $e) {
        throw new Exception("Erreur lors de la création de la table du fichier $file : " . $e->getMessage());
      }
    }
  }

  public function generate(): void
  {
    foreach ($this->getTables() as $table) {
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
