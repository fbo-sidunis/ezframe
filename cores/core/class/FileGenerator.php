<?php

namespace Core;

use Core\Db\Schema;

class FileGenerator
{
  protected static $schema;

  protected static function getSchema()
  {
    if (!self::$schema) {
      $schema = new Schema;
      $schema->loadTables();
      self::$schema = $schema;
    }
    return self::$schema;
  }

  protected static function snakeCaseToCamelCase($string)
  {
    return lcfirst(self::snakeCaseToPascalCase($string));
  }

  protected static function camelCaseToSnakeCase($string)
  {
    $str = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
    return $str;
  }

  protected static function snakeCaseToPascalCase($string)
  {
    return str_replace('_', '', ucwords($string, '_'));
  }

  protected static function putInFile(
    string $filePath,
    array $content
  ) {
    //the folder might not exist, we must create it if it doesn't
    $folderPath = dirname($filePath);
    if (!is_dir($folderPath)) {
      mkdir($folderPath, 0777, true);
    }
    $fileContent = "<?php\n\n" . implode("\n", $content);
    file_put_contents($filePath, $fileContent);
  }
}
