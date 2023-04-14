<?php
namespace Core;

use Core\Common\Site;

class Translator
{
  protected $translations = [];
  protected $locale = "fr";
  protected $defaultLocale = "fr";
  protected $twig = null;
  protected $cookieName = "locale";
  protected $cookieExpire = 3600 * 24 * 365;
  protected $cookiePath = "/";
  protected const DEFAULT_FILE = "config/i18n.json";

  function __construct($translations = []){
    $this->translations = $translations;
    $this->twig = Site::getTwigEnvironnment();
  }

  public function addToDefaultFile($key,$translation){
    $translations = arrayFromJson(ROOT_DIR . self::DEFAULT_FILE);
    $translations[$key][$this->defaultLocale] = $translation;
    ksort($translations);
    $json = json_encode($translations, JSON_PRETTY_PRINT);
    file_put_contents(ROOT_DIR . self::DEFAULT_FILE, $json);
  }

  public function getLocale(){
    return $this->locale;
  }

  public function setLocale($locale){
    setcookie($this->cookieName, $locale, time() + $this->cookieExpire, $this->cookiePath);
    $this->locale = $locale;
  }

  public function getDefaultLocale(){
    return $this->defaultLocale;
  }

  public function getTranslations(){
    return $this->translations;
  }

  public function setTranslations($translations){
    $this->translations = $translations;
  }

  public function retreiveLocale(){
    $locale = $this->defaultLocale;
    if(isset($_COOKIE[$this->cookieName])){
      $locale = $_COOKIE[$this->cookieName];
    }
    if(isset($_GET["locale"])){
      $locale = $_GET["locale"];
    }
    $this->locale = $locale;
    setcookie($this->cookieName, $locale, time() + $this->cookieExpire, $this->cookiePath);
  }

  public function translate($key, $parameters = []){
    $locale = $parameters["locale"] ?? $this->locale;
    $vars = $parameters["vars"] ?? [];
    $translations = $this->translations[$key] ?? [];
    if (empty($translations)){
      $this->addToDefaultFile($key, $key);
      $translations = $this->translations[$key] ?? [];
    }
    $translation = $translations[$locale] ?? $translations[$this->defaultLocale] ?? null;
    $translation = $this->twig->createTemplate($translation)->render($vars);
    return $translation;
  }

  public static function t($key, $parameters = []){
    return Site::getTranslator()->translate($key, $parameters);
  }

  public function addToDictionary($key, $translations){
    $this->translations[$key] = $translations;
  }

}