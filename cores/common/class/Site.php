<?php

namespace Core\Common;

use Core\Response;
use Core\Response\HtmlResponse;
use Core\Start\Dump;
use Core\Start\I18n;
use Core\Start\Route;
use Core\Start\Twig;
use Exception;
use Monolog\ErrorHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Dotenv\Dotenv;

class Site
{

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

  /**
   * 
   * @var Logger|null
   */
  private static $errorLogger = null;
  /**
   * 
   * @var Logger|null
   */
  private static $errorLoggerFileOnly = null;

  public static function getSiteConfig_()
  {
    return [
      "TEMPLATES" => TEMPLATES,
      "ENV" => ENV,
      "DEBUG" => DEBUG,
      "AUTODEBUG" => AUTODEBUG,
    ];
  }

  public static function getConfigFilePath()
  {
    return ROOT_DIR . self::$siteConfigFilePath;
  }
  public static function getTwigEnvironnment()
  {
    return self::$twigEnvironnement;
  }
  public static function getRouting()
  {
    return self::$routing;
  }
  public static function getTranslator()
  {
    return self::$translator;
  }
  public static function setTwigEnvironnment($twig)
  {
    self::$twigEnvironnement = $twig;
  }
  public static function setRouting($routing)
  {
    self::$routing = $routing;
  }
  public static function setTranslator($translator)
  {
    self::$translator = $translator;
  }
  public static function getErrorLogger()
  {
    if (self::$errorLogger === null) {
      self::$errorLogger = new Logger("error", [
        new RotatingFileHandler(ROOT_DIR . "var/log/error/error.log", 30),
        new StreamHandler("php://output"),
      ]);
    }
    return self::$errorLogger;
  }

  public static function getErrorLoggerFileOnly()
  {
    if (self::$errorLoggerFileOnly === null) {
      self::$errorLoggerFileOnly = new Logger("error", [
        new RotatingFileHandler(ROOT_DIR . "var/log/error/error.log", 30),
      ]);
    }
    return self::$errorLoggerFileOnly;
  }

  public static function init($rootDir)
  {
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
    if (!defined("ROOT_DIR")) define("ROOT_DIR", realpath($rootDir) . "/");
    if (!defined("APP_DIR")) define("APP_DIR", ROOT_DIR . "app/");
    if (!defined("LIBRARY_DIR")) define("LIBRARY_DIR", realpath(__DIR__ . "/../../../") . "/");
    if (!defined("TEMPLATES")) define("TEMPLATES", getConfig('twig.templates'));
    if (!defined("SITE_TITLE")) define("SITE_TITLE", getConfig('title'));
    if (!defined("DEBUG")) define("DEBUG", getConfig('debug'));
    if (!defined("AUTODEBUG")) define("AUTODEBUG", getConfig('autodebug'));

    //-------------------------------------------------------------//
    // Initialisation des constantes Environnement
    //-------------------------------------------------------------//
    $dotenv = new Dotenv();
    $dotenv->load(ROOT_DIR . '.env');

    if (!defined("ENV")) define("ENV", $_ENV['ENV'] ?? "dev");

    //-------------------------------------------------------------//
    // Error logging
    //-------------------------------------------------------------//
    ErrorHandler::register(self::getErrorLogger());
    //-------------------------------------------------------------//
    // Initialisation config du site
    //-------------------------------------------------------------//
    self::$siteConfigFilePath = ROOT_DIR . self::$siteConfigFilePath;
  }

  public static function initCli($rootDir)
  {
    self::init($rootDir);
    //-------------------------------------------------------------//
    // Initialisation des constantes manquantes
    //-------------------------------------------------------------//
    if (!defined("ROOT_URL")) define("ROOT_URL", getConfig('rootUrl'));
    if (!defined("LIBRARY_URL")) define("LIBRARY_URL", ROOT_URL . "vendor/groupefbo/ezframe/");
    if (!defined("DOMAIN")) define("DOMAIN", getConfig('domain.$ENV'));
    //-------------------------------------------------------------//
    //On charge le Dump Override,Twig et Routing
    //-------------------------------------------------------------//
    Dump::init();
    Route::initCli();
    I18n::init();
    Twig::init();
  }

