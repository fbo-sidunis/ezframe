<?php

namespace Core;

use Core\Common\Site;
use Core\Start\I18n;

class Translator
{
  protected $translations = [];
  protected $locale = "fr";
  protected $defaultLocale = "fr";
  protected $cookieName = "locale";
  protected $cookieExpire = 3600 * 24 * 365;
  protected $cookiePath = "/";

  function __construct($translations = [])
  {
    $this->translations = $translations;
    $this->retreiveLocale();
  }

  public function addToDefaultFile($key, $translation)
  {
    $translations = json_decode(file_get_contents(I18n::MAIN_CONFIG_FILE), true);
    $translations[$key][$this->defaultLocale] = $translation;
    ksort($translations);
    $json = json_encode($translations, JSON_PRETTY_PRINT);
    file_put_contents(I18n::MAIN_CONFIG_FILE, $json);
  }

  public function getLocale()
  {
    return $this->locale;
  }

  public function setLocale($locale)
  {
    setcookie($this->cookieName, $locale, time() + $this->cookieExpire, $this->cookiePath);
    $this->locale = $locale;
  }

  public function getDefaultLocale()
  {
    return $this->defaultLocale;
  }

  public function getTranslations()
  {
    return $this->translations;
  }

  public function setTranslations($translations)
  {
    $this->translations = $translations;
  }

  public function retreiveLocale()
  {
    $locale = $this->defaultLocale;
    if (isset($_COOKIE[$this->cookieName])) {
      $locale = $_COOKIE[$this->cookieName];
    }
    if (isset($_GET["locale"])) {
      $locale = $_GET["locale"];
    }
    $this->locale = $locale;
    setcookie($this->cookieName, $locale, time() + $this->cookieExpire, $this->cookiePath);
  }

  public function translate($key, $parameters = [])
  {
    $locale = $parameters["locale"] ?? $this->locale;
    $vars = $parameters["vars"] ?? [];
    $translations = $this->translations[$key] ?? [];
    if (empty($translations)) {
      $this->addToDefaultFile($key, $key);
      $translations = $this->translations[$key] ?? [];
    }
    $translation = $translations[$locale] ?? $translations[$this->defaultLocale] ?? null;
    $translation = Site::getTwigEnvironnment()->createTemplate($translation ?? "")->render($vars);
    return $translation;
  }

  public static function t($key, $parameters = [])
  {
    return Site::getTranslator()->translate($key, $parameters);
  }

  public function addToDictionary($key, $translations)
  {
    $this->translations[$key] = $translations;
  }
}
