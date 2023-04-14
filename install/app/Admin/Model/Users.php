<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Admin\Model;

use Core\Db\UserRole;

class Users extends \Core\Db\User {

  public static $tbl = 'user';

  /**
   *
   * @return array
   */
  public static function getUser() {
    $tbl = self::$tbl;
    $sql = "SELECT U.*
            ,DATE_FORMAT(U.lastcnx,'%d/%m/%Y %H:%i') as date
            FROM $tbl U";
    return self::db_all($sql);
  }

  /**
   *
   * @param string $q
   * @return array
   */
  public static function getBy_Txt($q){
    $tbl = self::$tbl;
    $strwhere = !empty($q) ? " WHERE mail LIKE '%$q%'  OR nom LIKE '%$q%'  OR prenom LIKE '%$q%' " : '';
    $sql = "SELECT U.*
              ,DATE_FORMAT(U.lastcnx,'%d/%m/%Y %H:%i') as date
            FROM $tbl U
            $strwhere
            ORDER BY id ASC";
    return self::db_all($sql);
  }

  /**
   *
   * @param string $name
   * @param string $firstname
   * @param string $mail
   * @param string $pwd
   * @return type
   */
  public static function insertNewUser($name, $firstname, $mail, $pwd, $lastupdate_by){
   return self::addUser($name, $firstname, $mail, $pwd, $lastupdate_by );
  }

  /**
   *
   * @param int|string $id_user
   * @return array
   */
  public static function removeRoleUser($id_user){
    $tbl_roles_user = UserRole::$tbl;
    $sql = "DELETE FROM $tbl_roles_user
            WHERE id_user=:id_user";
    $datas=array(':id_user'=>$id_user);
    return self::db_exec($sql, $datas);

  }

  /**
   *
   * @param int|string $id_user
   * @param array $arrRoles
   * @return array
   */
  public static function addRolesUser($id_user,$arrRoles=[],$lastupdate_by = null){
    $tbl_roles_user = UserRole::$tbl;
    $result = [];
    $sql = "INSERT INTO $tbl_roles_user (id_user, `role`, lastupdate_by)
             VALUES(:id_user,:role,:lastupdate_by)";
    foreach($arrRoles as $role){
     $datas=array(':id_user'=>$id_user,':role'=>$role,':lastupdate_by'=>$lastupdate_by);
     $result[] = self::db_insert($sql, $datas);
    }
    return $result;
  }

  /**
   *
   * @param int|string $user_id
   * @param string $etat
   * @return array
   */
  public static function activate($user_id,$etat='N'){
    $tbl = self::$tbl;
    $sql = "UPDATE $tbl
            SET actif = :etat
            WHERE id=:user_id";
    $datas=array(':user_id'=>$user_id,':etat'=>$etat);
    return self::db_exec($sql, $datas);
  }

  /**
   *
   * @param int|string $user_id
   * @return null|array
   */
  public static function deleteUser($user_id){
    return self::delete($user_id);
  }

  /**
   *
   * @param int|string $user_id
   * @return array
   */
  public static function getUserById($user_id){
    return self::getBy($user_id);
  }

}
