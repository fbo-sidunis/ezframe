<?php

/**
 * utilisateurs du site
 * */

namespace Core\Db;

use Core\Db\Sessions;
use Core\SQLBuilder;

class User extends \Core\Db {

  public static $tbl = 'user';
  public static $pkey = 'id';
  protected $id = null;
  protected $actif = null;
  protected $nom = null;
  protected $prenom = null;
  protected $mail = null;
  protected $pass = null;
  protected $lastupdate_by = null;
  protected $date_inscription = null;
  protected $lastcnx = null;
  public static $searchCols = [
    "nom",
    "prenom",
    "mail",
    "identifiant",
  ];

  /**
   * Retourne une liste d'utilisateurs
   * @param array $orderBy 
   * @param array $arrlimit 
   * @return mixed 
   */
  public static function get($orderBy = [], $arrlimit = []) {
    $tbl = self::$tbl;
    $sql = "SELECT U.*
                  ,TIMESTAMPDIFF(SECOND, U.lastcnx,now()) as lastcnx_time
            FROM $tbl U
            ORDER BY prenom, nom";

    return parent::db_all($sql);
  }

  /**
   * Raffraichis la dernière connexion d'un utilisateur
   * @param int|string $userid 
   * @return array 
   */
  public static function setLastCnx($userid) {
    $tbl = self::$tbl;
    $sql = "UPDATE $tbl
               SET lastcnx = now()
               WHERE id = :id
              ";
    $datas = array(':id' => $userid);
    return parent::db_exec($sql, $datas);
  }

  /**
   * Renouvelle le Mot de passe
   * @param string $email 
   * @return array 
   */
  public static function renewPass($email) {
    $user = parent::getBy($email, 'mail');
    $result = [];
    $pass = self::randomPassword();
    $result['UPDATE_PASS'] = self::updatePass($pass, $user['id']);
    $result['NEW_PASS'] = $pass;
    return $result;
  }

  /**
   * Génére un mot de passe aléatoire
   * @return string 
   */
  private static function randomPassword() {
    $alphabet = '!@#abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = []; //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 10; $i++) {
      $n = rand(0, $alphaLength);
      $pass[] = $alphabet[$n];
    }
    return implode("", $pass); //turn the array into a string
  }

  /**
   * Met à jour le mot de passe d'un utilisateur
   * @param string $pass
   * @param string $userid
   * @return mixed
   */
  public static function updatePass($pass, $userid) {
    $tbl = self::$tbl;
    $password = password_hash($pass, PASSWORD_DEFAULT);
    $sql = "UPDATE $tbl
              SET pass = :pass
              WHERE id=:id ";
    $datas = array(':pass' => $password, ':id' => $userid);
    return parent::db_exec($sql, $datas);
  }

  /**
   * Connexion
   * @param string $login
   * @param string $pass
   * @return boolean
   */
  public static function login($login, $pass) {
    $user = self::getBy($login, 'mail');
    if (empty($user) or ($user["actif"] ?? "N") != "Y")
      return errorResponse([], "Utilisateur/Mot de passe invalide");
    if (password_verify($pass, $user['pass'])) {
      self::setLastCnx($user['id']); //on met à jour la dernière connexion
      return $user;
    } else {
      return false;
    }
  }

  /**
   * Connexion
   * @param string $login
   * @param string $pass
   * @return boolean
   */
  public static function getBySession($token) {
    $query = new SQLBuilder([
      "colonnes" => ["U.*"],
      "table" => self::$tbl." U",
      "joinTables" => [
        Sessions::$tbl." S" => "S.id_user = U.".self::$pkey,
      ],
      "conditions" => [
        "S.expiration > NOW()",
        "S.token = :token",
      ],
      "values" => [
        ":token" => $token,
      ]
    ]);
    return self::db_one($query->getSql(),$query->getValues());
  }

  /**
   * Met à jour l'utilisateur
   * @param int|string $userid 
   * @param string $nom 
   * @param string $prenom 
   * @param string $mail 
   * @param string|null $pass 
   * @return array 
   */
  public static function updateUser($userid, $nom, $prenom, $mail, $pass = null, $actif = 'Y') {
    $tbl = self::$tbl;
    $res = [];
    $sql = "UPDATE $tbl
            SET nom =:nom
               ,prenom = :prenom
               ,mail = :mail
               ,actif= :actif
           WHERE id=:id ";
    $datas = array(':id' => $userid, ':nom' => $nom, ':prenom' => $prenom, ':mail' => $mail, ':actif' => $actif);
    $res['USER'] = parent::db_exec($sql, $datas);
    if ($res['USER'] && !empty($pass)) {
      $res['PASSWORD'] = $tbl = self::updatePass($pass, $userid);
    }

    return $res;
  }

  /**
   * Vérifie le mot de passe à partir d'un mail fourni
   * @param string $mail
   * @param string $mdp
   * @return mixed
   */
  public static function getByMailPass($mail, $mdp) {
    $user = self::getBy($mail, 'mail');
    //$mdp = password_hash($pass, PASSWORD_DEFAULT);
    if (!empty($user)) {
      if (password_verify($mdp, $user['pass'])) {
        return $user;
      } else {
        return false;
      }
    }
  }

  /**
   * Création User depuis le BO ADMIN
   * @param string $nom
   * @param string $prenom
   * @param string $mail
   * @param string $password
   * @param int|string $userid
   * @return mixed
   */
  public static function addUser($nom, $prenom, $mail, $password = NULL, $userid = 0) {
    $tbl = self::$tbl;
    $sql = "INSERT INTO $tbl (nom,prenom,mail,pass,date_inscription,lastupdate_by,actif)
            VALUES(:nom,:prenom,:mail,:pass,now(),:userid,'Y')";
    $pass = password_hash($password, PASSWORD_DEFAULT);
    $datas = array(':nom' => $nom, ':prenom' => $prenom, ':mail' => $mail, ':pass' => $pass, ':userid' => $userid);
    return parent::db_insert($sql, $datas, true);
  }

  public static function delete($userid) {
    return parent::removeBy($userid);
  }

//------------ FIN CLASS ------------------//
}
