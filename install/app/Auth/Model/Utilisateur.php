<?php
namespace App\Auth\Model;
class Utilisateur extends \Core\Db {

  public static $tbl = 'mob_utilisateur';
  public static $tbl_article = 'article_extranet';
  public static $tbl_historique = 'mob_histo_login';
  protected static $field_login = 'login';
  protected static $field_pass = 'password';
  protected static $field_id = 'id';
  protected static $field_mail = 'mail';
  protected static $field_date_parution = 'date_parution';
  protected static $field_titre = 'titre';
  protected static $field_texte = 'texte';
  protected static $field_image = 'image';


  function __construct() {
  }

  /**
   * Récupère le mail et le mot de passe dans la table.
   * @param str $mail
   * @param str $mdp
   * @return array
   */
  public static function get($mail, $mdp) {
    $tbl = self::$tbl;
    $field_pass = self::$field_pass;
    $field_login = self::$field_login;
    $datas = array(':email' => $mail, ':pass' => $mdp);
    $sql = "SELECT *
			   FROM  $tbl
			   WHERE $field_login = :email AND $field_pass = :pass ";
    $res = parent::db_one($sql, $datas);
    return $res;
  }

    /**
   * Créé et retourne l'entrée nouvellement créée
   * @param array $arrDatas 
   * @return array 
   */
  public static function create($arrDatas = []){
    $pass = $arrDatas["password"] ?? null;
    if ($pass) return null;
    $arrDatas["password"] = password_hash($pass, PASSWORD_DEFAULT);
    return parent::create($arrDatas);
  }

  /**
   * Récupère les informations de la table en fonction de l'id du client.
   * @param int $id
   * @return array
   */
  public static function getbyid($id) {
    $tbl = self::$tbl;
    $field_id = self::$field_id;
    $datas = array(':id' => $id);
    $sql = "SELECT *
            FROM $tbl
            WHERE $field_id = :id ";
    $res = parent::db_one($sql, $datas);
    return $res;
  }

  /**
   * Récupère une liste de toute la table.
   * @return array
   */
  public static function liste() {
    $tbl = self::$tbl;
    $sql = "SELECT *
            FROM $tbl
            ORDER BY id";
    $res = parent::db_all($sql);
    return $res;
  }

  public static function login($login = '', $pwd = '') {
    $tbl = self::$tbl;
    $field_id = self::$field_id;
    $field_login = self::$field_login;
    $field_pass = self::$field_pass;
    $field_mail = self::$field_mail;
    $datas = array(':login'=> $login);
    $sql = "SELECT $field_id
                  ,$field_login
                  ,$field_pass
                  ,$field_mail
          FROM $tbl
          WHERE $field_login = :login";
    $res = parent::db_one($sql,$datas);
    if (password_verify($pwd, $res[$field_pass])) {
        $result = $res['id'];
    } else  {
       $result = NULL;
    }
      return $result;
  }
  public static function histoLogin($login,$date,$ip,$navigateur,$statut=0){
    $tbl_historique = self::$tbl_historique;
    $datas = array(':login'=> $login
                  ,':date'=> $date
                  ,':ip'=> $ip
                  ,':navigateur'=>$navigateur
                  ,':statut'=>$statut);
    $sql = "INSERT INTO $tbl_historique (login, date, ip, navigateur, statut)
                        VALUES (:login,:date, :ip, :navigateur,:statut)";
    return parent::db_insert($sql,$datas);
  }

  public static function recupArticles(){
    $field_date_parution = self::$field_date_parution;
    $field_id = self::$field_id;
    $field_titre = self::$field_titre;
    $field_texte = self::$field_texte;
    $field_image = self::$field_image;
    $tbl_article = self::$tbl_article;
    $sql = "SELECT $field_id
                   $field_date_parution
                   $field_titre
                   $field_texte
                   $field_image
            FROM $tbl_article
            GROUP BY $field_id DESC
            LIMIT 4";
   /*  echo ("  <ul>
        <li>".$."</li>
        <li>".$."</li>
        <li>".$."</li>
        <li>".$."</li>
      </ul> " );
     */

    return parent::db_all($sql);

  }

  /**  -----------------------------------------Fin Classe---------------------------------- * */
}

?>