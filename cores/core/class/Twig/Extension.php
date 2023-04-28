<?php

namespace Core\Twig;

use Core\Common\Site;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\Template;
use Twig\TemplateWrapper;
use Twig\TwigFilter;
use Twig\TwigFunction;


/** @package App\Twig */
class Extension extends AbstractExtension
{
  private const ASSETS_PATH = "assets/";
  private static $additionalsAssetsPaths = [];
  public function getFilters()
  {
    return [
      new TwigFilter('json_decode', "json_decode"),
      new TwigFilter('mois', [$this, 'mois']),
      new TwigFilter('jour', [$this, 'jour']),
      new TwigFilter('date_literale', [$this, 'dateLiterale']),
      new TwigFilter('sum', "array_sum"),
      new TwigFilter('match', [$this, "match_value"]),
      new TwigFilter('strip_accents', "stripAccents"),
      new TwigFilter('group', [$this, "array_group"]),
      new TwigFilter('preg_replace', [$this, "preg_replace"]),
      new TwigFilter('time_format', [$this, "time_format"]),
    ];
  }

  public function getFunctions()
  {
    return [
      new TwigFunction('asset', [$this, 'asset'], ['is_safe' => ['html']]),
      new TwigFunction('common', [$this, 'common']),
      new TwigFunction('formBuilder', [$this, 'formBuilder'], ['is_safe' => ['html'], 'needs_environment' => true]),
      new TwigFunction('icon', [$this, 'icon'], ['is_safe' => ['html'], 'needs_environment' => true]),
      new TwigFunction('dump', [$this, 'dump'], ['is_safe' => ['html'], 'needs_context' => true]),
      new TwigFunction('imgBase64', [$this, 'imgBase64'], ['is_safe' => ['html']]),
      new TwigFunction('inline_css', [$this, 'inline_css'], ['is_safe' => ['html'], 'needs_environment' => true]),
      new TwigFunction('inline_js', [$this, 'inline_js'], ['is_safe' => ['html'], 'needs_environment' => true]),
      new TwigFunction('jsconst', [$this, 'jsConst'], ['is_safe' => ['html'], 'needs_environment' => true]),
      new TwigFunction('script', [$this, 'script'], ['is_safe' => ['html'], 'needs_environment' => true]),
      new TwigFunction('str_attrs', [$this, 'getAttrsString'], ['is_safe' => ['html'], 'needs_environment' => true]),
      new TwigFunction('style', [$this, 'style'], ['is_safe' => ['html'], 'needs_environment' => true]),
      new TwigFunction('svg', [$this, 'svg'], ['is_safe' => ['html'], 'needs_environment' => true]),
    ];
  }

  public function jsConst(\Twig\Environment $env, $name, $value = null, $default = null)
  {
    $string = twig_escape_filter($env, json_encode($value ?? $default), "js");
    return new Markup("<script type=\"text/javascript\">const $name = JSON.parse(\"" . $string . "\");</script>", "UTF-8");
  }

  public function imgBase64($path)
  {
    $assetsPaths = self::getAssetsPaths();
    do {
      $assetPath = array_shift($assetsPaths);
      $path = ROOT_DIR . $assetPath . $path;
    } while ($assetsPaths && !file_exists($path));

    if (!file_exists($path)) {
      return "";
    }
    return imgToBase64($path);
  }

  public function dump($context, ...$vars)
  {
    if (!$vars) {
      $vars = [];
      foreach ($context as $key => $value) {
        if (!$value instanceof Template && !$value instanceof TemplateWrapper) {
          $vars[$key] = $value;
        }
      }
      dump($vars);
    } else {
      dump(...$vars);
    }
  }

  private function getFilePath($path)
  {
    $assetsPaths = self::getAssetsPaths();
    do {
      $assetPath = array_shift($assetsPaths);
      $testPath = ROOT_DIR . $assetPath . $path;
    } while ($assetsPaths && !file_exists($testPath));
    if (!file_exists($testPath)) {
      return null;
    }

    return ROOT_URL . $assetPath . $path;
  }

  private function errorScriptPath($path)
  {
    return Site::getRouting()->get("path_error", ["path" => $path]);
  }

