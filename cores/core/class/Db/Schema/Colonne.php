<?php

namespace Core\Db\Schema;

use Core\Exception;

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
  protected $_length = null;
  protected $_default = null;
  protected $_onUpdate = null;
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
  protected ?Colonne $_previousColumn = null;
  protected ?array $_enumValues = null;
  /**
   * Colonne à renommer
   * @var string|string[]|null
   */
  protected $_renameFrom = null;
  protected ?Table $_table = null;

  public function __construct(
    string $name,
    string $type,
    $length = null,
    $default = null,
    $onUpdate = null,
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
    bool $cascadeOnUpdate = false,
    ?array $enumValues = null,
    $renameFrom = null,
    ?Table $table = null
  ) {
    if (empty($name)) {
      throw new Exception("Nom de colonne `$name` invalide");
    }
    $this->_name = $name;
    if (!in_array($type, array_merge(self::NUMERIC_TYPES, self::STRING_TYPES, self::DATE_TYPES, self::BOOLEAN_TYPES))) {
      throw new Exception("Type de colonne \"$type\" invalide");
    }
    if ($type === 'ENUM' && empty($enumValues)) {
      throw new Exception("Type de colonne \"$type\" invalide, enumValues ne peut pas être vide");
    }
    $this->_type = $type;
    $this->_length = $length;
    $this->_default = $default;
    $this->_onUpdate = $onUpdate;
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
    $this->_enumValues = $enumValues;
    $this->_renameFrom = $renameFrom;
    $this->_table = $table;
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

  public function getLength()
  {
    return $this->_length;
  }

  public function getDefault()
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

  public function getOnUpdate()
  {
    return $this->_onUpdate;
  }

  public function getQuotedOnUpdate()
  {
    if ($this->getOnUpdate() === null) {
      return 'NULL';
    }
    if (in_array($this->getType(), self::NUMERIC_TYPES)) {
      return $this->getOnUpdate();
    }
    if (in_array($this->getType(), self::STRING_TYPES)) {
      return "'{$this->getOnUpdate()}'";
    }
    if (in_array($this->getType(), self::DATE_TYPES)) {
      if ($this->getOnUpdate() === 'CURRENT_TIMESTAMP') {
        return $this->getOnUpdate();
      }
      return "'{$this->getOnUpdate()}'";
    }
    if (in_array($this->getType(), self::BOOLEAN_TYPES)) {
      return $this->getOnUpdate() ? 'TRUE' : 'FALSE';
    }
    return "'{$this->getOnUpdate()}'";
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
  public function getQuotedReferenceTable(): ?string
  {
    return "`{$this->_referenceTable}`";
  }

  public function isCascadeOnDelete(): bool
  {
    return $this->_cascadeOnDelete;
  }

  public function isCascadeOnUpdate(): bool
  {
    return $this->_cascadeOnUpdate;
  }

  public function getPreviousColumn(): ?Colonne
  {
    return $this->_previousColumn;
  }

  public function setPreviousColumn(?Colonne $previousColumn): self
  {
    $this->_previousColumn = $previousColumn;
    return $this;
  }

  public function getEnumValues(): ?array
  {
    return $this->_enumValues;
  }


  public function getQuotedEnumValues(): ?string
  {
    if ($this->getEnumValues() === null) {
      return null;
    }
    $values = [];
    foreach ($this->getEnumValues() as $value) {
      if (!is_string($value) && !is_numeric($value)) {
        throw new Exception("La valeur énumérée doit être une chaîne de caractères, ou numérique", [
          "table" => $this->getTable()->getName(),
          "column" => $this->getName(),
          "value" => $value,
        ]);
      }
      if (is_string($value)) {
        $values[] = "'{$value}'";
      } else {
        $values[] = $value;
      }
    }
    return implode(',', $values);
  }

  public function getRenameFrom()
  {
    return $this->_renameFrom;
  }

  public function getQuotedRenameFrom(): ?string
  {
    if ($this->getRenameFrom() === null) {
      return null;
    }
    if (!is_string($this->getRenameFrom())) {
      throw new Exception("Le nom de la colonne renommée doit être une chaîne de caractères", [
        "table" => $this->getTable()->getName(),
        "column" => $this->getName(),
        "renameFrom" => $this->getRenameFrom(),
      ]);
    }
    return "`{$this->getRenameFrom()}`";
  }

  public function setRenameFrom(?string $renameFrom): self
  {
    $this->_renameFrom = $renameFrom;
    return $this;
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

  public function getTable(): ?Table
  {
    return $this->_table;
  }

  public function setTable(?Table $table): self
  {
    $this->_table = $table;
    return $this;
  }


  /** -------------------- */


  public function getColumnLine(): string
  {
    $elements = [];
    $elements[] = $this->getQuotedName();
    if ($this->getType() == "ENUM") {
      $elements[] = $this->getType() . "({$this->getQuotedEnumValues()})";
    } else {
      $elements[] = $this->getType() . ($this->getLength() !== null ? ("(" . $this->getLength() . ")") : '');
    }
    if ($this->isUnsigned()) {
      $elements[] = 'UNSIGNED';
    }
    if ($this->isAutoIncrement()) {
      $elements[] = 'AUTO_INCREMENT';
    } else {
      if ($this->getDefault() !== null || $this->isNull()) {
        $elements[] = "DEFAULT {$this->getQuotedDefault()}";
      }
    }
    if ($this->getOnUpdate() !== null) {
      $elements[] = "ON UPDATE {$this->getQuotedOnUpdate()}";
    }
    if ($this->isNull()) {
      $elements[] = 'NULL';
    } else {
      $elements[] = 'NOT NULL';
    }
    if ($this->getComment() !== null) {
      $elements[] = "COMMENT {$this->getQuotedComment()}";
    }

    return implode(' ', $elements);
  }

  public function getAfterLine(): ?string
  {
    if (!$this->getPreviousColumn()) {
      return "FIRST";
    }
    return "AFTER {$this->getPreviousColumn()->getQuotedName()}";
  }

  public function getForeignKeyLine(): ?string
  {
    if (!$this->getReferenceColumn() || !$this->getReferenceTable()) {
      return null;
    }
    $elements = [];
    $elements[] = "FOREIGN KEY ({$this->getQuotedName()})";
    $elements[] = "REFERENCES {$this->getQuotedReferenceTable()}({$this->getQuotedReferenceColumn()})";
    if ($this->isCascadeOnDelete()) {
      $elements[] = 'ON DELETE CASCADE';
    }
    if ($this->isCascadeOnUpdate()) {
      $elements[] = 'ON UPDATE CASCADE';
    }
    return implode(' ', $elements);
  }

  public function getIndexLine($force = false): ?string
  {
    if (!$this->isIndex() && !$force) {
      return null;
    }
    return "INDEX ({$this->getQuotedName()})";
  }

  public function getUniqueLine(): ?string
  {
    if (!$this->isUnique()) {
      return null;
    }
    return "UNIQUE ({$this->getQuotedName()})";
  }

  public function getPrimaryLine(): ?string
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
      $parameters["name"] ?? null,
      /*type*/
      $parameters["type"] ?? null,
      /*length*/
      $parameters["length"] ?? null,
      /*default*/
      $parameters["default"] ?? null,
      /*onUpdate*/
      $parameters["onUpdate"] ?? null,
      /*comment*/
      $parameters["comment"] ?? null,
      /*null*/
      $parameters["null"] ?? false,
      /*autoIncrement*/
      $parameters["autoIncrement"] ?? false,
      /*primary*/
      $parameters["primary"] ?? false,
      /*unique*/
      $parameters["unique"] ?? false,
      /*index*/
      $parameters["index"] ?? false,
      /*unsigned*/
      $parameters["unsigned"] ?? false,
      /*referenceColumn*/
      $parameters["referenceColumn"] ?? null,
      /*referenceTable*/
      $parameters["referenceTable"] ?? null,
      /*cascadeOnDelete*/
      $parameters["cascadeOnDelete"] ?? false,
      /*cascadeOnUpdate*/
      $parameters["cascadeOnUpdate"] ?? false,
      /*enumValues*/
      $parameters["enumValues"] ?? null,
      /*renameFrom*/
      $parameters["renameFrom"] ?? null,
      /*table*/
      $parameters["table"] ?? null,
    );
  }
}
