<?php

namespace Core;

use Core\User;
use Core\Exception;
use JsonSerializable;

class DataObject implements JsonSerializable
{

  protected static array $_objects = [];
  protected static string $_modelClass;
  protected array $origDatas = [];
  protected int|string|null $_id;

  /**
   * @param array $datas 
   * @return void 
   * @throws Exception 
   */
  function __construct(
    protected array $datas
  ) {
    if (!isset(static::$_modelClass)) {
      throw new Exception("Model class not defined");
    }
    $this->setId($datas[static::_getModelClass()::$pkey] ?? null);
    if ($this->getId()) {
      static::$_objects[$this->getId()] = $this;
    }
  }

  /** @return void  */
  function __destruct()
  {
    if ($this->_id) {
      unset(static::$_objects[$this->_id]);
    }
  }

  /**
   * Récupère l'identifiant de l'objet
   */
  public function getId()
  {
    return $this->_id;
  }

  protected function setId($id)
  {
    $this->_id = $id;
  }

  /**
   * @param string $key 
   * @return mixed 
   */
  public function getData(string $key)
  {
    return $this->datas[$key] ?? null;
  }

  /**
   * @param string $key 
   * @return mixed 
   */
  public function getOrigData(string $key)
  {
    return $this->origDatas[$key] ?? $this->datas[$key] ?? null;
  }

  /** @return array  */
  public function getDatas(): array
  {
    return $this->datas;
  }

  /** @return array  */
  public function getOrigDatas(): array
  {
    return array_merge($this->datas, $this->origDatas);
  }

  /**
   * @param string $key 
   * @param mixed $value 
   * @return $this 
   */
  public function setData(string $key, $value)
  {
    $origData = $this->origDatas[$key] ?? null;
    if ($origData === $value) {
      if (isset($this->origDatas[$key])) {
        unset($this->origDatas[$key]);
      }
    } else if (!isset($this->origDatas[$key])) {
      $this->origDatas[$key] = $this->datas[$key] ?? null;
    }
    $this->datas[$key] = $value;
    return $this;
  }

  /**
   * @param array $datas 
   * @return $this 
   */
  public function setDatas(array $datas)
  {
    foreach ($datas as $key => $value) {
      $this->setData($key, $value);
    }
    return $this;
  }

  /** @return $this  */
  public function save($noclean = false)
  {
    $modelClass = static::_getModelClass();
    if ($this->getId()) {
      $modelClass::updateBy($this->getId(), $noclean ? $this->getDatas() : $this->getCleanDatas());
      $this->setDatas($modelClass::getBy($this->getId()));
    } else {
      $this->setDatas($modelClass::create($this->datas));
      $this->setId($this->getData($modelClass::$pkey));
      static::$_objects[$this->getId()] = $this;
    }
    $this->origDatas = $this->datas;
    return $this;
  }

  /** @return array  */
  public function getCleanDatas()
  {
    $cleanDatas = [];
    $columnsToRemove = [
      "created_at",
      "lastupdate_date",
    ];
    foreach ($this->datas as $key => $value) {
      if (!in_array($key, $columnsToRemove)) {
        $cleanDatas[$key] = $value;
      }
    }
    return $cleanDatas;
  }

  /** @return void  */
  public function delete()
  {
    if ($this->_id) {
      static::_getModelClass()::removeBy($this->getId());
      unset(static::$_objects[$this->_id]);
      $this->_id = null;
    }
  }

  /**
   * @param int|string $id 
   * @return null|static 
   */
  public static function getById(int|string $id): null|static
  {
    if (isset(static::$_objects[$id])) return static::$_objects[$id];
    $datas = static::_getModelClass()::getBy($id);
    if (!$datas) return null;
    return new static($datas);
  }


  /**
   * Récupère une liste d'objets selon les filtres
   * @param array $ids 
   * @return static[] 
   */
  public static function getListByIds(array $ids)
  {
    if (!$ids) {
      return [];
    }
    static::preloadByIds($ids);
    $func_ids = function ($id) use (&$ids) {
      return in_array($id, $ids);
    };
    $res = array_filter(static::$_objects, $func_ids, ARRAY_FILTER_USE_KEY);
    //we sort the resulting array to keep the same order as the ids array
    $res = array_replace(array_flip($ids), $res);
    return $res;
  }

