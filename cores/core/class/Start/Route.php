<?php

namespace Core\Start;

use Core\Annotation\Route as AnnotationRoute;
use Core\Common\Site;
use Core\Route as CoreRoute;

/**
  Les routes sont entrées sous \config\routes.json
  Sous cette forme :
    "/" : {
      "alias" : "alias"
      ,"vars":{"m" : "Module" ,"c" : "Controller","f" : "Fonction"}
    }
  Avec un paramètre passé dans l'url :
    "/avecunparam/{param}/" : {
      "alias" : "avecunparam"
      ,"vars":{"m" : "Module" ,"c" : "Controller","f" : "Fonction"}
    }
  Avec un paramètre passé dans l'url pouvant inclure des "/" :
    "/avecunparam/[param]/" : {
      "alias" : "avecunparam"
      ,"vars":{"m" : "Module" ,"c" : "Controller","f" : "Fonction"}
    }
  Note : ce paramètre sera récupèrable à partir de son nom donné dans l'url en GET et en POST;

  "alias" : La route liée à celle-ci, pourra être appelée dans les templates TWIG avec Route.get(alias(,{parameter:value,...})})
  "vars" : Les paramètres récupèrables en GET et en POST;
  La clé de la config contient toujours la forme du lien, ce lien DEVRA TOUJOURS commencer et terminer par un / ;
  Note : Si la route exacte n'existe pas, le programme va tester les routes avec des accolades, la route correspondante qu'il trouvera en premier sera prise.
 */

class Route
{

  public static $routes_config = [];

  public static function initCli()
  {
    self::$routes_config = arrayFromJson(ROOT_DIR . "config/routes.json");
    $controllers = [];
    foreach ((assetsMap(APP_DIR, 1) ?: []) as $mod) {
      $rpath = APP_DIR . $mod . "/routes.json";
      if (file_exists($rpath)) {
        self::mergeRoutesIntoConfig($mod, $rpath);
      } else {
        foreach ((assetsMap(APP_DIR . $mod, 1) ?: []) as $file) {
          if (str_starts_with($file, "routes_") && str_ends_with($file, ".json")) {
            $rpath = APP_DIR . $mod . "/" . $file;
            self::mergeRoutesIntoConfig($mod, $rpath);
          }
        }
        foreach ((assetsMap(APP_DIR . $mod . "/Controller", 1) ?: []) as $file) {
          if (str_ends_with($file, "Controller.php")) {
            $controllers[] = "\\App\\" . $mod . "\\Controller\\" . str_replace(".php", "", $file);
          }
        }
      }
    }
    //minimum PHP 8.0
    if (str_starts_with(PHP_VERSION, "8")) {
      self::mergeRoutesFromAnnotations($controllers);
    }
    Site::setRouting(new CoreRoute(self::$routes_config));
  }

  protected static function mergeRoutesFromAnnotations(array $controllers = [])
  {
    foreach ($controllers as $controller) {
      $splitted = explode("\\", $controller);
      $controllerClass = array_pop($splitted);
      //remove the last "Controller" from the class name
      $controllerStringSplitted = explode("Controller", $controllerClass);
      $controllerString = implode("Controller", array_filter($controllerStringSplitted));
      if (empty($controllerString)) {
        continue;
      }
      array_pop($splitted);
      $moduleClass = array_pop($splitted);
      //get all public non-static functions of controller
      $reflection = new \ReflectionClass($controller);
      $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
      foreach ($methods as $method) {
        if ($method->isStatic()) {
          continue;
        }
        $routes = $method->getAttributes(AnnotationRoute::class, \ReflectionAttribute::IS_INSTANCEOF);
        if (empty($routes)) {
          continue;
        }
        $vars = [
          "m" => $moduleClass,
          "c" => $controllerString,
          "f" => $method->getName(),
        ];
        foreach ($routes as $route) {
          $route = new AnnotationRoute(...$route->getArguments());
          self::$routes_config[$route->getPath()] = [
            "alias" => $route->getAlias(),
            "vars" => $route->getVars($vars)
          ];
        }
      }
    }
  }

