<?php

namespace Core\Db\Schema;

use Core\Db;
use Core\Db\Schema;
use Core\Exception;

/**
 * Class table, symbolise une  table
 * Devra pouvoir mettre à jour la table en base
 * Pour le MySQL / MariaDB seulement
 * @package Db\Schema
 */

class Table
{
  protected string $_name;
  protected array $_columns = [];
  protected ?string $_comment = null;
  protected ?string $_engine = "InnoDB";
  protected ?string $_charset = "utf8mb4";
  protected ?string $_collation = "utf8mb4_unicode_ci";
  protected $_renameFrom = null;
  protected ?Schema $_schema = null;

  public function __construct(
    string $name,
    array $columns = [],
    ?string $comment = null,
    ?string $engine = "InnoDB",
    ?string $charset = "utf8mb4",
    ?string $collation = "utf8mb4_unicode_ci",
    $renameFrom = null,
    ?Schema $schema = null
  ) {
    $this->_name = $name;
    $this->_columns = $columns;
    $this->_comment = $comment;
    $this->_engine = $engine ?? "InnoDB";
    $this->_charset = $charset ?? "utf8mb4";
    $this->_collation = $collation ?? $this->_charset . "_unicode_ci";
    $this->_renameFrom = $renameFrom;
    $this->_schema = $schema;
  }

  /**
   * Getters
   */

  public function getName(): string
  {
    return $this->_name;
  }

  public function getQuotedName(): string
  {
    return "`" . $this->_name . "`";
  }

  /**
   * Get the value of _columns
   * @return Colonne[] 
   */
  public function getColumns(): array
  {
    return $this->_columns;
  }

  public function getComment(): ?string
  {
    return $this->_comment;
  }

  public function getQuotedComment(): ?string
  {
    if ($this->_comment === null) {
      return null;
    }
    $comment = str_replace("'", "\\'", $this->_comment);
    return "'{$comment}'";
  }

  public function getEngine(): string
  {
    return $this->_engine;
  }

  public function getCharset(): string
  {
    return $this->_charset;
  }

  public function getCollation(): string
  {
    return $this->_collation;
  }

  public function getRenameFrom()
  {
    return $this->_renameFrom;
  }

  public function getSchema(): ?Schema
  {
    return $this->_schema;
  }

  public function getQuotedRenameFrom(): ?string
  {
    if ($this->getRenameFrom() === null) {
      return null;
    }
    if (!is_string($this->getRenameFrom())) {
      throw new Exception("Le nom de la colonne renommée doit être une chaîne de caractères", [
        "table" => $this->getName(),
        "renameFrom" => $this->getRenameFrom(),
      ]);
    }
    return "`{$this->getRenameFrom()}`";
  }

  public function isRenamedFrom($name): bool
  {
    $renameFrom = $this->getRenameFrom();
    if ($renameFrom === null) {
      return false;
    }
    if (is_array($renameFrom)) {
      return in_array($name, $renameFrom);
    }
    return $renameFrom == $name;
  }

  /**
   * Setters
   */

  public function setName(string $name): self
  {
    $this->_name = $name;
    return $this;
  }


  public function setComment(?string $comment): self
  {
    $this->_comment = $comment;
    return $this;
  }

  public function setEngine(string $engine): self
  {
    $this->_engine = $engine;
    return $this;
  }

  public function setCharset(string $charset): self
  {
    $this->_charset = $charset;
    return $this;
  }

  public function setCollation(string $collation): self
  {
    $this->_collation = $collation;
    return $this;
  }

  public function setRenameFrom($renameFrom): self
  {
    $this->_renameFrom = $renameFrom;
    return $this;
  }

  /**
   * Ajoute une colonne à la table
   * @param Colonne $colonne
   * @return self
   */
  public function addColumn(Colonne $colonne): self
  {
    $this->_columns[] = $colonne;
    return $this;
  }

  /**
   * Supprime une colonne de la table
   * @param Colonne $colonne
   * @return self
   */

  public function removeColumnByName(string $name): self
  {
    foreach ($this->_columns as $key => $colonne) {
      if ($colonne->getName() === $name) {
        unset($this->_columns[$key]);
      }
    }
    return $this;
  }

