<?php

namespace Core;

class SQLBuilder
{
  protected $colonnes = [];
  protected $conditions = [];
  protected $values = [];
  protected $table = "";
  protected $joinTables = []; // $tbl => $liaison
  protected $groupBy = [];
  protected $having = [];
  protected $limit = null;
  protected $offset = null;
  protected $orderBy = [];
  protected $unions = [];
  protected $sql = "";

  /**
   * Constructeur
   * @param array $parameters [
   *  "colonnes" => string[],
   *   "table" => string,
   *   "joinTables" => string["table" => "liaison"],
   *   "conditions" => string[],
   *   "values" => string["alias" => valeur],
   *   "groupBy" => string[],
   *   "having" => string[],
   *   "limit" => null|int,
   *   "offset" => null|int,
   *   "orderBy" => string[],
   * ]
   * @return void 
   */
  function __construct($parameters = [])
  {
    foreach (($parameters ?? []) as $parameter => $value) {
      $setter = "set" . ucfirst($parameter);
      if (method_exists($this, $setter)) $this->$setter($value);
    }
  }

  public function getColonnes(): array
  {
    return $this->colonnes;
  }

  public function getConditions(): array
  {
    return $this->conditions;
  }

  public function getValues(): array
  {
    return $this->values;
  }

  public function getTable(): string
  {
    return $this->table;
  }

  public function getJoinTables(): array
  {
    return $this->joinTables;
  }

  public function getGroupBy(): array
  {
    return $this->groupBy;
  }

  public function getHaving(): array
  {
    return $this->having;
  }

  public function getLimit()
  {
    return $this->limit;
  }

  public function getOffset()
  {
    return $this->offset;
  }

  public function getOrderBy()
  {
    return $this->orderBy;
  }

  public function getUnions()
  {
    return $this->unions;
  }

  public function getUnion($alias)
  {
    return $this->unions[$alias] ?? null;
  }

  public function getSql($force = false)
  {
    if ($force) return $this->generateSql();
    return $this->sql ?: $this->generateSql();
  }

  public function setColonnes($colonnes): self
  {
    $this->colonnes = $colonnes;
    return $this;
  }

  public function setConditions($conditions): self
  {
    $this->conditions = $conditions;
    return $this;
  }

  public function setValues($values): self
  {
    $this->values = $values;
    return $this;
  }

  public function setTable($table): self
  {
    $this->table = $table;
    return $this;
  }

  public function setJoinTables($joinTables): self
  {
    $this->joinTables = $joinTables;
    return $this;
  }

  public function generatePlaceholdersAndValues($values, $prefix = ""): string
  {
    $placeholders = [];
    $values = array_values($values);
    foreach ($values as $key => $value) {
      $placeholder = ":" . $prefix . "_" . $key . "_";
      $placeholders[] = $placeholder;
      $this->addValue([$placeholder => $value]);
    }
    return implode(",", $placeholders);
  }

  public function setAutoJoinTables($autoJoinTables): self
  {
    return $this->addAutoJoinTables($autoJoinTables);
  }

  public function setGroupBy($groupBy): self
  {
    $this->groupBy = $groupBy;
    return $this;
  }

  public function setHaving($having): self
  {
    $this->having = $having;
    return $this;
  }

  public function setLimit($limit): self
  {
    $this->limit = $limit;
    return $this;
  }

  public function setOffset($offset): self
  {
    $this->offset = $offset;
    return $this;
  }

  public function setOrderBy($orderBy): self
  {
    $this->orderBy = $orderBy;
    return $this;
  }

  public function setUnions($unions): self
  {
    $this->unions = $unions;
    return $this;
  }

  public function addColonne($colonne)
  {
    return $this->setColonnes(array_merge($this->getColonnes(), is_array($colonne) ? $colonne : [$colonne]));
  }

  public function addCondition($condition)
  {
    return $this->setConditions(array_merge($this->getConditions(), is_array($condition) ? $condition : [$condition]));
  }

  public function addValue($value)
  {
    return $this->setValues($this->getValues() + (is_array($value) ? $value : [$value]));
  }

  public function addJoinTable($table)
  {
    return $this->setJoinTables($this->getJoinTables() + (is_array($table) ? $table : [$table]));
  }

