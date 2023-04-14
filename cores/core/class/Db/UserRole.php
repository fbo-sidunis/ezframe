<?php

namespace Core\Db;

use Core\SQLBuilder;
use PDO;

class UserRole extends \Core\Db {

  public static $tbl = 'config_user_role';
  public static $pkey = 'id';
  protected static $tbl_user = 'user';
  protected $id = null;
  protected $id_user = null;
  protected $role = null;
  protected $lastupdate_date = null;
  protected $lastupdate_by = null;

  function __construct() {
    // parent::__construct();
  }

  public function set_id($pArg = NULL) {
    $this->id = $pArg;
  }

  public function set_id_user($pArg = NULL) {
    $this->id_user = $pArg;
  }

  public function set_role($pArg = NULL) {
    $this->role = $pArg;
  }

  public function set_lastupdate_date($pArg = NULL) {
    $this->lastupdate_date = $pArg;
  }

  public function set_lastupdate_by($pArg = NULL) {
    $this->lastupdate_by = $pArg;
  }

  public function get_id() {
    return (integer) $this->id;
  }

  public function get_id_user() {
    return (integer) $this->id_user;
  }

  public function get_role() {
    return (string) $this->role;
  }

  public function get_lastupdate_date() {
    return (string) $this->lastupdate_date;
  }

  public function get_lastupdate_by() {
    return (integer) $this->lastupdate_by;
  }

  public static function getRoles($idUser) {
    $query = new SQLBuilder([
      "colonnes" => ["UR.role"],
      "table" => self::$tbl." UR",
      "conditions" => ["UR.id_user = :id_user"],
      "values" => [":id_user" => $idUser]
    ]);
    return self::db_all($query->getSql(),$query->getValues(),PDO::FETCH_COLUMN) ?: [];
  }

  public static function userHasRole($user_id, $role) {
    $tbl = self::$tbl;
    if (!empty($role) && is_array($role)) {
      $strRole = join("','", $role);
      $strWhereRole = " AND role IN('" . $strRole . "');";
    } else if (!empty($role)){
      $strWhereRole = " AND role IN('" . $role . "');";
    }else{
      return true;
    }

    $sql = "SELECT * FROM $tbl
            WHERE id_user = :id_user
             $strWhereRole";
    $datas = array(':id_user' => $user_id);
    $res = parent::db_one($sql, $datas);
    return !empty($res) ? true : false;
  }

  /**
   *
   * @param type $role
   * @return type
   */
  public static function getUserByRole($role = []) {
    $tbl = self::$tbl;
    $tbl_user = self::$tbl_user;
    $strRole = join("','", $role);
    $strWhereRole = " role IN('" . $strRole . "');";
    $sql = "SELECT DISTINCT mail FROM
            $tbl T
            LEFT JOIN $tbl_user U ON U.id = T.id_user
            WHERE U.actif = 'Y' AND $strWhereRole";

    $res = parent::db_all($sql);
    return $res;
  }

//------------ FIN CLASS ------------------//
}
