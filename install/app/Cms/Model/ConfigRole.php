<?php

/**
 * ConfigRole.php
 * 
 * */

namespace App\Cms\Model;

class ConfigRole extends \Core\Db {

  public static $tbl = 'config_role';
  public static $pkey = 'id';

  //--------------------------------------------------------
  // FONCTIONS GENERIQUES
  //--------------------------------------------------------

  public static function getAll($orderBy = array(), $arrlimit = array()) {
    return parent::getList($orderBy, $arrlimit);
  }

  //--------------------------------------------------------
  // FONCTIONS
  //--------------------------------------------------------


  //------------ FIN CLASS ------------------//
}