  /**
   * @return static[]
   */
  public static function getList($force = false)
  {
    if ($force) {
      static::preloadAll();
    }
    return static::$_objects;
  }

  /**
   * Récupère une liste d'objets selon les filtres
   * @param array $ids 
   * @return static[] 
   */
  public static function preloadByIds(array $ids)
  {
    $objects = [];
    $idsLoaded = static::$_objects ? array_keys(static::$_objects) : [];
    $idsToLoad = array_diff($ids, $idsLoaded);
    $modelClass = static::_getModelClass();
    $entries = $idsToLoad ? $modelClass::getAllBy($idsToLoad) : [];
    foreach ($entries as $entry) {
      $objects[$entry[$modelClass::$pkey]] = new static($entry);
    }
  }

  public static function preloadByFilters(
    array $filters = [],
  ): void {
    $modelClass = static::_getModelClass();
    if (!method_exists($modelClass, "getByFilters")) {
      throw new Exception("Model class " . $modelClass . " does not have a getByFilters method");
    }
    $ids = $modelClass::getByFilters([], $filters, "ids");
    if (!$ids) return;
    static::preloadByIds($ids);
  }

  /** @return void  */
  public static function preloadAll()
  {
    $entries = static::_getModelClass()::getList();
    foreach ($entries as $entry) {
      new static($entry);
    }
  }


  /** @return array  */
  public function jsonSerialize(): array
  {
    return $this->__toArray();
  }

  function __toArray(): array
  {
    return $this->datas;
  }

  function __toString(): string
  {
    return implode(" ", [
      "Object",
      "#" . $this->getId() ?? "null",
      "of class",
      static::class,
    ]);
  }

  /**
   * @param array $datas 
   * @return static 
   */
  public static function create(array $datas)
  {
    $object = new static($datas);
    $object->save();
    return $object;
  }

  private static function snakeCaseToCamelCase(string $string)
  {
    return lcfirst(str_replace('_', '', ucwords($string, '_')));
  }

  private static function camelCaseToSnakeCase(string $string)
  {
    return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
  }

  public static function getByFilters(
    array $queryDatas = [],
    array $filters = [],
  ): array {
    $modelClass = static::_getModelClass();
    if (!method_exists($modelClass, "getByFilters")) {
      throw new Exception("Model class " . $modelClass . " does not have a getByFilters method");
    }
    $ids = $modelClass::getByFilters($queryDatas, $filters, "ids");
    if (!$ids) return [];
    return static::getListByIds($ids);
  }

  public static function getOneByFilters(
    array $queryDatas = [],
    array $filters = [],
  ): ?static {
    $modelClass = static::_getModelClass();
    if (!method_exists($modelClass, "getByFilters")) {
      throw new Exception("Model class " . $modelClass . " does not have a getByFilters method");
    }
    $queryDatas["length"] = 1;
    $id = $modelClass::getByFilters($queryDatas, $filters, "id");
    if (!$id) return null;
    return static::getById($id);
  }

  protected static function _getClass(): string
  {
    $overridenClasses = getConfig("overrides.dataObjects") ?: [];
    if (isset($overridenClasses[static::class])) {
      return $overridenClasses[static::class];
    }
    return static::class;
  }

  protected static function _getModelClass(): string
  {
    $overridenClasses = getConfig("overrides.models") ?: [];
    if (isset($overridenClasses[static::$_modelClass])) {
      return $overridenClasses[static::$_modelClass];
    }
    return static::$_modelClass;
  }

  //Fonctions magiques pour les getters et setters, permettent de faire $object->getNomClient() au lieu de $object->getData("nom_client")
  public function __call($name, $arguments)
  {
    $action = substr($name, 0, 3);
    if ($action == "get") {
      $key = self::camelCaseToSnakeCase(substr($name, 3));
      return $this->getData($key);
    } elseif ($action == "set") {
      $key = self::camelCaseToSnakeCase(substr($name, 3));
      return $this->setData($key, $arguments[0]);
    }
  }
}
