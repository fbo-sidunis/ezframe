<?php

/**
  ------------------------------------------------------
  METHODES
  ------------------------------------------------------

  Syntaxe :
  lafonctiopn($sql,$datas) -> $sql : string contenant la requête à executer
  -> $datas : variables (dans un array associatif) à passer à la requête

  Liste des méthodes :
  all() -> retourne un Array contenant TOUTES les lignes du résultat de la requête
  one() -> retourne un Array contenant LA PREMIERRE ligne du résultat de la requête
  insert -> retourne l' ID nouvellement créé par une requête INSERT
  exec() -> Pour les requêtes DELETE / UPDATE : retourne le nombre de lignes impactées

 */

namespace Core;

use Exception;
use Monolog\Logger;
use PDO;
use PDOException;

class Db
{

  private $bdd = null;
  private $prepare = NULL;
  private $rowCount = 0;
  private $res = NULL;
  private $host = "localhost";
  private $user = "root";
  private $pwd = "example_pw";
  private $dbname = "example_db";
  private $port = "3306";
  private $charset = "utf8";
  protected static $pkey = "id";
  protected static $db = null;
  protected static $tbl = null;
  protected static $cols = [];
  public const ALIAS = "";

  /**
   * Constructeur
   */
  function __construct($database = ENV)
  {
    if (!empty($database)) {
      $jsonBDD = json_decode(file_get_contents(ROOT_DIR . "config/bdd.json"));
      $jsonDB = $jsonBDD->$database;
      $this->host = $jsonDB->host;
      $this->user = $jsonDB->username;
      $this->pwd = $jsonDB->password;
      $this->dbname = $jsonDB->dbname;
      $this->port = !empty($jsonDB->port) ? $jsonDB->port : 3306;
      $this->charset = !empty($jsonDB->charset) ? $jsonDB->charset : "utf8";
      // print_r($jsonDB);
    }
    $this->cnx();
  }

  public static function getDb()
  {
    self::$db = self::$db ?? new Db();
    return self::$db;
  }

  /**
   * connexion à la BDD
   */
  private function cnx()
  {
    $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=' . $this->charset . ';port=' . $this->port;
    try {
      $bdd = new PDO($dsn, $this->user, $this->pwd);
      // Activation des erreurs PDO
      $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      // mode de fetch par défaut : FETCH_ASSOC / FETCH_OBJ / FETCH_BOTH
      $bdd->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo "<br>dsn : $dsn";
      die('Erreur : ' . $e->getMessage());
    }
    $this->bdd = $bdd;
  }

  /**
   * Execute une requête
   * @param string $sql [La requête]
   * @param array|null $datas [Les datas]
   * @return void 
   */
  private function db_query($sql, $datas = NULL)
  {
    try {
      $this->prepare = $this->bdd->prepare($sql);
      $this->res = $this->prepare->execute($datas);
      $this->rowCount = $this->prepare->rowCount();
    } catch (\Exception $e) {
      $logger = new Logger('db', [
        new \Monolog\Handler\RotatingFileHandler(ROOT_DIR . "logs/db/db.log", Logger::ERROR, 30)
      ]);
      // en cas d'erreur :
      $logger->error($e->getMessage());
      $logger->error("Requête : " . $sql, $datas ?: []);
      echo "<br><b>Erreur ! " . $e->getMessage() . "</b>" . PHP_EOL;
      echo "<pre> La requete :" . $sql . "</pre>" . PHP_EOL;
      echo " <pre>Les datas : " . PHP_EOL;
      print_r($datas);
      echo "</pre>" . PHP_EOL;
    }
  }

  /**
   * 
   * @param mixed $sql 
   * @param mixed|null $datas 
   * @param int $fetchmode 
   * @return mixed 
   */
  public static function db_all($sql, $datas = NULL, $fetchmode = PDO::FETCH_ASSOC)
  {
    $oDb = self::getDb();
    $oDb->db_query($sql, $datas);
    return $oDb->prepare->fetchAll($fetchmode);
  }

