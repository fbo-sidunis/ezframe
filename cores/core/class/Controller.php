<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Core;

use Core\Common\Site;

set_time_limit(1000);

/**
 * Description of Controller
 *
 * @author jreynet
 */
class Controller {

  /**
   * L'objet twig Environnement
   * @var \Core\User|null
   */
  protected $user = null;
  /**
   * L'objet twig Environnement
   * @var \Twig\Environment|null
   */
  protected $twig = null;
  /**
   * L'objet twig loader
   * @var \Twig\Loader\FilesystemLoader|null
   */
  protected $loader = null;
  /**
   * Les roles autorisés, mettre à null si tout le monde est autorisé, [] si seul les utilisateurs authentifiés peuvent y accéder
   * @var array|null
   */
  protected $authorized = null;
  /**
   * Le template actuel
   * @var string|null
   */
  protected $template = null;
  /**
   * L'objet route
   * @var \Core\Route|null
   */
  protected $route = null;
  /**
   * La route de fallback si on n'a pas les droits pour accéder à la page actuelle
   * @var string
   */
  protected $fallback = "login";
  /**
   * Les datas par défaut pour la généréation du twig
   * @var array
   */
  protected $datas = [];

  /**
   * Constructeur
   * @param mixed|null $twigEnv 
   * @param mixed|null $loader 
   * @param mixed|null $route 
   * @param array $datas 
   * @return void 
   */
  function __construct($datas = []) {
    $this->user = User::retreive();
    $datas["USER"] = $this->user;
    $twigEnv = Site::getTwigEnvironnment();
    if ($twigEnv) {
      $this->twig = $twigEnv;
      $this->loader = $this->twig->getLoader();
    }
    $routing = Site::getRouting();
    if ($routing) {
      $this->route = $routing;
      $this->template = $this->route->vars["template"] ?? $this->route->vars["t"] ?? $this->template;
      $this->authorized = $this->route->vars["authorized"] ?? $this->route->vars["access"] ?? $this->route->vars["a"] ?? $this->authorized;
      $this->fallback = $this->route->vars["fallback"] ?? $this->fallback;
    }
    if (!$this->datas) {
      $this->datas = $datas;
    }
    if ($this->authorized !== null && $this->authorized !== 0) {
      if ($this->fallback) {
        $this->checkAuthorization();
      } else {
        $this->checkAuthorizationJSON();
      }
    }
  }

  /**
   *
   * @param type $obj
   * @return type
   */
  public function getCurlResponseToArray($obj = NULL) {
    if (!empty($obj)) {
      if (is_array($obj) || is_object($obj)) {
        $arr = (array) $obj;
        if (is_object($arr)) {
          return !empty($arr->response) ? (array) $arr->response : (array) $arr;
        } else {
          return !empty($arr['response']) ? (array) $arr['response'] : (array) $arr;
        }
      } else {
        return $obj;
      }
    }
  }

  /**
   * Affichage de la page
   * @param type $template [chemin du template twig, se replius sur $this->template si null]
   * @return boolean
   */
  public function display($template = null, $datas = []) {
    $this->datas['template'][] = $this->template;
    if ($this->twig) {
      echo $this->twig->render($template ?: $this->template, $datas ?: $this->datas);
      return true;
    } else {
      return "Template or loader error ";
    }
  }

  protected function checkAccess($roles = null) {
    $roles = $roles ?? $this->authorized;
    return $this->user && $this->user->hasOneRole($roles);
  }

  /**
   * Vérifie l'accès de l'utilisateur et echo une réponse d'erreur en JSON si accès refusé
   * @param mixed|null $roles 
   * @return false|void 
   */
  protected function checkAuthorizationJSON($roles = null) {
    if (!$this->checkAccess($roles)) {
      return \errorResponse(["roles" => $roles ?? $this->authorized], "Non autorisé", 401);
    }
  }

  /**
   * Vérifie l'accès de l'utilisateur et le renvoie à la route fallback renseignée si accès refusé
   * @param mixed|null $roles [roles, se replie sur $this->roles si null]
   * @param mixed|null $route [fallback, se replie sur $this->fallback si null]
   * @param array $variables [variables de route de fallback si besoin est]
   * @return mixed 
   */
  protected function checkAuthorization($roles = null, $route = null, $variables = []) {
    if (!$this->checkAccess($roles)) {
      $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
      if (empty($_COOKIE["redirect_url"])){
        setcookie("redirect_url",$url,[
          "expires" => time()+1200,
          "path" => ROOT_URL,
          "domain" => $_SERVER["HTTP_HOST"],
          "secure" => true,
          "httponly" => true,
          "samesite" => "Lax",
        ]);
      }else if ($url == $_COOKIE["redirect_url"]){
        setcookie("redirect_url","",[
          "expires" => -1,
          "path" => ROOT_URL,
          "domain" => $_SERVER["HTTP_HOST"],
          "secure" => true,
          "httponly" => true,
          "samesite" => "Lax",
        ]);
      }
      return $this->route->redirect($route ?? $this->fallback, $variables);
    }else{
      setcookie("redirect_url","",[
        "expires" => -1,
        "path" => ROOT_URL,
        "domain" => $_SERVER["HTTP_HOST"],
        "secure" => true,
        "httponly" => true,
        "samesite" => "Lax",
      ]);
    }
  }

  protected function setData($name,$value){
    $this->datas[$name] = $value;
    return $this;
  }

  protected function getData($name){
    return $this->datas[$name] ?? null;
  }

//------------------ FIN CLASS /---------------------------//
}
