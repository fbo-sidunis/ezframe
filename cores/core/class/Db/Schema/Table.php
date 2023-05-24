<?php

namespace Db\Schema;

use Core\Db;

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
  protected string $_engine = "InnoDB";
  protected string $_charset = "utf8mb4";
  protected string $_collation = "utf8mb4_unicode_ci";

  public function __construct(
    string $name,
    array $columns = [],
    ?string $comment = null,
    string $engine = "InnoDB",
    string $charset = "utf8mb4",
    string $collation = "utf8mb4_unicode_ci"
  ) {
    $this->_name = $name;
    $this->_columns = $columns;
    $this->_comment = $comment;
    $this->_engine = $engine;
    $this->_charset = $charset;
    $this->_collation = $collation;
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

  public function isTableInDb()
  {
    $tables = Db::showTables();
    foreach ($tables as $table) {
      if ($table === $this->_name) {
        return true;
      }
    }
  }

  public function getColumnsInDb()
  {
    $columns = Db::showColumns($this->_name);
    return $columns;
  }

  public function getKeysInDb()
  {
    $keys = Db::showKeys($this->_name);
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
    $elementsCreate[] = ") ENGINE=" . $table->getEngine() . " DEFAULT CHARSET=" . $table->getCharset() . " COLLATE=" . $table->getCollation() . " COMMENT='" . $table->getComment() . "';";
    return Db::db_exec(implode("\n", $elementsCreate));
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
    $uniqueKeysInDb = array_filter($keysInDb, fn ($key) => $key["Key_name"] !== "PRIMARY" && $key["Non_unique"] === "0");
    $uniqueKeysInTable = array_filter($columnsInTable, fn ($colonne) => $colonne->isUnique());
    $uniqueKeysNameInDb = array_map(fn ($key) => $key["Column_name"], $uniqueKeysInDb);
    $uniqueKeysNameInTable = array_map(fn ($colonne) => $colonne->getName(), $uniqueKeysInTable);
    $uniqueKeysToDrop = array_filter($uniqueKeysInDb, fn ($key) => !in_array($key["Column_name"], $uniqueKeysNameInTable));
    $uniqueKeysToAdd = array_filter($uniqueKeysInTable, fn ($colonne) => !in_array($colonne->getName(), $uniqueKeysNameInDb));
    $indexKeysInDb = array_filter($keysInDb, fn ($key) => $key["Key_name"] !== "PRIMARY" && $key["Non_unique"] === "1");
    $indexKeysInTable = array_filter($columnsInTable, fn ($colonne) => $colonne->isIndex());
    $indexKeysNameInDb = array_map(fn ($key) => $key["Column_name"], $indexKeysInDb);
    $indexKeysNameInTable = array_map(fn ($colonne) => $colonne->getName(), $indexKeysInTable);
    $indexKeysToDrop = array_filter($indexKeysInDb, fn ($key) => !in_array($key["Column_name"], $indexKeysNameInTable));
    $indexKeysToAdd = array_filter($indexKeysInTable, fn ($colonne) => !in_array($colonne->getName(), $indexKeysNameInDb));

    $elementsAlter = ["ALTER TABLE " . $this->getQuotedName()];
    $elements = [];
    foreach ($primaryKeysToDrop as $key => $column) {
      $elements[] = "  DROP PRIMARY KEY";
    }
    foreach ($uniqueKeysToDrop as $key => $column) {
      $elements[] = "  DROP INDEX " . $column["Key_name"];
    }
    foreach ($indexKeysToDrop as $key => $column) {
      $elements[] = "  DROP INDEX " . $column["Key_name"];
    }
    foreach ($columnsToDrop as $key => $column) {
      $elements[] = "  DROP COLUMN " . $column["Field"];
    }
    foreach ($columnsToAdd as $key => $column) {
      $elements[] = "  ADD " . $column->getColumnLine();
    }
    foreach ($columnsToModify as $key => $column) {
      $elements[] = "  MODIFY " . $column->getColumnLine();
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

    $elementsAlter[] = implode(",\n", $elements) . ";";
    return Db::db_exec(implode("\n", $elementsAlter));
  }

  public function update()
  {
    if ($this->isTableInDb()) {
      return $this->alter();
    } else {
      return $this->create();
    }
  }

  public static function createFromArray(array $table)
  {
    $columns = array_map(fn ($colonne) => Colonne::createFromArray($colonne), $table["columns"] ?? []);
    return new Table(
      $table["name"] ?? null,
      $columns,
      $table["comment"] ?? null,
      $table["engine"] ?? null,
      $table["charset"] ?? null,
      $table["collation"] ?? null,
    );
  }
}