  public function addAutoJoinTable($alias, $class, $aliasJoined, $classJoined)
  {
    if (!class_exists($class) or !class_exists($classJoined)) return $this;
    $tbl = $class::$tbl . " " . $alias;
    $classKeys = $class::$fkeys ?? [];
    $classJoinedKeys = $classJoined::$fkeys ?? [];
    $join = $alias . "." . (in_array($classJoined, array_keys($classKeys)) ? $class::$fkeys[$classJoined] : $class::$pkey) . " = ";
    $join .= $aliasJoined . "." . (in_array($class, array_keys($classJoinedKeys)) ? $classJoined::$fkeys[$class] : $classJoined::$pkey);
    if (!in_array($classJoined, array_keys($classKeys)) && !in_array($class, array_keys($classJoinedKeys))) {
      $join .= "/* Les fkeys ne sont pas initialisés correctement dans $class ou $classJoined */";
    }
    return $this->addJoinTable([$tbl => $join]);
  }
  public function addAutoJoinTables($autoJoinTables)
  {
    if (isset($autoJoinTables["alias"])) $autoJoinTables = [$autoJoinTables];
    foreach ($autoJoinTables as $autoJoinTable) {
      $this->addAutoJoinTable(
        $autoJoinTable["alias"] ?? null,
        $autoJoinTable["class"] ?? null,
        $autoJoinTable["aliasJoined"] ?? null,
        $autoJoinTable["classJoined"] ?? null
      );
    }
    return $this;
  }

  const EQUAL_OPERATOR = '$eq';
  const NOT_EQUAL_OPERATOR = '$ne';
  const IN_OPERATOR = '$in';
  const NOT_IN_OPERATOR = '$nin';
  const LIKE_OPERATOR = '$like';
  const ALL_LIKE_OPERATOR = '$alllike';
  const NOT_LIKE_OPERATOR = '$nlike';
  const NOT_ALL_LIKE_OPERATOR = '$notalllike';
  const GREATER_THAN_OPERATOR = '$gt';
  const GREATER_THAN_OR_EQUAL_OPERATOR = '$gte';
  const LESS_THAN_OPERATOR = '$lt';
  const LESS_THAN_OR_EQUAL_OPERATOR = '$lte';
  const IS_NULL_OPERATOR = '$null';
  public function handleGenericFilter(
    string $modelClass,
    string $name,
    $value
  ) {
    if (is_array($value)) {
      $this->addGenericInFilter($modelClass, $name, $value);
    } else if (is_string($value)) {
      if (str_starts_with($value, self::EQUAL_OPERATOR)) {
        $this->addGenericEqualFilter($modelClass, $name, substr($value, strlen(self::EQUAL_OPERATOR)));
      } else if (str_starts_with($value, self::NOT_EQUAL_OPERATOR)) {
        $this->addGenericEqualFilter($modelClass, $name, substr($value, strlen(self::NOT_EQUAL_OPERATOR)), true);
      } else if (str_starts_with($value, self::IN_OPERATOR)) {
        $this->addGenericInFilter($modelClass, $name, substr($value, strlen(self::IN_OPERATOR)));
      } else if (str_starts_with($value, self::NOT_IN_OPERATOR)) {
        $this->addGenericInFilter($modelClass, $name, substr($value, strlen(self::NOT_IN_OPERATOR)), true);
      } else if (str_starts_with($value, self::LIKE_OPERATOR)) {
        $this->addGenericLikeFilter($modelClass, $name, substr($value, strlen(self::LIKE_OPERATOR)));
      } else if (str_starts_with($value, self::NOT_LIKE_OPERATOR)) {
        $this->addGenericLikeFilter($modelClass, $name, substr($value, strlen(self::NOT_LIKE_OPERATOR)), true);
      } else if (str_starts_with($value, self::ALL_LIKE_OPERATOR)) {
        $this->addGenericLikeFilter($modelClass, $name, substr($value, strlen(self::ALL_LIKE_OPERATOR)), false, false);
      } else if (str_starts_with($value, self::NOT_ALL_LIKE_OPERATOR)) {
        $this->addGenericLikeFilter($modelClass, $name, substr($value, strlen(self::NOT_ALL_LIKE_OPERATOR)), true, false);
      } else if (str_starts_with($value, self::GREATER_THAN_OPERATOR)) {
        $this->addGenericComparisonFilter($modelClass, $name, substr($value, strlen(self::GREATER_THAN_OPERATOR)), ">");
      } else if (str_starts_with($value, self::GREATER_THAN_OR_EQUAL_OPERATOR)) {
        $this->addGenericComparisonFilter($modelClass, $name, substr($value, strlen(self::GREATER_THAN_OR_EQUAL_OPERATOR)), ">=");
      } else if (str_starts_with($value, self::LESS_THAN_OPERATOR)) {
        $this->addGenericComparisonFilter($modelClass, $name, substr($value, strlen(self::LESS_THAN_OPERATOR)), "<");
      } else if (str_starts_with($value, self::LESS_THAN_OR_EQUAL_OPERATOR)) {
        $this->addGenericComparisonFilter($modelClass, $name, substr($value, strlen(self::LESS_THAN_OR_EQUAL_OPERATOR)), "<=");
      } else if (str_starts_with($value, self::IS_NULL_OPERATOR)) {
        $this->addGenericIsNullFilter($modelClass, $name, $value);
      } else {
        $this->addGenericInFilter($modelClass, $name, $value);
      }
    } else {
      $this->addGenericEqualFilter($modelClass, $name, $value);
    }
  }

