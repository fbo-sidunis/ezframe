<?php

namespace Core\Start;

use Core\Common\Site;
use Core\Translator;

class I18n
{
  public static $translations;
  public const MAIN_CONFIG_FILE = ROOT_DIR . "config/i18n.json";
  public const APP_DIR = APP_DIR;
  public static function init()
  {
    if (!file_exists(self::MAIN_CONFIG_FILE)) {
      file_put_contents(self::MAIN_CONFIG_FILE, "{}");
    }
    self::$translations = self::getConfig(self::MAIN_CONFIG_FILE);
    foreach ((self::assetsMap(self::APP_DIR, 1) ?: []) as $mod) {
      $rpath = self::APP_DIR . $mod . "/i18n.json";
      if (file_exists($rpath)) {
        self::$translations = array_merge(self::$translations, self::getConfig($rpath));
      } else {
        foreach ((self::assetsMap(self::APP_DIR . $mod, 1) ?: []) as $file) {
          if (str_starts_with($file, "i18n_") && str_ends_with($file, ".json")) {
            $rpath = self::APP_DIR . $mod . "/" . $file;
            self::$translations = array_merge(self::$translations, self::getConfig($rpath));
          }
        }
      }
    }
    Site::setTranslator(new Translator(self::$translations));
  }

  private static function getConfig($path)
  {
    return json_decode(file_get_contents($path), true);
  }

  private static function assetsMap($source_dir, $directory_depth = 0, $hidden = FALSE)
  {
    if ($fp = @opendir($source_dir)) {
      $filedata   = array();
      $new_depth  = $directory_depth - 1;
      $source_dir = rtrim($source_dir, '/') . '/';

      while (FALSE !== ($file = readdir($fp))) {
        // Remove '.', '..', and hidden files [optional]
        if (!trim($file, '.') or ($hidden == FALSE && $file[0] == '.')) {
          continue;
        }

        if (($directory_depth < 1 or $new_depth > 0) && @is_dir($source_dir . $file)) {
          $filedata[$file] = assetsMap($source_dir . $file . '/', $new_depth, $hidden);
        } else {
          $filedata[] = $file;
        }
      }

      closedir($fp);
      return $filedata;
    }
    return FALSE;
  }
}
