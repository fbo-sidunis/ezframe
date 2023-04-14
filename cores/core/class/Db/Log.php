<?php

/**
 * log.class.php
 *
 * */
namespace Core\Db;
class Log extends \Core\Db {

  public static $tbl = 'log';
  protected $id = null;
  protected $date = null;
  protected $title = null;
  protected $txt = null;
  protected $type = null;
  protected $sess = null;

  function __construct() {
    // parent::__construct();
  }

  public function set_id($pArg = NULL) {
    $this->id = $pArg;
  }

  public function set_date($pArg = NULL) {
    $this->date = $pArg;
  }

  public function set_title($pArg = NULL) {
    $this->title = $pArg;
  }

  public function set_txt($pArg = NULL) {
    $this->txt = $pArg;
  }

  public function set_type($pArg = NULL) {
    $this->type = $pArg;
  }

  public function set_sess($pArg = NULL) {
    $this->sess = $pArg;
  }

  public function get_id() {
    return (integer) $this->id;
  }

  public function get_date() {
    return (string) $this->date;
  }

  public function get_title() {
    return (string) $this->title;
  }

  public function get_txt() {
    return (string) $this->txt;
  }

  public function get_type() {
    return (string) $this->type;
  }

  public function get_sess() {
    return (string) $this->sess;
  }

  public static function insertLog($text, $title = '', $type = 'DEBUG') {
    $tbl = self::$tbl;
    $sql = "INSERT INTO $tbl (title,txt,`type`,`date`)
            VALUES (:title,:text,:type,now())";
    $datas = array(':title' => $title, ':text' => $text, ':type' => $type);
    return parent::db_insert($sql, $datas);
  }

//------------ FIN CLASS ------------------//
}