  public static function initWeb($rootDir)
  {
    self::init($rootDir);
    //-------------------------------------------------------------//
    // Initialisation des constantes manquantes
    //-------------------------------------------------------------//
    if (!defined("ROOT_URL")) define("ROOT_URL", relativePathUrl(ROOT_DIR));
    if (!defined("LIBRARY_URL")) define("LIBRARY_URL", ROOT_URL . "vendor/groupefbo/ezframe/");
    if (!defined("DOMAIN")) define("DOMAIN", siteURL());
    //-------------------------------------------------------------//
    //On charge le Dump Override,Twig et Routing
    //-------------------------------------------------------------//
    Dump::init();
    $routed = Route::initWeb();
    I18n::init();
    Twig::init();
    //-------------------------------------------------------------//
    // Variables transmises dans les templates/controller...
    //-------------------------------------------------------------//
    $datas = [];
    $_SESSION["is_logged"] = $_SESSION["is_logged"] ?? false;
    $errorResponse = new HtmlResponse('404.html.twig', $datas);
    $errorResponse->setErrorCode(404);
    if (!$routed) {
      $errorResponse->setData("ERROR", "Route inconnue");
      return $errorResponse->display();
    }
    $routing = Site::getRouting();
    $page = $routing->vars['p'] ?? "";
    $mcv = explode(":", $page);
    $datas['xdebug'] = getGet('xdebug', 0);
    $module = ucfirst(($routing->vars['m'] ?? null) ?: (($mcv[0] ?? null) ?: 'Default'));
    $controller = ucfirst(($routing->vars['c'] ?? null) ?: (($mcv[1] ?? null) ?: 'Default'));
    $methode = ucfirst(($routing->vars['f'] ?? null) ?: (($mcv[2] ?? null) ?: 'render'));
    $controllerClass = "\\App\\" . $module . "\\Controller\\" . $controller . "Controller";
    if (!class_exists($controllerClass)) {
      $errorResponse->setData("ERROR", "Controller : " . $controllerClass . " inconnu");
      $datas['ERROR'] = "Controller : " . $controllerClass . " inconnu";
      if (DEBUG == true) {
        $errorResponse->setData("DEBUG", ["ERREURS" => ["Controller : " . $controllerClass . " inconnu"]]);
      }
      return $errorResponse->display();
    }
    $datas['CTRL'] = [
      'module' => $module, 'controller' => $controller, 'methode' => $methode,
      'call' => '?p=' . $module . ':' . $controller . ":" . $methode
    ];
    $objPage = new $controllerClass($datas);
    if (!method_exists($objPage, $methode)) {
      $errorResponse->setData("ERROR", "Méthode : " . $methode . " inconnue");
      if (DEBUG == true) {
        $errorResponse->setData("DEBUG", ["ERREURS" => ["Méthode : " . $methode . " inconnue"]]);
      }
      return $errorResponse->display();
    }
    //Get Method return type 
    $reflection = new \ReflectionMethod($objPage, $methode);
    $returnType = $reflection->getReturnType();
    if ($returnType && $returnType instanceof \ReflectionNamedType) {
      $returnType = $returnType->getName();
    } else {
      $returnType = Response::class;
    }
    $response = null;
    try {
      /** @var Response */
      $response = $objPage->$methode();
      $response->display();
    } catch (\Throwable $e) {
      /** @var \Exception|\Core\Exception|\TypeError $e */
      $datas = (method_exists($e, 'getData') ? $e->getData() : []) ?: [];
      $backtrace = [];
      $traceLength = count($e->getTrace());
      foreach ($e->getTrace() as $index => $trace) {
        if (isset($trace['object']) && $trace['object'] instanceof \Twig\Template) {
          $template = $trace['object'];
          $name = $template->getSourceContext()->getPath();
          $line = $template->getDebugInfo()[$backtrace[$index]['line'] ?? -1] ?? null;
          $backtrace[strval($traceLength - $index)] = [
            "file" => $name . ":" . $line,
            "function" => $trace["function"] ?? null,
          ];
          continue;
        }
        if (!isset($trace["file"])) continue;
        $backtrace[strval($traceLength - $index)] = [
          "file" => $trace["file"] . (!empty($trace["line"]) ? (":" . $trace["line"]) : ""),
          "function" => implode("::", array_filter([$trace["class"] ?? null, $trace["function"] ?? null])) . "(" . implode(", ", array_map(function ($arg) {
            return is_object($arg) ? get_class($arg) : json_encode($arg);
          }, $trace["args"] ?? [])) . ")",
        ];
      }
      if ($returnType) {
        $returnType::displayErrorResponse($e->getMessage(), $datas, $backtrace, $e->getFile(), $e->getLine());
      }
      self::getErrorLoggerFileOnly()->error($e->getMessage(), [
        "file" => $e->getFile(),
        "line" => $e->getLine(),
      ]);
    }
  }
  //--------- FIN DE LA CLASS -----------//
}