  private function errorScript(\Twig\Environment $env, $path)
  {
    $sAttrs = $this->getAttrsString($env, [
      "src" => $this->errorScriptPath($path),
      "type" => "text/javascript",
    ], true);
    return new Markup("<script $sAttrs></script>", "UTF-8");
  }

  public function asset($path, $absolute = false)
  {
    $path = $this->getFilePath($path);
    if (!$path) return null;
    if ($absolute) {
      $path = DOMAIN . $path;
    }
    if (DEBUG) {
      $path .=  "?_=" . time();
    }
    return $path;
  }

  public function common($array1, $array2)
  {
    return !empty(array_intersect($array1, $array2));
  }

  public function script(\Twig\Environment $env, $path, $parameters = [])
  {
    $absolute = $parameters["absolute"] ?? false;
    $defer = $parameters["defer"] ?? true;
    $attributes = $parameters["attributes"] ?? [];
    $attributes["type"] = $attributes["type"] ?? "text/javascript";
    foreach (["defer", "src"] as $attr) {
      if (isset($attributes[$attr])) {
        unset($attributes[$attr]);
      }
    }
    if ($defer) {
      $attributes["defer"] = 1;
    }
    $src = $this->asset($path, $absolute);
    if (!$src) {
      return $this->errorScript($env, $path);
    }
    $attributes["src"] = $src;
    $sAttrs = $this->getAttrsString($env, $attributes, true);
    return new Markup("<script $sAttrs></script>", "UTF-8");
  }

  public function getAttrsString(\Twig\Environment $env, $attributes = [], $noMarkup = false)
  {
    $sAttrs = implode(" ", array_map(function ($key, $value) use ($env) {
      return twig_escape_filter($env, $key, "html") . "=\"" . twig_escape_filter($env, $value, "html") . "\"";
    }, array_keys($attributes), $attributes));
    if ($noMarkup) {
      return $sAttrs;
    }
    return new Markup($sAttrs, "UTF-8");
  }

  public function style(\Twig\Environment $env, $path, $absolute = false)
  {
    $absolute = $parameters["absolute"] ?? false;
    $attributes = $parameters["attributes"] ?? [];
    $attributes["rel"] = $attributes["rel"] ?? "stylesheet";
    $attributes["type"] = $attributes["type"] ?? "text/css";
    if (isset($attributes["href"])) {
      unset($attributes["href"]);
    }
    $href = $this->asset($path, $absolute);
    if (!$href) {
      return $this->errorScript($env, $path);
    }
    $attributes["href"] = $href;
    $sAttrs = $this->getAttrsString($env, $attributes, true);
    return new Markup("<link $sAttrs>", "UTF-8");
  }

  public function icon(\Twig\Environment $env, $path, $absolute = false)
  {
    $href = $this->asset($path, $absolute);
    if (!$href) {
      return $this->errorScript($env, $path);
    }
    $attributes = [
      "rel" => "icon",
      "href" => $href,
    ];
    $sAttrs = $this->getAttrsString($env, $attributes, true);
    return new Markup("<link $sAttrs>", "UTF-8");
  }

  public function svg(\Twig\Environment $env, $path)
  {
    $assetsPaths = self::getAssetsPaths();
    do {
      $assetPath = array_shift($assetsPaths);
      $svgPath = ROOT_DIR . $assetPath . $path;
    } while ($assetsPaths && !file_exists($path));

    if (!file_exists($svgPath) || is_dir($svgPath)) {
      return $this->errorScript($env, $path);
    }

    return new Markup(file_get_contents($svgPath), "UTF-8");
  }

  public function mois($mois, $capitalized = false)
  {
    $mois = intval($mois);
    $str_mois = [
      1 => "janvier",
      2 => "février",
      3 => "mars",
      4 => "avril",
      5 => "mai",
      6 => "juin",
      7 => "juillet",
      8 => "août",
      9 => "septembre",
      10 => "octobre",
      11 => "novembre",
      12 => "décembre",
    ];
    return $capitalized ? ucfirst($str_mois[$mois]) : $str_mois[$mois];
  }