  protected static function mergeRoutesIntoConfig(
    string $mod,
    string $rpath
  ) {
    $array = arrayFromJson($rpath);
    foreach ($array as $key => &$value) {
      $value["vars"]["m"] = $value["vars"]["m"] ?? $mod;
    }
    self::$routes_config = array_merge(self::$routes_config, $array);
  }

  public static function initWeb()
  {
    self::initCli();
    $routing = Site::getRouting();
    $route_handler = function ($uri) use ($routing) {
      if (substr($uri, -1) !== "/") $uri .= "/";
      $rewriter = self::getRewrite($uri);
      if ($rewriter) {
        redirectRoute($rewriter["alias"]);
        exit;
      }
      $config = !empty(self::$routes_config[$uri]) ? self::$routes_config[$uri] : null;
      $rewrite = $config ? ($config["rewrite"] ?? null) : null;
      if ($rewrite) {
        $config = !empty($routes_config[$uri]) ? $routes_config[$uri] : null;
        $uri = $rewrite;
        if (substr($uri, -1) !== "/") $uri .= "/";
      }
      if (!$config) {
        $routes_config_ = self::$routes_config;
        $routes_config_keys = array_keys(self::$routes_config);
        $chosen_one = null;
        while (!$chosen_one and !empty($routes_config_)) {
          $config_ = array_shift($routes_config_);
          $key = array_shift($routes_config_keys);
          $regexp = str_replace("/", "\/", $key);
          $regexp = preg_replace("/\{[^\/]*?\}/", "([^\/]*?)", $regexp);
          $regexp = preg_replace("/\[[^\/]*?\]/", "(.*?)", $regexp);
          $regexp = "/^" . $regexp . "$/";
          if (preg_match($regexp, $uri, $matches)) {
            $chosen_one = $key;
            $config = $config_;
          }
        }
        if ($chosen_one) {
          $chosen_exploded = explode("/", $chosen_one);
          $count = 1;
          foreach ($chosen_exploded as $K => $V) {
            if (preg_match("/\{([^\/]*?)\}/", $V, $variable) || preg_match("/\[([^\/]*?)\]/", $V, $variable)) {
              $_GET[$variable[1]] = $matches[$count];
              $_POST[$variable[1]] = $matches[$count];
              $_REQUEST[$variable[1]] = $matches[$count];
              $routing->vars[$variable[1]] = $matches[$count];
              $count++;
            }
          }
        }
      }
      if ($config) {
        $routing->route = $config["alias"] ?? null;
        $routing->vars = $config["vars"] ?? [];
        return true;
      } else {
        return false;
      }
    };


    // Fetch method and URI from somewhere
    $httpMethod = $_SERVER['REQUEST_METHOD'];
    $uri = $_SERVER['REQUEST_URI'];
    if (str_starts_with($uri, ROOT_URL)) {
      $count = 1;
      $uri = str_replace(ROOT_URL, "/", $uri, $count);
    }

    // Strip query string (?foo=bar) and decode URI
    if (false !== $pos = strpos($uri, '?')) {
      $uri = substr($uri, 0, $pos);
    }
    $uri = rawurldecode($uri);
    if (!strpos($uri, ".js") && !strpos($uri, ".css") && !strpos($uri, ".png") && !strpos($uri, ".jpeg") && !strpos($uri, ".pdf")) {
      return $route_handler($uri);
    } else {
      return true;
    }
  }

  private static function getRewrite($uri)
  {
    //search routes configs for a config with rewrite parameter = $uri
    //if found, return the route config
    //else return null
    foreach (self::$routes_config as $key => $config) {
      if (($config["rewrite"] ?? null) == $uri) {
        return $config;
        break;
      }
    }
    return null;
  }
}