  public function addGenericEqualFilter(
    string $modelClass,
    string $name,
    $value
  ) {
    $this->addCondition($modelClass::al($name) . " = :$name");
    $this->addValue([":$name" => $value]);
  }

  public function addGenericInFilter(
    string $modelClass,
    string $name,
    $value,
    bool $not = false
  ) {
    $this->addCondition($modelClass::al($name) . ($not ? " NOT" : "") . " IN (" . $this->generatePlaceholdersAndValues($value, $name) . ")");
  }

  public function addGenericLikeFilter(
    string $modelClass,
    string $name,
    $value,
    bool $not = false,
    bool $or = false
  ) {
    if (is_array($value)) {
      $conditions = [];
      foreach ($value as $val) {
        $conditions[] = $modelClass::al($name) . ($not ? " NOT" : "") . " LIKE :$name";
        $this->addValue([":$name" => $val]);
      }
      $this->addCondition("(" . implode($or ? " OR " : " AND ", $conditions) . ")");
    } else {
      $this->addCondition($modelClass::al($name) . ($not ? " NOT" : "") . " LIKE :$name");
      $this->addValue([":$name" => $value]);
    }
  }

  public function addGenericComparisonFilter(
    string $modelClass,
    string $name,
    $value,
    string $operator,
  ) {
    $this->addCondition($modelClass::al($name) . " $operator :$name");
    $this->addValue([":$name" => $value]);
  }

  public function addGenericIsNullFilter(
    string $modelClass,
    string $name,
    bool $not,
  ) {
    $this->addCondition($modelClass::al($name) . " IS" . ($not ? " NOT" : "") . " NULL");
  }

  public function addGroupBy($groupBy)
  {
    return $this->setGroupBy(array_merge($this->getGroupBy(), is_array($groupBy) ? $groupBy : [$groupBy]));
  }

  public function addHaving($having)
  {
    return $this->setHaving(array_merge($this->getHaving(), is_array($having) ? $having : [$having]));
  }

  public function addOrderBy($orderBy)
  {
    return $this->setOrderBy(array_merge($this->getOrderBy(), is_array($orderBy) ? $orderBy : [$orderBy]));
  }

  public function addUnion($query, $alias = null)
  {
    return $this->setUnions($alias ? $this->getUnions() + [$alias => $query] : array_merge($this->getUnions(), [$query]));
  }

  protected function generateSql()
  {
    /* MISE EN FORME */
    $tables = [$this->getTable() => null] + $this->getJoinTables();
    $tables = implode(PHP_EOL . "LEFT JOIN ", array_map(function ($liaison, $tbl) {
      return $tbl . ($liaison ? " ON $liaison" : "");
    }, $tables, array_keys($tables)));

    $where = $this->getConditions() ? "WHERE " . implode(PHP_EOL . "  AND ", $this->getConditions()) : "";
    $having = $this->getHaving() ? "HAVING " . implode(PHP_EOL . "   AND ", $this->getHaving()) : "";
    $colonnes = implode(',' . PHP_EOL . "  ", $this->getColonnes());
    $group = $this->getGroupBy() ? "GROUP BY " . PHP_EOL . "  " . implode("," . PHP_EOL . "  ", $this->getGroupBy()) : "";
    $order = $this->getOrderBy() ? "ORDER BY" . PHP_EOL . "  " . implode("," . PHP_EOL . "  ", $this->getOrderBy()) : "";
    $limit = $this->getLimit() ? ("LIMIT " . $this->getLimit() . ($this->getOffset() ? " OFFSET " . $this->getOffset() : "")) : "";
    $comment = "";
    $done = false;
    if (DEBUG) {
      $backtrace = debug_backtrace();
      array_walk($backtrace, static function ($trace) use (&$tmpIndex, &$comment, &$done) {
        if ($done) return;
        $file = $trace["file"] ?? null;
        $line = $trace["line"] ?? null;
        if (!$tmpIndex && __FILE__ != $file && $trace["class"] != self::class && !str_ends_with($file ?? "", "core/class/Db.php")) {
          $comment = PHP_EOL . "-- " . $trace["class"] . $trace["type"] . $trace["function"] . PHP_EOL . ($file && $line ? ("-- " . $file . " on line " . $line) : "");
          $done = true;
        }
      });
    }
    /* SQL */
    $this->sql = $comment . PHP_EOL;
    // $this->sql .= implode("UNION".PHP_EOL,array_map(function($query){return $query->getSql();},$this->getUnions()));
    $this->sql .= "SELECT" . PHP_EOL . "  " . $colonnes . PHP_EOL;
    $this->sql .= "FROM $tables" . PHP_EOL;
    $this->sql .= $where ? $where . PHP_EOL : "";
    $this->sql .= $group ? $group . PHP_EOL : "";
    $this->sql .= $having ? $having . PHP_EOL : "";
    $this->sql .= $order ? $order . PHP_EOL : "";
    $this->sql .= $limit ? $limit . PHP_EOL : "";
    return $this->getSql();
  }
}