  public function isTableInDb($tableName = null)
  {
    $tables = Db::showTables();
    return in_array($tableName ?? $this->_name, $tables);
  }

  public function isRenameFromInDb()
  {
    if ($this->getRenameFrom() === null) {
      return false;
    }
    if (is_array($this->getRenameFrom())) {
      foreach ($this->getRenameFrom() as $renameFrom) {
        if ($this->isTableInDb($renameFrom)) {
          $this->setRenameFrom($renameFrom);
          return true;
        }
      }
      return false;
    }
    return $this->isTableInDb($this->getRenameFrom());
  }

  public function getColumnsInDb()
  {
    $columns = Db::showColumns($this->_name);
    return $columns;
  }

  /**
   * Retourne les tables référencées par la table
   * @return Table[] 
   */
  public function getDependencies(): array
  {
    $dependencies = [];
    foreach ($this->getColumns() as $key => $colonne) {
      if ($colonne->getReferenceTable() && $colonne->getReferenceTable() !== $colonne->getName()) {
        $dependencies[] = $this->getSchema()->getTable($colonne->getReferenceTable());
      }
    }
    return $dependencies;
  }

  /**
   * Retourne les tables dans lesquelles cette table est référencée
   * @return Table[] 
   */
  public function getDependingTables(): array
  {
    $dependingTables = [];
    foreach ($this->getSchema()->getTables() as $key => $table) {
      foreach ($table->getColumns() as $key2 => $colonne) {
        if ($colonne->getReferenceTable() === $this->getName()) {
          $dependingTables[] = $table;
        }
      }
    }
    return $dependingTables;
  }

  /**
   * Retourne une colonne de la table
   * @param mixed $name 
   * @return Colonne|null 
   */
  public function getColumnByName($name)
  {
    foreach ($this->_columns as $key => $colonne) {
      if ($colonne->getName() === $name) {
        return $colonne;
      }
    }
    return null;
  }

  public function getKeysInDb()
  {
    $keys = Db::showKeys($this->_name);
    return $keys;
  }

  public function getForeignKeysInDb()
  {
    $keys = Db::getForeignKeys($this->_name);
    return $keys;
  }

  public static function drop($name)
  {
    return Db::dropTable($name);
  }

  public static function createTable(Table $table)
  {
    $elementsCreate = ["CREATE TABLE IF NOT EXISTS " . $table->getQuotedName() . " ("];
    $elements = [];
    foreach ($table->getColumns() as $key => $colonne) {
      $elements[] = "  " . $colonne->getColumnLine();
    }
    foreach (array_filter(array_map(fn ($colonne) => $colonne->getPrimaryLine(), $table->getColumns())) as $colonne) {
      $elements[] = "  " . $colonne;
    }
    foreach (array_filter(array_map(fn ($colonne) => $colonne->getUniqueLine(), $table->getColumns())) as $colonne) {
      $elements[] = "  " . $colonne;
    }
    foreach (array_filter(array_map(fn ($colonne) => $colonne->getIndexLine(), $table->getColumns())) as $colonne) {
      $elements[] = "  " . $colonne;
    }
    foreach (array_filter(array_map(fn ($colonne) => $colonne->getForeignKeyLine(), $table->getColumns())) as $colonne) {
      $elements[] = "  " . $colonne;
    }
    $elementsCreate[] = implode(",\n", $elements);
    $elementsCreate[] = ")";
    $elementsCreate[] = "ENGINE=" . $table->getEngine();
    $elementsCreate[] = "DEFAULT CHARSET=" . $table->getCharset();
    $elementsCreate[] = "COLLATE=" . $table->getCollation();
    if ($table->getComment() !== null) $elementsCreate[] = "COMMENT={$table->getQuotedComment()}";
    return Db::db_exec(implode("\n", $elementsCreate) . ";");
  }

  public function create()
  {
    return self::createTable($this);
  }

