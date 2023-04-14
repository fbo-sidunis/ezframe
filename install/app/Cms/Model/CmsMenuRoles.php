<?php

/**
 * CmsMenuRoles.php
 * 
 * */

namespace App\Cms\Model;

use Core\SQLBuilder;
use PDO;

class CmsMenuRoles extends \Core\Db {

  public static $tbl = 'cms_menu_roles';
  public static $tbl_roles = 'config_role';
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

  public static function UpdateOrAddRolesMenu($R,$V,$id_menu){

    $exist = self::getRoleByIdMenu($R,$id_menu);

    if($exist){
      $res['UPDATE'] = parent::updateBy($exist['id'],[
          'c' => $V['c']
          ,'r' => $V['r']
          ,'u' => $V['u']
          ,'d' => $V['d']
      ]);      
    }else{
      $res['INSERT'] = parent::add([
        'id_menu_item' => $id_menu
        ,'code_role' => $R
        ,'c' => $V['c']
        ,'r' => $V['r']
        ,'u' => $V['u']
        ,'d' => $V['d']
      ]);
    }

    return $res;
  }

  public static function getRoleByIdMenu($code_role,$id_menu){
    $sql = "SELECT CR.*             
    FROM " . self::$tbl . " CR
    WHERE id_menu_item = :id_menu AND  code_role = :code_role ";

    $datas = [
      ':id_menu' => $id_menu
      ,':code_role' => $code_role
    ];

    return parent::db_one($sql,$datas);
  }

  public static function getRoleActif(){

    $sql = "SELECT *             
    FROM " . self::$tbl_roles . " CR
    LEFT JOIN " . self::$tbl . " R ON R.code_role = CR.code
    WHERE CR.actif = 'Y'";

    return parent::db_all($sql);
  }

  public static function getByIdsMenuGrouped($ids){
    $query = new SQLBuilder([
      "table" => self::$tbl." CMS_MR",
      "colonnes" => [
        "CMS_MR.id_menu_item",
        "CMS_MR.*"
      ],
    ]);
    $query->addCondition("CMS_MR.id_menu_item IN (".$query->generatePlaceholdersAndValues($ids,"id_menu").")");
    return self::db_all($query->getSql(),$query->getValues(),PDO::FETCH_GROUP);
  }


  //------------ FIN CLASS ------------------//
}
