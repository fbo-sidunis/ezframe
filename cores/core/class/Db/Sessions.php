<?php
namespace Core\Db;
class Sessions extends \Core\Db {

  public static $tbl = 'sessions';
  public static $pkey = 'id';

  public static function clean(){
    $tbl = self::$tbl;
    $sql = 
      "DELETE FROM $tbl
      WHERE expiration < NOW()
    ";
    return self::db_exec($sql);
  }

}

?>