  public function alter()
  {
    $columnsInDb = $this->getColumnsInDb();
    $columnsInTable = $this->getColumns();
    $keysInDb = $this->getKeysInDb();
    $foreignKeysInDb = $this->getForeignKeysInDb();

    $columnsNameInDb = array_map(fn ($colonne) => $colonne["Field"], $columnsInDb);
    $columnsNameInTable = array_map(fn ($colonne) => $colonne->getName(), $columnsInTable);
    $columnsToDrop = array_filter($columnsInDb, fn ($colonne) => !in_array($colonne["Field"], $columnsNameInTable));
    $columnsToAdd = array_filter($columnsInTable, fn ($colonne) => !in_array($colonne->getName(), $columnsNameInDb));
    $columnsToModify = array_filter($columnsInTable, fn ($colonne) => in_array($colonne->getName(), $columnsNameInDb));
    $primaryKeysInDb = array_filter($keysInDb, fn ($key) => $key["Key_name"] === "PRIMARY");
    $primaryKeysInTable = array_filter($columnsInTable, fn ($colonne) => $colonne->isPrimary());
    $primaryKeysNameInDb = array_map(fn ($key) => $key["Column_name"], $primaryKeysInDb);
    $primaryKeysNameInTable = array_map(fn ($colonne) => $colonne->getName(), $primaryKeysInTable);
    $primaryKeysToDrop = array_filter($primaryKeysInDb, fn ($key) => !in_array($key["Column_name"], $primaryKeysNameInTable));
    $primaryKeysToAdd = array_filter($primaryKeysInTable, fn ($colonne) => !in_array($colonne->getName(), $primaryKeysNameInDb));
    $uniqueKeysInDb = array_filter($keysInDb, fn ($key) => $key["Key_name"] !== "PRIMARY" && $key["Non_unique"] == 0);
    $uniqueKeysInTable = array_filter($columnsInTable, fn ($colonne) => $colonne->isUnique());
    $uniqueKeysNameInDb = array_map(fn ($key) => $key["Column_name"], $uniqueKeysInDb);
    $uniqueKeysNameInTable = array_map(fn ($colonne) => $colonne->getName(), $uniqueKeysInTable);
    $uniqueKeysToDrop = array_filter($uniqueKeysInDb, fn ($key) => !in_array($key["Column_name"], $uniqueKeysNameInTable));
    $uniqueKeysToAdd = array_filter($uniqueKeysInTable, fn ($colonne) => !in_array($colonne->getName(), $uniqueKeysNameInDb));
    $indexKeysInDb = array_filter($keysInDb, fn ($key) => $key["Key_name"] !== "PRIMARY" && $key["Non_unique"] == 1);
    $indexKeysInTable = array_filter($columnsInTable, fn ($colonne) => $colonne->isIndex());
    $indexKeysNameInDb = array_map(fn ($key) => $key["Column_name"], $indexKeysInDb);
    $indexKeysNameInTable = array_map(fn ($colonne) => $colonne->getName(), $indexKeysInTable);
    $indexKeysToDrop = array_filter($indexKeysInDb, fn ($key) => !in_array($key["Column_name"], $indexKeysNameInTable));
    $indexKeysToAdd = array_filter($indexKeysInTable, fn ($colonne) => !in_array($colonne->getName(), $indexKeysNameInDb));

    $foreignKeysInTable = array_filter($columnsInTable, fn ($colonne) => $colonne->getReferenceTable() !== null);
    //On drop les foreign keys si la paire clé étrangère / clé référencée n'est pas dans nos colonnes dans la table
    $foreignKeysToDrop = array_filter($foreignKeysInDb, function ($key) use ($foreignKeysInTable) {
      foreach ($foreignKeysInTable as $key2 => $colonne) {
        if ($colonne->getName() === $key["COLUMN_NAME"] && $colonne->getReferenceColumn() === $key["REFERENCED_TABLE_NAME"]) {
          return false;
        }
      }
      return true;
    });
    //On ajoute les foreign keys si la paire clé étrangère / clé référencée n'est pas dans nos colonnes dans la table
    $foreignKeysToAdd = array_filter($foreignKeysInTable, function ($colonne) use ($foreignKeysInDb) {
      foreach ($foreignKeysInDb as $key => $key2) {
        if ($colonne->getName() === $key2["COLUMN_NAME"] && $colonne->getReferenceColumn() === $key2["REFERENCED_TABLE_NAME"]) {
          return false;
        }
      }
      return true;
    });

    $columnToRename = [];
    foreach ($columnsToAdd as $key => $column) {
      foreach ($columnsToDrop as $key2 => $column2) {
        if ($column->isRenamedFrom($column2)) {
          $column = $column->setRenameFrom($column2);
          $columnToRename[] = $column;
          unset($columnsToAdd[$key]);
          unset($columnsToDrop[$key2]);
        }
      }
    }

    $elementsAlter = ["ALTER TABLE " . $this->getQuotedName()];
    $elements = [];
    foreach ($primaryKeysToDrop as $key => $column) {
      $elements[] = "  DROP PRIMARY KEY";
    }
    foreach ($foreignKeysToDrop as $key => $column) {
      $elements[] = "  DROP FOREIGN KEY " . $column["CONSTRAINT_NAME"];
    }
    foreach ($foreignKeysToAdd as $key => $column) {
      $elements[] = "  ADD " . $column->getForeignKeyLine();
    }
    foreach ($uniqueKeysToDrop as $key => $column) {
      if ($this->getColumnByName($column["Column_name"])->getReferenceColumn() !== null) {
        $elements[] = "  ADD " . $this->getColumnByName($column["Column_name"])->getIndexLine(true);
      }
      $elements[] = "  DROP INDEX " . $column["Key_name"];
    }
    foreach ($indexKeysToDrop as $key => $column) {
      if ($this->getColumnByName($column["Column_name"])->getReferenceColumn() !== null) continue;
      $elements[] = "  DROP INDEX " . $column["Key_name"];
    }
    foreach ($columnsToDrop as $key => $column) {
      $elements[] = "  DROP COLUMN " . $column["Field"];
    }
    foreach ($columnsToAdd as $key => $column) {
      $elements[] = "  ADD " . $column->getColumnLine() . " " . $column->getAfterLine();
    }
    foreach ($columnsToModify as $key => $column) {
      $elements[] = "  MODIFY " . $column->getColumnLine() . " " . $column->getAfterLine();
    }

    foreach ($columnToRename as $key => $column) {
      $elements[] = "  CHANGE COLUMN " . $column->getQuotedRenameFrom() . " " . $column->getColumnLine() . " " . $column->getAfterLine();
    }
    foreach ($primaryKeysToAdd as $key => $column) {
      $elements[] = "  ADD " . $column->getPrimaryLine();
    }
    foreach ($uniqueKeysToAdd as $key => $column) {
      $elements[] = "  ADD " . $column->getUniqueLine();
    }
    foreach ($indexKeysToAdd as $key => $column) {
      $elements[] = "  ADD " . $column->getIndexLine();
    }
    $elements[] = "COMMENT={$this->getQuotedComment()}";
    $elements[] = "ENGINE=" . $this->getEngine();
    $elements[] = "DEFAULT CHARSET=" . $this->getCharset();
    $elements[] = "COLLATE=" . $this->getCollation();

    $elementsAlter[] = implode(",\n", $elements) . ";";
    return Db::db_exec(implode("\n", $elementsAlter));
  }

