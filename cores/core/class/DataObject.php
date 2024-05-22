<?php

namespace Core\FileGenerator;

use Core\Db\Schema;
use Core\Db\Schema\Colonne;
use Core\Db\Schema\Table;
use Core\Exception;
use Core\FileGenerator;

class DataObject extends FileGenerator
{

  protected const PATH = ROOT_DIR . '/model/DataObject/';

  public static function generateFiles()
  {
    $schema = self::getSchema();
    $tables = $schema->getTables();
    foreach ($tables as $table) {
      try {
        self::generateFileContent($table, $schema);
      } catch (Exception $e) {
        echo "Erreur lors de la génération du dataObject de la table {$table->getName()} : " . $e->getMessage() . PHP_EOL;
      }
    }
  }

  protected static function generateFileContent(
    Table $table,
    Schema $schema
  ) {
    $tableName = $table->getName();
    $className = self::snakeCaseToPascalCase($tableName);
    $fileFullPath = self::PATH . $className . '.php';
    if (file_exists($fileFullPath)) {
      unlink($fileFullPath);
    }
    $primaryKeys = $table->getPrimaryKeys();
    if (count($primaryKeys) < 1) {
      throw new Exception("La table $tableName n'a pas de clé primaire");
    }
    if (count($primaryKeys) > 1) {
      throw new Exception("La génération de modèle ne supporte pas les clés primaires multiples (table $tableName)");
    }
    $dependances = $table->getDependencies();
    $dependingTables = $table->getDependingTables();
    $tablesNames = array_merge(
      array_map(fn ($table) => $table->getName(), $dependances),
      array_map(fn ($table) => $table->getName(), $dependingTables)
    );
    sort($tablesNames);


    $modelClassName = $className . "Model";
    $content = [];
    $content[] = "namespace Model\DataObject;";
    $content[] = "";
    $content[] = "use Core\DataObject;";
    $content[] = "use Core\Exception;";
    $content[] = "use Model\\$className as $modelClassName;";
    foreach ($tablesNames as $tableName) {
      $content[] = "use Model\\DataObject\\" . self::snakeCaseToPascalCase($tableName) . ";";
    }
    $content[] = "";
    self::addMethodsDoc($table, $content);
    $content[] = "class $className extends DataObject";
    $content[] = "{";
    $content[] = "\tprotected static array \$_objects = [];";
    $content[] = "\tprotected static string \$_modelClass = $modelClassName::class;";
    foreach ($dependances as $dependance) {
      $className = self::snakeCaseToPascalCase($dependance->getName());
      $varName = "\$_" . self::snakeCaseToCamelCase($dependance->getName());
      $content[] = "\tprotected static ?$className $varName = null;";
      $content[] = "\t";
    }
    $content[] = "\t";
    foreach (array_filter($table->getColumns(), fn ($column) => $column->getReferenceTable()) as $colonne) {
      self::addReferenceTableGetterAndSetter($colonne, $content);
      $content[] = "\t";
    }
    foreach ($dependingTables as $dependingTable) {
      foreach (array_filter($dependingTable->getColumns(), fn ($column) => $column->getReferenceTable() === $table->getName()) as $column) {
        self::addDependingTableGetter($table, $column, $content);
        $content[] = "\t";
      }
    }
    $content[] = "}";
    self::putInFile($fileFullPath, $content);
  }

  protected static function addMethodsDoc(
    Table $table,
    array &$content
  ) {
    $content[] = "/**";
    foreach ($table->getColumns() as $column) {
      if ($column->getName() != "id") {
        $pascalName = self::snakeCaseToPascalCase($column->getName());
        $content[] = " * @method mixed get$pascalName()";
      }
    }
    foreach ($table->getColumns() as $column) {
      if ($column->getName() != "id") {
        $pascalName = self::snakeCaseToPascalCase($column->getName());
        $content[] = " * @method static set$pascalName(\$value)";
      }
    }
    $content[] = " */";
  }

  protected static function addReferenceTableGetterAndSetter(
    Colonne $colonne,
    array &$content
  ) {
    $className = self::snakeCaseToPascalCase($colonne->getReferenceTable());
    $varName = "\$this->_" . self::snakeCaseToCamelCase($colonne->getReferenceTable());
    $getterName = "get$className" . "Object";
    $setterName = "set$className" . "Object";
    $content[] = "\tpublic function $getterName(): ?$className";
    $content[] = "\t{";
    $content[] = "\t\tif (!{$varName} || {$varName}->getData(\"{$colonne->getReferenceColumn()}\") != \$this->getData(\"{$colonne->getName()}\")) {";
    $content[] = "\t\t\t{$varName} = self::_getClass()::getOneByFilters([\"{$colonne->getReferenceColumn()}\" => \$this->getData(\"{$colonne->getName()}\")]);";
    $content[] = "\t\t}";
    $content[] = "\t\treturn {$varName};";
    $content[] = "\t}";
    $content[] = "\t";
    $content[] = "\tpublic function $setterName(?{$className} \$value): static";
    $content[] = "\t{";
    $content[] = "\t\t\$this->setData(\"{$colonne->getName()}\", \$value ? \$value->getId() : null);";
    $content[] = "\t\treturn \$this;";
    $content[] = "\t}";
  }

  protected static function addDependingTableGetter(
    Table $table,
    Colonne $colonne,
    array &$content
  ) {
    $className = self::snakeCaseToPascalCase($colonne->getTable()->getName());
    $getterName = "get$className" . "Objects";
    $preloadName = "preload$className" . "Objects";
    $content[] = "\t/** @return {$className}[] */";
    $content[] = "\tpublic function {$getterName}(): array";
    $content[] = "\t{";
    $content[] = "\t\treturn self::_getClass()::getByFilters([\"{$colonne->getName()}\" => \$this->getData(\"{$colonne->getReferenceColumn()}\")]);";
    $content[] = "\t}";
    $content[] = "\t";
    $content[] = "\tpublic static function $preloadName(): void";
    $content[] = "\t{";
    $content[] = "\t\t\$values = array_map(function(\$object){return \$object->getData(\"{$colonne->getReferenceColumn()}\");}, self::\$_objects);";
    $content[] = "\t\tself::_getClass()::preloadByFilters([\"{$colonne->getName()}\" => \$values]);";
    $content[] = "\t}";
  }
}
