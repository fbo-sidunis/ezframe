<?php

namespace Core\FileGenerator;

use Core\Db\Schema\Table;
use Core\Exception;
use Core\FileGenerator;

class Model extends FileGenerator
{

  protected const PATH = ROOT_DIR . '/model/';

  public static function generateFiles()
  {
    $schema = self::getSchema();
    $tables = $schema->getTables();
    foreach ($tables as $table) {
      try {
        self::generateFileContent($table);
      } catch (Exception $e) {
        echo "Erreur lors de la génération du modèle de la table {$table->getName()} : " . $e->getMessage() . PHP_EOL;
      }
    }
  }

  protected static function generateFileContent(
    Table $table
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
    $primaryKey = $primaryKeys[0];
    $content = [];
    $content[] = "namespace Model;";
    $content[] = "";
    $content[] = "use Core\Db;";
    $content[] = "use Core\SQLBuilder;";
    $content[] = "use PDO;";
    $content[] = "use Core\Exception;";
    $content[] = "";
    $content[] = "class $className extends Db";
    $content[] = "{";
    $content[] = "\tpublic static \$tbl = \"$tableName\";";
    $content[] = "\tpublic static \$pkey = \"{$primaryKey->getName()}\";";
    $content[] = "\t";
    self::addGetByFiltersMethod($table, $content);
    $content[] = "\t";
    self::addGetColumns($table, $content);
    $content[] = "\t";
    self::addGetJoinTablesMethod($table, $content);
    $content[] = "\t";
    self::addGetGroupByMethod($table, $content);
    $content[] = "\t";
    self::addGetOrderColumnsAuthorizedMethod($table, $content);
    $content[] = "\t";
    self::addAddFiltersMethod($table, $content);
    $content[] = "\t";
    self::addGetSpecialQueryMethod($table, $content);
    $content[] = "}";
    self::putInFile($fileFullPath, $content);
  }

  protected static function addGetByFiltersMethod(
    Table $table,
    array &$content
  ) {
    $content[] = "\tpublic static function getByFilters(";
    $content[] = "\t\tarray \$queryDatas = [],";
    $content[] = "\t\tarray \$filters = [],";
    $content[] = "\t\t?string \$specialQuery = null";
    $content[] = "\t) {";
    $content[] = "\t\t\$query = new SQLBuilder([";
    $content[] = "\t\t\t\"colonnes\" => self::getColumns(),";
    $content[] = "\t\t\t\"table\" => self::tbl(),";
    $content[] = "\t\t\t\"joinTables\" => self::getJoinTables(),";
    $content[] = "\t\t\t\"groupBy\" => self::getGroupBy(),";
    $content[] = "\t\t\t\"limit\" => \$queryDatas[\"length\"] ?? null,";
    $content[] = "\t\t\t\"offset\" => \$queryDatas[\"start\"] ?? null,";
    $content[] = "\t\t]);";
    //Fin Init
    $content[] = "\t\t";
    //Début de l'order by'
    $content[] = "\t\t\$orderCol = \$queryDatas[\"orderCol\"] ?? null;";
    $content[] = "\t\t\$direction = \$queryDatas[\"direction\"] ?? null;";
    $content[] = "\t\t\$orderColumnsAuthorized = self::getOrderColumnsAuthorized();";
    $content[] = "\t\tif (in_array(\$orderCol, array_keys(\$orderColumnsAuthorized)) && in_array(\$direction, [\"ASC\", \"DESC\"])) {";
    $content[] = "\t\t\t\$query->addOrderBy(\$orderColumnsAuthorized[\$orderCol] . \" \" . \$direction);";
    $content[] = "\t\t}";
    //Fin de l'order by'
    $content[] = "\t\t";
    //Début des filtres
    foreach ($table->getColumns() as $column) {
      $camelCaseName = self::snakeCaseToCamelCase($column->getName());
      $content[] = "\t\t\$$camelCaseName = \$filters[\"$camelCaseName\"] ?? null;";
    }
    $content[] = "\t\t";
    foreach ($table->getColumns() as $column) {
      $camelCaseName = self::snakeCaseToCamelCase($column->getName());
      $content[] = "\t\tif (\$$camelCaseName !== null) {";
      $content[] = "\t\t\tif (is_array(\$$camelCaseName)) {";
      $content[] = "\t\t\t\t\$query->addCondition(self::al(\"{$column->getName()}\") . \" IN (\" . \$query->generatePlaceholdersAndValues(\$$camelCaseName, \"{$column->getName()}\") . \")\");";
      $content[] = "\t\t\t} else {";
      $content[] = "\t\t\t\t\$query->addCondition(self::al(\"{$column->getName()}\") . \" = :{$column->getName()}\");";
      $content[] = "\t\t\t\t\$query->addValue([\":{$column->getName()}\" => \$$camelCaseName]);";
      $content[] = "\t\t\t}";
      $content[] = "\t\t}";
      $content[] = "\t\t";
    }
    $content[] = "\t\t";
    $content[] = "\t\tself::addFilters(\$query, \$filters);";
    $content[] = "\t\t";
    //Fin des filtres
    //Début des SpecialQuery (case count, id et ids)
    $content[] = "\t\tif (\$specialQuery !== null) {";
    $content[] = "\t\t\tswitch (\$specialQuery) {";
    $content[] = "\t\t\t\tcase \"count\":";
    $content[] = "\t\t\t\t\t\$query->setColonnes([";
    $content[] = "\t\t\t\t\t\t\"COUNT(*) OVER()\",";
    $content[] = "\t\t\t\t\t]);";
    $content[] = "\t\t\t\t\treturn intval(self::db_one_col(\$query->getSql(), \$query->getValues()));";
    $content[] = "\t\t\t\t\tbreak;";
    $content[] = "\t\t\t\tcase \"id\":";
    $content[] = "\t\t\t\t\t\$query->setColonnes([";
    $content[] = "\t\t\t\t\t\tself::al(self::\$pkey),";
    $content[] = "\t\t\t\t\t]);";
    $content[] = "\t\t\t\t\treturn self::db_one_col(\$query->getSql(), \$query->getValues());";
    $content[] = "\t\t\t\t\tbreak;";
    $content[] = "\t\t\t\tcase \"ids\":";
    $content[] = "\t\t\t\t\t\$query->setColonnes([";
    $content[] = "\t\t\t\t\t\tself::al(self::\$pkey),";
    $content[] = "\t\t\t\t\t]);";
    $content[] = "\t\t\t\t\treturn self::db_all(\$query->getSql(), \$query->getValues(), PDO::FETCH_COLUMN);";
    $content[] = "\t\t\t\t\tbreak;";
    $content[] = "\t\t\t\tdefault:";
    $content[] = "\t\t\t\t\treturn self::getSpecialQuery(\$query, \$queryDatas, \$filters, \$specialQuery);";
    $content[] = "\t\t\t\t\tbreak;";
    $content[] = "\t\t\t}";
    $content[] = "\t\t}";
    //Fin des SpecialQuery
    $content[] = "\t\t";
    $content[] = "\t\treturn self::db_all(\$query->getSql(), \$query->getValues());";
    //Fin du code de la méthode
    $content[] = "\t}";
  }