  public static function db_all_group($sql = '', $datas = null)
  {
    return static::db_all($sql, $datas, PDO::FETCH_GROUP);
  }

  public static function db_all_unique($sql = '', $datas = null)
  {
    return static::db_all($sql, $datas, PDO::FETCH_UNIQUE);
  }

  protected static function db_one($sql, $datas = NULL, $fetchmode = PDO::FETCH_ASSOC)
  {

    try {
      $oDb = self::getDb();
      $oDb->db_query($sql, $datas);
      return $oDb->prepare->fetch($fetchmode);
    } catch (\Exception $e) {
      echo "Erreur " . $e->getMessage();
      echo "<br> SQL :" . $sql;
      echo "<br> datas :" . print_r($datas, true);
    }
  }
  protected static function db_one_col($sql, $datas = NULL)
  {
    return static::db_one($sql, $datas, PDO::FETCH_COLUMN);
  }

  protected static function db_insert($sql, $datas = NULL, $returnId = TRUE)
  {
    $oDb = self::getDb();
    $oDb->db_query($sql, $datas);
    return $returnId ? $oDb->bdd->lastInsertId() : $oDb->res;
  }

  public static function db_exec($sql, $datas = NULL)
  {
    $oDb = self::getDb();
    $oDb->db_query($sql, $datas);
    return array('table' => $oDb->res, 'rowCount' => $oDb->rowCount);
  }

  //-----------------------------------------------------------------------//
  // METHODES
  //-----------------------------------------------------------------------//

  public static function showTables()
  {
    $sql = "show tables;";
    return self::db_all($sql);
  }

  public static function showColumns($tbl = null)
  {
    $tbl = $tbl ?? static::$tbl;
    if (!$tbl) return null;
    $sql = "DESCRIBE $tbl";
    return self::db_all($sql);
  }

  public static function dropTable($tbl = null)
  {
    $tbl = $tbl ?? static::$tbl;
    if (!$tbl) return null;
    $sql = "DROP TABLE IF EXISTS $tbl";
    return self::db_exec($sql);
  }

  /**
   * Retourne les clés primaires, uniques et indexées d'une table
   * @param mixed $tbl 
   * @return array|false 
   */
  public static function showKeys($tbl = null)
  {
    $tbl = $tbl ?? static::$tbl;
    if (!$tbl) return null;
    $sql = "SHOW KEYS FROM $tbl";
    return self::db_all($sql);
  }

  public static function lockTable()
  {
    if (!static::$tbl)
      return null;
    $tbl = static::$tbl;
    // lock table to prevent other sessions from modifying the data and thus preserving data integrity
    $sql = 'LOCK TABLE `' . $tbl . '` WRITE';
    self::db_exec($sql);
  }

  public static function unLockTables()
  {
    // lock table to prevent other sessions from modifying the data and thus preserving data integrity
    $sql = 'UNLOCK TABLES';
    self::db_exec($sql);
  }

  public static function add($arrDatas = [], $returnId = true)
  {
    if (!static::$tbl)
      return null;
    $tbl = static::$tbl;
    // if (static::$pkey && !isset($arrDatas[static::$pkey])){
    //   $arrDatas[static::$pkey] = uniqid();
    // }

    $leTableauAssociatifNomDuchamValeur = [];

    $tmp = [];
    $tmp_placeholders = [];
    $count = 1;
    foreach ($arrDatas as $K => $V) {
      $placeholder = ':val' . $count . "_";
      $leTableauAssociatifNomDuchamValeur[$placeholder] = $V;
      $tmp[] = self::quoteIdentifier($K);
      $tmp_placeholders[] = $placeholder;
      $count++;
    }

    $lescolonnes = join(',', $tmp);
    $lesChampsNommes = join(',', $tmp_placeholders);

    $sql = "INSERT INTO `$tbl`
          ($lescolonnes)
          VALUES ($lesChampsNommes)";
    $datas = $leTableauAssociatifNomDuchamValeur;

    //return ['sql' => $sql, 'datas' => $datas];
    return self::db_insert($sql, $datas, $returnId);
  }

