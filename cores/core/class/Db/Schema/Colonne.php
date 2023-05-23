<?php

namespace Db\Schema;

/**
 * Class Colonne, symbolise une colonne d'une table
 * Devra pouvoir mettre à jour la colonne en base
 * Pour le MySQL / MariaDB seulement
 * @package Db\Schema
 */
class Colonne
{
  private const NUMERIC_TYPES = [
    "TINYINT",
    "SMALLINT",
    "MEDIUMINT",
    "INT",
    "BIGINT",
    "FLOAT",
    "DOUBLE",
    "DECIMAL",
  ];
  private const STRING_TYPES = [
    "CHAR",
    "VARCHAR",
    "TINYTEXT",
    "TEXT",
    "MEDIUMTEXT",
    "LONGTEXT",
    "BINARY",
    "VARBINARY",
    "TINYBLOB",
    "MEDIUMBLOB",
    "BLOB",
    "LONGBLOB",
    "ENUM",
    "SET",
    "JSON",
  ];
  private const DATE_TYPES = [
    "DATE",
    "DATETIME",
    "TIMESTAMP",
    "TIME",
    "YEAR",
  ];
  private const BOOLEAN_TYPES = [
    "BOOLEAN",
  ];
  protected string $_name;
  protected string $_type;
  protected ?int $_length = null;
  protected mixed $_default = null;
  protected ?string $_comment = null;
  protected bool $_null = false;
  protected bool $_autoIncrement = false;
  protected bool $_primary = false;
  protected bool $_unique = false;
  protected bool $_index = false;
  protected bool $_unsigned = false;
  protected ?string $_referenceColumn = null;
  protected ?string $_referenceTable = null;
  protected bool $_cascadeOnDelete = false;
  protected bool $_cascadeOnUpdate = false;

  public function __construct(
    string $name,
    string $type,
    ?int $length = null,
    mixed $default = null,
    ?string $comment = null,
    bool $null = false,
    bool $autoIncrement = false,
    bool $primary = false,
    bool $unique = false,
    bool $index = false,
    bool $unsigned = false,
    ?string $referenceColumn = null,
    ?string $referenceTable = null,
    bool $cascadeOnDelete = false,
    bool $cascadeOnUpdate = false
  ) {
    if (empty($name)) {
      throw new \Exception("Nom de colonne `$name` invalide");
    }
    $this->_name = $name;
    if (!in_array($type, array_merge(self::NUMERIC_TYPES, self::STRING_TYPES, self::DATE_TYPES, self::BOOLEAN_TYPES))) {
      throw new \Exception("Type de colonne \"$type\" invalide");
    }
    $this->_type = $type;
    $this->_length = $length;
    $this->_default = $default;
    $this->_comment = $comment;
    $this->_null = $null;
    $this->_autoIncrement = $autoIncrement;
    $this->_primary = $primary;
    $this->_unique = $unique;
    $this->_index = $index;
    $this->_unsigned = $unsigned;
    $this->_referenceColumn = $referenceColumn;
    $this->_referenceTable = $referenceTable;
    $this->_cascadeOnDelete = $cascadeOnDelete;
    $this->_cascadeOnUpdate = $cascadeOnUpdate;
  }

  /** Getters and Setters */

  public function getName(): string
  {
    return $this->_name;
  }

  public function getQuotedName(): string
  {
    return "`{$this->_name}`";
  }

  public function getType(): string
  {
    return $this->_type;
  }

  public function getLength(): ?int
  {
    return $this->_length;
  }

  public function getDefault(): mixed
  {
    return $this->_default;
  }

  public function getQuotedDefault(): string
  {
    if ($this->getDefault() === null) {
      return 'NULL';
    }
    if (in_array($this->getType(), self::NUMERIC_TYPES)) {
      return $this->getDefault();
    }
    if (in_array($this->getType(), self::STRING_TYPES)) {
      return "'{$this->getDefault()}'";
    }
    if (in_array($this->getType(), self::DATE_TYPES)) {
      if ($this->getDefault() === 'CURRENT_TIMESTAMP') {
        return $this->getDefault();
      }
      return "'{$this->getDefault()}'";
    }
    if (in_array($this->getType(), self::BOOLEAN_TYPES)) {
      return $this->getDefault() ? 'TRUE' : 'FALSE';
    }
    return "'{$this->getDefault()}'";
  }

