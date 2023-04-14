<?php
namespace Core;

class Route
{
  protected $routes;
  protected $routesConfig;
  public $route = "login";
  public $vars = [];

  function __construct($routesConfig = []){
    $this->routes = [];
    $this->routesConfig = $routesConfig;
    foreach ($routesConfig as $config_key=>$config){
      $this->routes[$config["alias"]] = $config_key;
    }
  }

  public function getRoutes(){
    return $this->routes;
  }

  public function getRoutesForSitemap(){
    return array_filter($this->routesConfig,function($route){
      return $route["sitemap"] ?? false;
    });
  }

  public static function testForSitemap(){
    return [1,2,5,4];
  }

  public function getVars(){
    return $this->vars;
  }

  /**
	 * Permet de générer la Route en fonction des l'alias (et des variables)
	 * La route récupérée sera toujours relative au domaine
	 * Elle prends également en compte si la racine du site se situe à la racine du domaine ou pas.
   * Si absolute est à true, le chemin retournée est absolu
   * @param string $alias
   * @param array $variables
   * @param bool $absolute
   * @return string [URL DE LA ROUTE]
   */
  public function get(string $alias, $variables = [],$absolute = false)
  {
    $url = !empty($this->routes[$alias]) ? $this->routes[$alias] : null;
    $arrUrl = (\explode("/",$url ?? ""));
    if (\str_contains((string)$url,"{") || \str_contains((string)$url,"[")){
      foreach($arrUrl as &$urlPart){
        $isMatching = preg_match("/\{([^\/]*?)\}/",$urlPart,$matches);
        if ($isMatching) $matches[1] = \str_replace("/","",$matches[1]);
        $isMatching = $isMatching ?: preg_match("/\[([^\/]*?)\]/",$urlPart,$matches);
        if ( $isMatching ){
          if ($variables && !empty($variables[$matches[1]])){
            $divisedUrlPart = explode("/",$variables[$matches[1]]);
            foreach ($divisedUrlPart as &$part) $part = urlencode($part);
            $urlPart = implode("/",$divisedUrlPart);
            unset($variables[$matches[1]]);
          }
        }
      }
    }
    array_shift($arrUrl);
    array_pop($arrUrl);
    $url = implode("/",$arrUrl);
    $vars = "";
    if (AUTODEBUG && DEBUG) $variables["xdebug"] = 1;
    if (!empty($variables)){
      $arrVars = [];
      foreach($variables as $key=>$value){
        $arrVars[] = urlencode($key)."=".urlencode($value);
      }
      if ($arrVars) $vars = "?" . implode("&",$arrVars);
    }
    return ($absolute ? DOMAIN : "") . ROOT_URL . $url . $vars;
  }

  public function redirect(string $alias, $variables = []){
    if (empty($alias)) return false;
    $url = $this->get($alias,$variables);
    return redirectURL($url);
  }

  function __toString()
  {
    return $this->get($this->route,$this->vars);
  }
}