  /**
   * Créé et retourne l'entrée nouvellement créée
   * @param array $arrDatas 
   * @return array 
   */
  public static function create($arrDatas = [])
  {
    $new = self::add($arrDatas, true);
    if (!$new) return null;
    return self::getBy($new);
  }

  /**
   *
   * fieldsValues :  nomchamp1 = :nomchamp1 , nomchamp2 =:nomchamp2 ...etc...
   */
  public static function updateBy($whereValue = null, $arrDatas = [], $whereField = null)
  {
    if (!static::$tbl)
      return null;
    $tbl = static::$tbl;
    $whereField = self::quoteIdentifier($whereField ? $whereField : static::$pkey);
    $leTableauAssociatifNomDuchamValeur = [];

    $tmp = [];
    $count = 1;
    foreach ($arrDatas as $K => $V) {
      if ($K != static::$pkey) {
        $placeholder = ':val' . $count . "_";
        $leTableauAssociatifNomDuchamValeur[$placeholder] = $V;
        $tmp[] = self::quoteIdentifier($K) . ' = ' . $placeholder;
        $count++;
      }
    }
    $fieldsValues = join(',', $tmp);

    $sql = "UPDATE `$tbl`
            SET $fieldsValues
            WHERE $whereField = :whereValue ";
    $datas = $leTableauAssociatifNomDuchamValeur;
    //return [$sql, $datas];
    $datas[":whereValue"] = $whereValue;
    return self::db_exec($sql, $datas);
  }

  public static function removeBy($whereValue = null, $whereField = null)
  {
    if (!static::$tbl)
      return null;
    $tbl = static::$tbl;
    $whereField = self::quoteIdentifier($whereField ? $whereField : static::$pkey);
    $sql = "DELETE FROM `$tbl`
            WHERE $whereField = :whereValue";
    $datas = [':whereValue' => $whereValue];
    $oDb = new self;
    return $oDb->db_exec($sql, $datas);
  }

  /**
   * GetList avec Fetch Unique
   * @param array $orderBy
   * @param array $arrlimit
   * @return void
   */
  public static function getListUnique($orderBy = [], $arrlimit = [])
  {
    return static::getList($orderBy, $arrlimit, PDO::FETCH_UNIQUE);
  }

  /**
   * Retourne toutes les données de tables avec toutes les colonnes
   * @param array $orderBy
   * @param array $arrlimit
   * @param int $fetchmode
   * @return array
   */
  public static function getList($orderBy = [], $arrlimit = [], $fetchmode = PDO::FETCH_ASSOC)
  {
    if (!static::$tbl) return null;
    $orderfield = "TBL." . self::quoteIdentifier(($orderBy[0] ?? null) ?: static::$pkey);
    $query = new SQLBuilder([
      "colonnes" => ["TBL.*"],
      "table" => static::$tbl . " TBL",
      "limit" => ((int)($arrlimit[0] ?? null)) ?: null,
      "offset" => ((int)($arrlimit[1] ?? null)) ?: null,
      "orderBy" => [$orderfield . " " . (($orderBy[1] ?? null) ?: 'ASC')],
    ]);
    if ($fetchmode === PDO::FETCH_UNIQUE) {
      $query->addColonne(static::$pkey);
    }

    return self::db_all($query->getSql(), null, $fetchmode);
  }

