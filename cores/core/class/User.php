<?php
namespace Core;

use Core\Db\Sessions;
use Core\Db\User as DbUser;
use Core\Db\UserRole;

class User
{
  private const TOKEN_COOKIE_NAME = "login_token";
  protected $id = null;
  protected $datas = [];
  protected $roles = [];
  private static $expirationTime = null;
  private static $expirationDate = null;

  function __construct($parameters = []){
    foreach (($parameters ?? []) as $parameter=>$value){
      $setter = "set".ucfirst($parameter);
      if (method_exists($this,$setter)) $this->$setter($value);
    }
    $this->putInSession();
    if (self::getCookie()){
      $this->refreshSession();
    }else{
      $this->createSession();
    }
  }

  private static function createByDatas($datas = []){
    if (!$datas) return null;
    $idUser = $datas[DbUser::$pkey];
    $user = new self([
      "id" => $idUser,
      "datas" => $datas,
      "roles" => UserRole::getRoles($idUser),
    ]);
    return $user;
  }

  public static function init($id){
    return self::createByDatas(DbUser::getBy($id));
  }

  public static function getBySession($token){
    return self::createByDatas(DbUser::getBySession($token));
  }

  /**
   * Get the value of datas
   */ 
  public function getDatas()
  {
    return $this->datas;
  }

  /**
   * Get the value of datas
   */ 
  public function getData($key)
  {
    return $this->datas[$key] ?? null;
  }

  /**
   * Get the value of datas
   */ 
  public function setData($key,$value)
  {
    $this->datas[$key] = $value;
    return $this;
  }

  /**
   * Set the value of datas
   *
   * @return  self
   */ 
  protected function setDatas($datas)
  {
    $this->datas = $datas;

    return $this;
  }

  /**
   * Get the value of id
   */ 
  public function getId()
  {
    return $this->id;
  }

  /**
   * Get the value of roles
   */ 
  public function getRoles()
  {
    return $this->roles;
  }

  /**
   * Set the value of roles
   *
   * @return  self
   */ 
  protected function setRoles($roles)
  {
    $this->roles = $roles;

    return $this;
  }


  public function hasRole($role = null)
  {
    if ($role === null) return true;
    return in_array($role,$this->getRoles());
  }

 public function hasOneRole($roles = [])
  {
    if ($roles === null) return true;
    if ($roles === []) return true;
    if (!empty($this->getRoles()) && is_array($roles) && is_array($this->getRoles())) {
      return !empty(array_intersect($roles ?: [], $this->getRoles()));
    } else {
      return false;
    }
  }

  public function hasRoles($roles = [])
  {
    if (empty($roles)) return true;
    if (!empty($this->getRoles()) && is_array($roles) && is_array($this->getRoles())) {
      return count(array_intersect($roles ?: [], $this->getRoles())) === count($roles);
    } else {
      return false;
    }
  }


  /**
   * Set the value of id
   *
   * @return  self
   */ 
  protected function setId($id)
  {
    $this->id = $id;

    return $this;
  }

  protected function createSession(){
    Sessions::clean();
    $token = uniqtoken();
    self::setCookie($token);
    Sessions::add([
      "expiration" => self::getExpirationDate(),
      "id_user" => $this->getId(),
      "token" => $token,
    ]);
  }
  protected function putInSession(){
    $_SESSION['USER']['id'] = $this->getId();
    $_SESSION['USER']['login'] = $this->getData("login");
    $_SESSION['is_logged'] = true;
  }

  protected function refreshSession(){
    self::setCookie(self::getCookie());
    Sessions::updateBy(self::getCookie(),[
      "expiration" => self::getExpirationDate(),
    ],"token");
  }

  public static function clearSession(){
    $_SESSION["USER"] = null;
    unset($_SESSION["USER"]);
    $_SESSION['is_logged'] = false;
    Sessions::removeBy(self::getCookie(),'token');
    self::removeCookie();
    session_destroy();
  }

  public static function retreive(){
    $token = self::getCookie();
    return $token ? self::getBySession($token) : null;
  }

  private static function getCookie(){
    return $_COOKIE[self::TOKEN_COOKIE_NAME] ?? null;
  }
  private static function setCookie($token) {

    return setcookie(self::TOKEN_COOKIE_NAME, $token, [
      "expires" => self::getExpirationTime(),
      "path" => ROOT_URL,
      "domain" => $_SERVER["HTTP_HOST"],
      "secure" => true,
      "httponly" => true,
      "samesite" => "Lax",
    ]);
  }

  private static function removeCookie() {
    return setcookie(self::TOKEN_COOKIE_NAME, "", [
      "expires" => -1,
      "path" => ROOT_URL,
      "domain" => $_SERVER["HTTP_HOST"],
      "secure" => true,
      "httponly" => true,
      "samesite" => "Lax",
    ]);
  }
  private static function getExpirationTime(){
    self::$expirationTime = self::$expirationTime ?? strtotime(getConfig("expiration_session"));
    return self::$expirationTime;
  }
  private static function getExpirationDate(){
    self::$expirationDate = self::$expirationDate ?? date("Y-m-d H:i:s",self::getExpirationTime());
    return self::$expirationDate;
  }
}
