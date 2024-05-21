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
    $prefix = $prefix ?: uniqid();
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

  /**
   * @param string|string[] $condition 
   * @return SQLBuilder 
   */
  public function addColonne($colonne)
  {
    return $this->setColonnes(array_merge($this->getColonnes(), is_array($colonne) ? $colonne : [$colonne]));
  }

  /**
   * @param string|string[] $condition 
   * @return SQLBuilder 
   */
  public function addCondition($condition)
  {
    return $this->setConditions(array_merge($this->getConditions(), is_array($condition) ? $condition : [$condition]));
  }

  public function setFilters(array $filters)
  {
    return $this->setConditions([$this->handleFilter($filters)]);
  }

  public function addFilter(array $filter)
  {
    return $this->addCondition($this->handleFilter($filter));
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

  public function handleGenericFilter(
    string $modelClass,
    string $name,
    $value
  ) {
    if (is_array($value)) {
      if (in_array(array_key_first($value), self::OPERATORS_GROUP)) {
        throw new Exception("Invalid filter");
      }
      if (in_array(array_key_first($value), self::OPERATORS_VALUE)) {
        $this->addFilter([$modelClass::al($name) => $value]);
        return;
      }
      $this->addFilter([$modelClass::al($name) => ['$in' => $value]]);
      return;
    }
    $this->addFilter([$modelClass::al($name) => $value]);
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

  const OPERATORS_GROUP = [
    '$or',
    '$and',
    '$not',
  ];

  const OPERATORS_VALUE = [
    '$in',
    '$nin', // 'NOT IN'
    '$eq',
    '$neq', // '!='
    '$isnull',
    '$like',
    '$nlike', // 'NOT LIKE'
    '$gt',
    '$gte',
    '$lt',
    '$lte',
  ];

  public function handleFilter($filter)
  {
    if (empty($filter)) {
      return null;
    }
    if (is_string($filter)) {
      return $filter;
    }
    $key = array_key_first($filter);
    $value = $filter[$key];
    switch ($key) {
      case '$or':
        if (is_array($value) && array_key_first($value) != 0) {
          throw new Exception("Invalid OR filter, must be this format: ['\$or' => [['key1' => 'value1'], ['key2' => 'value2']]], not this format: ['\$or' => ['key1' => 'value1', 'key2' => 'value2']]");
        }
        return "(" . implode(" OR ", array_map(fn ($v) => $this->handleFilter($v), $value)) . ")";
      case 0:
        $value = $filter;
      case '$and':
        if (is_array($value) && array_key_first($value) != 0) {
          throw new Exception("Invalid AND filter, must be this format: ['\$and' => [['key1' => 'value1'], ['key2' => 'value2']]], not this format: ['\$and' => ['key1' => 'value1', 'key2' => 'value2']]");
        }
        return "(" . implode(" AND ", array_map(fn ($v) => $this->handleFilter($v), $value)) . ")";
      case '$not':
        return "(NOT (" . $this->handleFilter($value) . "))";
      default:
        return $this->handleFilterValue($key, $value);
    }
  }

  protected int $countValues = 0;

  public function instanceValue($value)
  {
    $this->countValues++;
    $key = ":value_" . $this->countValues . "_";
    $this->addValue([$key => $value]);
    return $key;
  }

  public function handleFilterValue($key, $value, $noQuoting = true)
  {
    $value = is_array($value) ? $value : ['$eq' => $value];
    $operator = array_key_first($value);
    $value = $value[$operator];
    $quote = function ($v) use ($noQuoting) {
      return $noQuoting ? $v : Db::quoteIdentifier($v);
    };
    switch ($operator) {
      case '$in':
        return $quote($key) . ' IN (' . implode(',', array_map(fn ($v) => $this->instanceValue($v), $value)) . ')';
      case '$nin':
        return $quote($key) . ' NOT IN (' . implode(',', array_map(fn ($v) => $this->instanceValue($v), $value)) . ')';
      case '$eq':
        return $quote($key) . ' = ' . $this->instanceValue($value);
      case '$neq':
        return $quote($key) . ' != ' . $this->instanceValue($value);
      case '$like':
        return $quote($key) . ' LIKE ' . $this->instanceValue($value);
      case '$nlike':
        return $quote($key) . ' NOT LIKE ' . $this->instanceValue($value);
      case '$gt':
        return $quote($key) . ' > ' . $this->instanceValue($value);
      case '$gte':
        return $quote($key) . ' >= ' . $this->instanceValue($value);
      case '$lt':
        return $quote($key) . ' < ' . $this->instanceValue($value);
      case '$lte':
        return $quote($key) . ' <= ' . $this->instanceValue($value);
      case '$isnull':
        return $quote($key) . ' IS ' . (!$value ? 'NOT ' : '') . 'NULL';
    }
  }
}