  public static function getBy($value = '', $field = null, $orderBy = [], $arrlimit = [])
  {
    if (!static::$tbl) return null;
    $field = ($field ?: static::$pkey);
    $field = "TBL." . $field;
    $orderfield = ($orderBy[0] ?? null) ?: static::$pkey;
    $orderfield = "TBL." . $orderfield;
    $query = new SQLBuilder([
      "colonnes" => ["TBL.*"],
      "table" => static::$tbl . " TBL",
      "limit" => ((int)($arrlimit[0] ?? null)) ?: null,
      "offset" => ((int)($arrlimit[1] ?? null)) ?: null,
      "orderBy" => [$orderfield . " " . (($orderBy[1] ?? null) ?: 'ASC')],
      "conditions" => [$field . " = ?"],
      "values" => [$value],
    ]);
    return self::db_one($query->getSql(), $query->getValues());
  }

  /**
   *
   * @param mixed|mixed[] $value
   * @param mixed|null $field
   * @param array $orderBy
   * @param array $arrlimit
   * @param int $fetchmode
   * @return mixed
   */
  public static function getAllBy($value = '', $field = null, $orderBy = [], $arrlimit = [], $fetchmode = PDO::FETCH_ASSOC)
  {
    if (!static::$tbl) return null;
    $field = "TBL." . self::quoteIdentifier($field ?: static::$pkey);
    $orderfield = "TBL." . self::quoteIdentifier(($orderBy[0] ?? null) ?: static::$pkey);
    $query = new SQLBuilder([
      "colonnes" => ["TBL.*"],
      "table" => static::$tbl . " TBL",
      "limit" => ((int)($arrlimit[0] ?? null)) ?: null,
      "offset" => ((int)($arrlimit[1] ?? null)) ?: null,
      "orderBy" => [$orderfield . " " . (($orderBy[1] ?? null) ?: 'ASC')],
      "conditions" => [$field . " = ?"],
      "values" => [$value],
    ]);
    if ($fetchmode === PDO::FETCH_UNIQUE) $query->addColonne("TBL." . self::quoteIdentifier(static::$pkey));
    if (is_array($value)) {
      $value = array_values($value);
      $query->setValues($value);
      $query->setConditions([$field . " IN (" . implode(",", array_fill(0, count($value), "?")) . ")"]);
    }

    return self::db_all($query->getSql(), $query->getValues(), $fetchmode);
  }

  public static function getAllByUnique($value = '', $field = null, $orderBy = [], $arrlimit = [])
  {
    return static::getAllBy($value, $field, $orderBy, $arrlimit, PDO::FETCH_UNIQUE);
  }

  /**
   * Récupère un tableau de tableaux regroupés par le champ donné
   * @param string $field
   * @param array $orderBy
   * @param array $arrlimit
   * @return array
   */
  public static function getListGroup($field, $orderBy = [], $arrlimit = [])
  {
    if (!static::$tbl) return null;
    $field = "TBL." . self::quoteIdentifier($field);
    $orderfield = "TBL." . self::quoteIdentifier(($orderBy[0] ?? null) ?: static::$pkey);
    $query = new SQLBuilder([
      "colonnes" => [$field, "TBL.*", $field],
      "table" => static::$tbl . " TBL",
      "limit" => ((int)($arrlimit[0] ?? null)) ?: null,
      "offset" => ((int)($arrlimit[1] ?? null)) ?: null,
      "orderBy" => [$orderfield . " " . (($orderBy[1] ?? null) ?: 'ASC')],
    ]);

    return self::db_all_group($query->getSql());
  }

  protected static function quoteIdentifier($str)
  {
    $str = trim($str);
    if ($str == "*") {
      return $str;
    }
    return '`' . preg_replace('/`/', '``', $str) . '`';
  }
  protected static function qi($str)
  {
    return self::quoteIdentifier($str);
  }
  public static function al($str, $suffix = null)
  {
    return implode(".", array_filter([(implode("_", array_filter([static::ALIAS, $suffix]))) ?: ("`" . static::$tbl . "`"), self::qi($str)]));
  }
  public static function tbl($suffix = null)
  {
    return implode(" ", array_filter([static::$tbl, (implode("_", array_filter([static::ALIAS, $suffix])))]));
  }
}