  public function jour($jour, $capitalized = false)
  {
    $jour = intval($jour);
    if (!$jour) {
      return "";
    }
    $str_jours = [
      1 => "lundi",
      2 => "mardi",
      3 => "mercredi",
      4 => "jeudi",
      5 => "vendredi",
      6 => "samedi",
      7 => "dimanche"
    ];
    return $capitalized ? ucfirst($str_jours[$jour]) : $str_jours[$jour];
  }

  public function dateLiterale($date = 'now', $jourCapitalized = false, $moisCapitalized = false)
  {
    $time = strtotime($date);
    $dateLiterale = implode(" ", [
      $this->jour(date("w", $time), $jourCapitalized), //JOUR
      date("d", $time),                               //DATE
      $this->mois(date("n", $time), $moisCapitalized), //MOIS
      date("Y", $time),                               //ANNEE
    ]);
    return $dateLiterale;
  }

  public function inline_js(\Twig\Environment $env, $path)
  {
    $assetsPaths = self::getAssetsPaths();
    do {
      $assetPath = array_shift($assetsPaths);
      $jsPath = ROOT_DIR . $assetPath . $path;
    } while ($assetsPaths && !file_exists($jsPath));

    if (!file_exists($jsPath) || is_dir($jsPath)) {
      return $this->errorScript($env, $path);
    }

    $sAttrs = $this->getAttrsString($env, [
      "type" => "text/javascript"
    ], true);
    $jsCode = PHP_EOL . file_get_contents($jsPath) . PHP_EOL;
    return new Markup("<script $sAttrs>" . $jsCode . "</script>", "UTF-8");
  }

  public function inline_css(\Twig\Environment $env, $path)
  {
    $assetsPaths = self::getAssetsPaths();
    do {
      $assetPath = array_shift($assetsPaths);
      $cssPath = ROOT_DIR . $assetPath . $path;
    } while ($assetsPaths && !file_exists($cssPath));

    if (!file_exists($cssPath) || is_dir($cssPath)) {
      return $this->errorScript($env, $path);
    }

    $sAttrs = $this->getAttrsString($env, [
      "type" => "text/css"
    ], true);
    $cssCode = PHP_EOL . file_get_contents($cssPath) . PHP_EOL;
    return new Markup("<style $sAttrs>" . $cssCode . "</style>", "UTF-8");
  }

  public function formBuilder(\Twig\Environment $env, $name)
  {
    return new FormBuilder($env, $this, $name);
  }

  public static function addAssetPath($path)
  {
    $path = str_replace("\\", "/", realpath($path));
    $rootDir = str_replace("\\", "/", ROOT_DIR);
    $path = str_replace($rootDir, "", $path) . "/";
    self::$additionalsAssetsPaths[] = $path;
  }

  private static function getAssetsPaths()
  {
    $frameworkDir = str_replace("\\", "/", FRAMEWORK_DIR);
    $rootDir = str_replace("\\", "/", ROOT_DIR);
    $frameworkPath = str_replace($rootDir, "", $frameworkDir);
    $fwPaths = $frameworkPath ? [$frameworkPath . self::ASSETS_PATH, $frameworkPath] : [];
    return array_merge($fwPaths, self::$additionalsAssetsPaths, [self::ASSETS_PATH, ""]);
  }

  /**
   * 
   * @param mixed $value 
   * @param array $associativeArray 
   * @param mixed $defaultValue 
   * @return mixed 
   */
  public function match_value(mixed $value, array $associativeArray, mixed $defaultValue = null)
  {
    return $associativeArray[$value] ?? $defaultValue;
  }

  public function array_group(array $array, $key)
  {
    $grouped = [];
    foreach ($array as $item) {
      $grouped[$item[$key]][] = $item;
    }
    return $grouped;
  }

  public function preg_replace($subject, $pattern, $replacement)
  {
    return preg_replace($pattern, $replacement, $subject);
  }

  /**
   * Formatte un date au format HH:MM au format spécifié
   * @return string 
   */
  public function time_format($time, $format = "H:i")
  {
    return date($format, strtotime("1970-01-01 $time"));
  }
}
