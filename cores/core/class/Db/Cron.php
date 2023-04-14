<?php

namespace Core\Db;

use Core\SQLBuilder;
/**
 * Description of cron
 *
 * @author jreynet
 */
class Cron extends \Core\Db {

  public static $tbl = 'cron_queue';
  public static $pkey = 'id';
	protected static $cols = [
    "id" => "BIGINT NOT NULL AUTO_INCREMENT",
    "script" => "VARCHAR(2048) NOT NULL COMMENT 'full path script' COLLATE 'utf8mb4_general_ci'",
    "params" => "VARCHAR(1024) NULL DEFAULT NULL COMMENT 'liste parametres separes par un espace' COLLATE 'utf8mb4_general_ci'",
    "in_progress" => "INT NULL DEFAULT NULL COMMENT '1 : en cours'",
    "start_date" => "DATETIME NULL DEFAULT NULL",
    "end_date" => "DATETIME NULL DEFAULT NULL",
    "log" => "TEXT NULL COLLATE 'utf8mb4_general_ci'",
    "priority" => "ENUM('Y','N') NULL DEFAULT 'N' COLLATE 'utf8mb4_general_ci'",
    "lastupdate_date" => "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP",
	];
	protected static $indexes = [
	];
	protected static $options = [
		"COMMENT='table de queue des taches cron'",
		"COLLATE='utf8mb4_general_ci'",
		"ENGINE=MyISAM",
	];

  public static function addQueue($script, $params = null) {
    $sql = "INSERT INTO " . self::$tbl . "(script,params) VALUES (:script,:params)";
    $datas = [':script' => $script, ':params' => trim($params)];
    return self::db_Insert($sql, $datas, false);
  }

  public static function getQueue($notInProgress = FALSE,$priority = FALSE) {
    $query = new SQLBuilder([
      "colonnes" => ["*"],
      "table" => self::tbl(),
    ]);
    if ($notInProgress) {
      $query->addCondition("(".self::al("in_progress")." IS NULL OR ".self::al("in_progress")." = '0')");
    }
    if ($priority) {
      $query->addCondition(self::al("priority")." = 'Y'");
    }
    return self::db_all($query->getSQL(), $query->getValues());
  }

  public static function getActiveTask() {
    $sql = "SELECT * FROM " . self::$tbl;
    $sql .= " WHERE in_progress=1";
    return self::db_All($sql);
  }

  public static function setStatus($id, $in_progress = 1) {
    $sql = "UPDATE " . self::$tbl . " SET in_progress=:in_progress WHERE id=:id";
    $datas = [':in_progress' => $in_progress, ':id' => $id];
    return self::db_Exec($sql, $datas);
  }

  public static function setStartDate($id, $date) {
    $sql = "UPDATE " . self::$tbl . " SET start_date=:date WHERE id=:id";
    $datas = [':date' => $date, ':id' => $id];
    return self::db_Exec($sql, $datas);
  }

  public static function setEndDate($id, $date) {
    $sql = "UPDATE " . self::$tbl . " SET end_date=:date WHERE id=:id";
    $datas = [':date' => $date, ':id' => $id];
    return self::db_Exec($sql, $datas);
  }

  public static function setLog($id, $log) {
    $sql = "UPDATE " . self::$tbl . " SET log=:log WHERE id=:id";
    $datas = [':log' => $log, ':id' => $id];
    return self::db_Exec($sql, $datas);
  }

  public static function delete_cronqueu(){
    $sql='TRUNCATE TABLE ' . self::$tbl ;
    return self::db_exec($sql, null);
  }

  public static function addTask($script,$params = [],$priority = 'N') {
    $script = trim($script);
    $params = implode(" ",array_map(function($k,$v){
      return "--$k=\"$v\"";
    },array_keys($params),$params));
    return self::add([
      'script' => $script,
      'params' => $params,
      'priority' => $priority,
    ]);
  }

}