  protected static function addGetOrderColumnsAuthorizedMethod(
    Table $table,
    array &$content
  ) {
    $content[] = "\tprotected static function getOrderColumnsAuthorized() {";
    $content[] = "\t\treturn [";
    foreach ($table->getColumns() as $column) {
      $content[] = "\t\t\t\"{$column->getName()}\" => self::al(\"{$column->getName()}\"),";
    }
    $content[] = "\t\t];";
    $content[] = "\t}";
  }

  protected static function addGetJoinTablesMethod(
    Table $table,
    array &$content
  ) {
    $content[] = "\tprotected static function getJoinTables() {";
    $content[] = "\t\t//Pour customiser les jointures, il faut surcharger cette méthode dans le modèle";
    $content[] = "\t\treturn [];";
    $content[] = "\t}";
  }

  protected static function addGetColumns(
    Table $table,
    array &$content
  ) {
    $content[] = "\tprotected static function getColumns() {";
    $content[] = "\t\treturn [";
    $content[] = "\t\t\tself::al(\"*\"),";
    $content[] = "\t\t];";
    $content[] = "\t}";
  }

  protected static function addAddFiltersMethod(
    Table $table,
    array &$content
  ) {
    $content[] = "\tprotected static function addFilters(SQLBuilder \$query, array \$filters) {";
    $content[] = "\t\t//Pour customiser les filtres, il faut surcharger cette méthode dans le modèle";
    $content[] = "\t}";
  }

  protected static function addGetSpecialQueryMethod(
    Table $table,
    array &$content
  ) {
    $content[] = "\tprotected static function getSpecialQuery(SQLBuilder \$query, array \$queryDatas, array \$filters, string \$specialQuery) {";
    $content[] = "\t\t//Pour customiser les requêtes spéciales, il faut surcharger cette méthode dans le modèle";
    $content[] = "\t\tthrow new Exception(\"La requête spéciale \$specialQuery n'est pas supportée\");";
    $content[] = "\t}";
  }

  protected static function addGetGroupByMethod(
    Table $table,
    array &$content
  ) {
    $content[] = "\tprotected static function getGroupBy() {";
    $content[] = "\t\t//Pour customiser les group by, il faut surcharger cette méthode dans le modèle";
    $content[] = "\t\treturn [";
    $content[] = "\t\t\tself::al(self::\$pkey),";
    $content[] = "\t\t];";
    $content[] = "\t}";
  }
}
