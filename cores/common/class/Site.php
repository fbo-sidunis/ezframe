<?php

namespace Core\Common;

use Core\Start\Dump;
use Core\Start\Route;
use Core\Start\Twig;
use Monolog\ErrorHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Dotenv\Dotenv;

class Site {

  private static $siteConfigFilePath = 'config/site_config.json';
  /**
   * 
   * @var \Twig\Environment
   */
  private static $twigEnvironnement = null;
  /**
   * 
   * @var \Core\Route
   */
  private static $routing = null;
  /**
   * 
   * @var \Core\Translator
   */
  private static $translator = null;

  public static function getSiteConfig_(){
    return [
      "TEMPLATES" => TEMPLATES,
      "ENV" => ENV,
      "SITEDIR" => SITEDIR,
      "DEBUG" => DEBUG,
      "AUTODEBUG" => AUTODEBUG,
    ];
  }

  public static function getConfigFilePath(){
    return ROOT_DIR . self::$siteConfigFilePath;
  }
  public static function getTwigEnvironnment(){
    return self::$twigEnvironnement;
  }
  public static function getRouting(){
    return self::$routing;
  }
  public static function getTranslator(){
    return self::$translator;
  }
  public static function setTwigEnvironnment($twig){
    self::$twigEnvironnement = $twig;
  }
  public static function setRouting($routing){
    self::$routing = $routing;
  }
  public static function setTranslator($translator){
    self::$translator = $translator;
  }

  public static function init($rootDir){
    //Démarrage des sessions :
    date_default_timezone_set("Europe/Paris");
    session_start();

    //-------------------------------------------------------------//
    //affichage des erreurs PHP
    //-------------------------------------------------------------//
    error_reporting(E_ALL);
    ini_set('display_errors', TRUE);
    ini_set('display_startup_errors', TRUE);

    //-------------------------------------------------------------//
    // Constantes
    //-------------------------------------------------------------//
    if (!defined("ROOT_DIR")) define("ROOT_DIR",realpath($rootDir)."/");
    if (!defined("APP_DIR")) define("APP_DIR",ROOT_DIR."app/");
    if (!defined("FRAMEWORK_DIR")) define("FRAMEWORK_DIR",realpath(__DIR__."/../../../")."/");
    if (!defined("TEMPLATES")) define("TEMPLATES",getConfig('twig.templates'));
    if (!defined("SITEDIR")) define("SITEDIR",getConfig('siteDir'));
    if (!defined("SITE_TITLE")) define("SITE_TITLE",getConfig('title'));
    if (!defined("DEBUG")) define("DEBUG",getConfig('debug'));
    if (!defined("AUTODEBUG")) define("AUTODEBUG",getConfig('autodebug'));
    if (!defined("LIBRARY_URL")) define("LIBRARY_URL","/vendor/groupefbo/ezframe/");
    if (!defined("LIBRARY_DIR")) define("LIBRARY_DIR",ROOT_DIR."vendor/groupefbo/ezframe/");

    //-------------------------------------------------------------//
    // Initialisation des constantes Environnement
    //-------------------------------------------------------------//
    $dotenv = new Dotenv();
    $dotenv->load(ROOT_DIR.'.env');

    if (!defined("ENV")) define("ENV",$_ENV['ENV'] ?? "dev");

    //-------------------------------------------------------------//
    // Error logging
    //-------------------------------------------------------------//
    ErrorHandler::register(new Logger("error",[
      new RotatingFileHandler(ROOT_DIR."logs/error/error.log",30),
      new StreamHandler("php://output"),
    ]));
    //-------------------------------------------------------------//
    // Initialisation config du site
    //-------------------------------------------------------------//
    self::$siteConfigFilePath = ROOT_DIR . self::$siteConfigFilePath;
  }

  public static function initCli($rootDir){
    self::init($rootDir);
    //-------------------------------------------------------------//
    // Initialisation des constantes manquantes
    //-------------------------------------------------------------//
    if (!defined("ROOT_URL")) define("ROOT_URL", getConfig('rootUrl'));
    if (!defined("DOMAIN")) define("DOMAIN", getConfig('domain.$ENV'));
    //-------------------------------------------------------------//
    //On charge le Dump Override,Twig et Routing
    //-------------------------------------------------------------//
    Dump::init();
    Route::initCli();
    Twig::init();
  }

  public static function initWeb($rootDir){
    self::init($rootDir);
    //-------------------------------------------------------------//
    // Initialisation des constantes manquantes
    //-------------------------------------------------------------//
    if (!defined("ROOT_URL")) define("ROOT_URL", relativePathUrl(ROOT_DIR));
    if (!defined("DOMAIN")) define("DOMAIN", siteURL());
    //-------------------------------------------------------------//
    //On charge le Dump Override,Twig et Routing
    //-------------------------------------------------------------//
    Dump::init();
    $routed = Route::initWeb();
    Twig::init();
    //-------------------------------------------------------------//
    // Variables transmises dans les templates/controller...
    //-------------------------------------------------------------//
    $datas = [];
    $_SESSION["is_logged"] = $_SESSION["is_logged"] ?? false;

    if (!$routed) {
      $datas['ERROR'] = 'Route inconnue';
      self::notFound($datas);
    }
    $routing = Site::getRouting();
    $page = $routing->vars['p'] ?? "";
    $mcv = explode(":", $page);
    $datas['xdebug'] = getGet('xdebug', 0);
    $module = ucfirst(($routing->vars['m'] ?? null) ?: (($mcv[0] ?? null) ?: 'Home'));
    $controller = ucfirst(($routing->vars['c'] ?? null) ?: (($mcv[1] ?? null) ?: 'Home'));
    $methode = ucfirst(($routing->vars['f'] ?? null) ?: (($mcv[2] ?? null) ?: 'render'));
    $controllerClass = "\\App\\" . $module . "\\Controller\\" . $controller . "Controller";
    if (!class_exists($controllerClass)) {
      $datas['ERROR'] = "Controller : " . $controllerClass . " inconnu";
      if (DEBUG == true) {
        $datas['DEBUG']['ERREURS'][] = "Classe : $controllerClass  introuvable !";
      }
      self::notFound($datas);
    }
    $datas['CTRL'] = [
        'module' => $module
        , 'controller' => $controller
        , 'methode' => $methode,
        'call' => '?p=' . $module . ':' . $controller . ":" . $methode
    ];
    $objPage = new $controllerClass($datas);
    if (!method_exists($objPage, $methode)) {
      $datas['ERROR'] = 'Méthode inconnue';
      if (DEBUG == true) {
        $datas['DEBUG']['ERREURS'][] = "Méthode : $methode  introuvable !";
      }
      self::notFound($datas);
    }
    if (!$objPage->$methode()) {
      $datas['ERROR'] = "Erreur lors du chargement de la méthode";
      if (DEBUG == true) {
        $datas['DEBUG']['ERREURS'][] = "Erreur lors du chargement de la méthode : " . $methode . " dans le controller :" . $controller;
      }
      self::notFound($datas);
    }
  }

  public static function notFound($datas) {
    $tpl_404 = '404.html.twig';
    http_response_code(404);
    echo Site::getTwigEnvironnment()->render($tpl_404, $datas);
    exit;
  }
//--------- FIN DE LA CLASS -----------//
}


?>