  public function getPrimaryKeys()
  {
    $primaryKeys = [];
    foreach ($this->getColumns() as $key => $colonne) {
      if ($colonne->isPrimary()) {
        $primaryKeys[] = $colonne;
      }
    }
    return $primaryKeys;
  }

  public function update()
  {
    if ($this->isRenameFromInDb()) {
      $this->rename();
    }
    if ($this->isTableInDb()) {
      return $this->alter();
    } else {
      return $this->create();
    }
  }

  public static function createFromArray(array $table)
  {
    $columns = [];
    $previousColumn = null;
    foreach ($table["columns"] ?? [] as $key => $columnData) {
      $column = Colonne::createFromArray($columnData);
      if ($previousColumn !== null) {
        $column->setPreviousColumn($previousColumn);
      }
      $columns[] = $column;
      $previousColumn = $column;
    }
    $table = new Table(
      $table["name"] ?? null,
      $columns,
      $table["comment"] ?? null,
      $table["engine"] ?? null,
      $table["charset"] ?? null,
      $table["collation"] ?? null,
      $table["renameFrom"] ?? null,
      $table["schema"] ?? null
    );
    foreach ($table->getColumns() as $column) {
      $column->setTable($table);
    }
    return $table;
  }

  protected function rename()
  {
    $sql = "RENAME TABLE {$this->getQuotedRenameFrom()} TO {$this->getQuotedName()};";
    return Db::db_exec($sql);
  }
}