  public function getComment(): ?string
  {
    return $this->_comment;
  }

  public function isNull(): bool
  {
    return $this->_null;
  }

  public function isAutoIncrement(): bool
  {
    return $this->_autoIncrement;
  }

  public function isPrimary(): bool
  {
    return $this->_primary;
  }

  public function isUnique(): bool
  {
    return $this->_unique;
  }

  public function isIndex(): bool
  {
    return $this->_index;
  }

  public function isUnsigned(): bool
  {
    return $this->_unsigned;
  }

  public function getReferenceColumn(): ?string
  {
    return $this->_referenceColumn;
  }

  public function getQuotedReferenceColumn(): ?string
  {
    return "`{$this->_referenceColumn}`";
  }

  public function getReferenceTable(): ?string
  {
    return $this->_referenceTable;
  }

  public function isCascadeOnDelete(): bool
  {
    return $this->_cascadeOnDelete;
  }

  public function isCascadeOnUpdate(): bool
  {
    return $this->_cascadeOnUpdate;
  }

  /** -------------------- */


  public function getColumnLine(): string
  {
    $elements = [];
    $elements[] = $this->getQuotedName();
    $elements[] = $this->getType() . ($this->getLength() !== null ? "({$this->getLength()})" : '');
    if ($this->isUnsigned()) {
      $elements[] = 'UNSIGNED';
    }
    if ($this->isAutoIncrement()) {
      $elements[] = 'AUTO_INCREMENT';
    } else {
      $elements[] = "DEFAULT {$this->getQuotedDefault()}";
    }
    if ($this->isNull()) {
      $elements[] = 'NULL';
    } else {
      $elements[] = 'NOT NULL';
    }
    if ($this->getComment() !== null) {
      $elements[] = "COMMENT '{$this->getComment()}'";
    }

    return implode(' ', $elements);
  }

  public function getForeignKeyLine(): string
  {
    if (!$this->getReferenceColumn() || !$this->getReferenceTable()) {
      return null;
    }
    $elements = [];
    $elements[] = "FOREIGN KEY ({$this->getQuotedName()})";
    $elements[] = "REFERENCES {$this->getReferenceTable()}({$this->getQuotedReferenceColumn()})";
    if ($this->isCascadeOnDelete()) {
      $elements[] = 'ON DELETE CASCADE';
    }
    if ($this->isCascadeOnUpdate()) {
      $elements[] = 'ON UPDATE CASCADE';
    }
    return implode(' ', $elements);
  }

  public function getIndexLine(): string
  {
    if (!$this->isIndex()) {
      return null;
    }
    return "INDEX ({$this->getQuotedName()})";
  }

  public function getUniqueLine(): string
  {
    if (!$this->isUnique()) {
      return null;
    }
    return "UNIQUE ({$this->getQuotedName()})";
  }

  public function getPrimaryLine(): string
  {
    if (!$this->isPrimary()) {
      return null;
    }
    return "PRIMARY KEY ({$this->getQuotedName()})";
  }

  public static function createFromArray($parameters = [])
  {
    return new Colonne(
      /*name*/
      $column["name"] ?? null,
      /*type*/
      $column["type"] ?? null,
      /*length*/
      $column["length"] ?? null,
      /*default*/
      $column["default"] ?? null,
      /*comment*/
      $column["comment"] ?? null,
      /*null*/
      $column["null"] ?? null,
      /*autoIncrement*/
      $column["autoIncrement"] ?? null,
      /*primary*/
      $column["primary"] ?? null,
      /*unique*/
      $column["unique"] ?? null,
      /*index*/
      $column["index"] ?? null,
      /*unsigned*/
      $column["unsigned"] ?? null,
      /*referenceColumn*/
      $column["referenceColumn"] ?? null,
      /*referenceTable*/
      $column["referenceTable"] ?? null,
      /*cascadeOnDelete*/
      $column["cascadeOnDelete"] ?? null,
      /*cascadeOnUpdate*/
      $column["cascadeOnUpdate"] ?? null,
    );
  }
}
