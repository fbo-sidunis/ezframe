<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\EzTools\Model;

class Routes {

  private static $jsonFile = ROOT_DIR . 'config/routes.json';

  /**
   * Retourne la liste des routes à partir du fichier json
   * @return false|array 
   */
  public static function getRoutesFromJson() {
    $res = [];
    if (!file_exists(self::$jsonFile)) {
      return errorResponse(["file" => self::$jsonFile], "Fichier Manquant", 404);
    }
    $json = file_get_contents(self::$jsonFile);
    $arr = !empty($json) ? json_decode($json, true) : null;
    if (!empty($arr)) {
      foreach ($arr as $url => $R) {
        $res[$R['alias']] = self::route2Array($R);
        $res[$R['alias']]['url'] = $url;
      }
    }
    return $res;
  }

  /**
   * 
   * @param mixed $route 
   * @return array 
   */
  public static function route2Array($route) {
    $res = [];
    $res['alias'] = $route['alias'];
    $vars = $route['vars'];
    foreach ($vars as $K => $V) {
      switch ($K) {
        case 'p':
          $spl = explode(":", $V);
          $res['m'] = !empty($spl[0]) ? ucfirst($spl[0]) : "";
          $res['c'] = !empty($spl[1]) ? ucfirst($spl[1]) : "";
          $res['f'] = !empty($spl[2]) ? $spl[2] : "render";
          break;
        case 'm':
          $res[$K] = !empty($V) ? ucfirst($V) : "";
          break;
        case 'c':
          $res[$K] = !empty($V) ? ucfirst($V) : "";
          break;
        default:
          $res[$K] = $V;
          break;
      }
    }

    return $res;
  }

  /**
   * Recherche une route par son alias
   * @param string $alias
   * @return array (route)
   */
  public Static function getRouteByAlias($alias) {
    $allRoutes = self::getRoutesFromJson();
    //return $allRoutes;
    return !empty($allRoutes[trim($alias)]) ? $allRoutes[trim($alias)] : NULL;
  }

  public static function WritteRoutesToJson() {
    
  }

  /**
   * Retourne la liste des modules
   * @return array 
   */
  public static function getModules() {
    $dirModule = ROOT_DIR . 'app/';
    $arrApps = scandir($dirModule);
    $apps = [];
    if (!empty($arrApps)) {
      foreach ($arrApps as $fd) {
        if ($fd != '.' && $fd != '..' && is_dir($dirModule . $fd)) {
          $apps[] = ucfirst($fd);
        }
      }
    }
    return $apps;
  }

  /**
   * Liste des controllers d'un module
   * @param string $app
   * @return array
   */
  public static function getController($app) {
    $dirApp = ROOT_DIR . 'app/' . ucfirst($app) . '/Controller/';
    if (is_dir($dirApp)) {
      $arrCtrl = scandir($dirApp);

      $ctrl = [];
      if (!empty($arrCtrl)) {
        foreach ($arrCtrl as $fd) {
          if ($fd != '.' && $fd != '..' && is_file($dirApp . $fd) && str_contains($fd, 'Controller.php')) {
            $ctrl[] = str_replace("Controller", "", ucfirst(basename($fd, '.php')));
          }
        }
      }
      return $ctrl;
    } else {
      return ['ERROR DIR APP' => $dirApp];
    }
  }

  /**
   * Liste des méthode de la class
   * @param string $app
   * @param string $ctrl
   * @return array
   */
  public static function getControllerFunctions($app, $ctrl) {
    $ctrlPath = ROOT_DIR . 'app/' . $app . '/Controller/' . $ctrl . "Controller.php";
    if (file_exists($ctrlPath)) {
      //include_once $ctrlPath;
      $className = trim(ucfirst($ctrl)) . "Controller";
      //return ['classname' => $className];

      $fullClassName = "\\App\\$app\\Controller\\$className";

      $class = new \ReflectionClass($fullClassName);
      $arrMethodes = $class->getMethods();
      $methodes = [];
      if (!empty($arrMethodes)) {
        foreach ($arrMethodes as $M) {
          if ($M->class != "Core\\Controller") {
            $methodes[] = $M->name;
          }
        }

        return $methodes;
      }
    } else {
      return ["file error" => $ctrlPath];
    }
  }

  /**
   * Liste des templates
   * @return array
   */
  public static function getTemplates() {
    $templateDir = ROOT_DIR . 'templates';
    if (is_dir($templateDir)) {
      $arrTmpl = self::recursive_directory($templateDir, 10);
      return $arrTmpl;
    }
  }

  /**
   * Récursivité pour récupérer les fichiers d'un dossier
   * @param mixed $dirname 
   * @param int $maxdepth 
   * @param int $depth 
   * @return false|array 
   */
  private static function recursive_directory($dirname, $maxdepth = 10, $depth = 0) {
    if ($depth >= $maxdepth) {
      return false;
    }
    $subdirectories = array();
    $files = array();
    if (is_dir($dirname) && is_readable($dirname)) {
      $d = dir($dirname);
      while (false !== ($f = $d->read())) {
        $file = $d->path . '/' . $f;
        // skip . and ..
        if (('.' == $f) || ('..' == $f)) {
          continue;
        };
        if (is_dir($dirname . '/' . $f)) {
          array_push($subdirectories, $dirname . '/' . $f);
        } else {
          array_push($files, str_replace(ROOT_DIR . "templates/", "", $dirname . '/' . $f));
        }
      }
      $d->close();
      foreach ($subdirectories as $subdirectory) {
        $files = array_merge($files, self::recursive_directory($subdirectory, $maxdepth, $depth + 1));
      };
    }
    sort($files);
    return $files;
  }

  /**
   * Sauvegarde les routes dans un fichier json
   * @param mixed $url 
   * @param mixed $alias 
   * @param mixed $m 
   * @param mixed $c 
   * @param mixed $f 
   * @param mixed $template 
   * @param mixed $fallback 
   * @return int|false 
   */
  public static function saveRoute($url, $alias, $m, $c, $f, $template = NULL, $fallback = NULL) {
    //On récupère les routes existantes
    $json = file_get_contents(self::$jsonFile);
    $allRoutes = json_decode($json, true);
    //Ajout du slash de fin d'url si pas présent..
    $url = rtrim($url, "/") . '/';

    $allRoutes[$url] = [
        "alias" => $alias
        , "vars" => []
    ];
    $allRoutes[$url]['vars']['m'] = $m;
    $allRoutes[$url]['vars']['c'] = $c;
    $allRoutes[$url]['vars']['f'] = $f;
    if (!empty($template)) {
      $allRoutes[$url]['vars']["t"] = $template;
    }
    if (!empty($fallback)) {
      $allRoutes[$url]['vars']["fallback"] = $fallback;
    }

    // on sauvegarde le JSON
    $json = json_encode($allRoutes);
    $res = file_put_contents(self::$jsonFile, stripcslashes($json));
    return $res;
  }

  /**
   * Supprime une route
   * @param mixed $url 
   * @return int|false 
   */
  public static function deleteRoute($url) {
    //On récupère les routes existantes
    $json = file_get_contents(self::$jsonFile);
    $allRoutes = json_decode($json, true);
    //Ajout du slash de fin d'url si pas présent..
    $url = rtrim($url, "/") . '/';
    unset($allRoutes[$url]);

    // on sauvegarde le JSON
    $json = json_encode($allRoutes);
    $res = file_put_contents(self::$jsonFile, stripcslashes($json));
    return $res;
  }

}
