<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Admin\Model;
class Roles extends \Core\Db {

  public static $tbl = 'config_role';
  public static $pkey = 'code';

  public static function getAll() {
    $tbl = self::$tbl;
    $sql = "SELECT R.*
            FROM $tbl R";
    return parent::db_all($sql);
  }


}