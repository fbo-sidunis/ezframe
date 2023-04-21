<?php

namespace Core\Start;

use Core\Common\Site;
use Core\Translator;

class I18n
{
  public static $translations;
  public const MAIN_CONFIG_FILE = ROOT_DIR . "config/i18n.json";
  public static function init()
  {
    if (!file_exists(self::MAIN_CONFIG_FILE)) {
      file_put_contents(self::MAIN_CONFIG_FILE, "{}");
    }
    self::$translations = arrayFromJson(self::MAIN_CONFIG_FILE);
    foreach ((assetsMap(APP_DIR, 1) ?: []) as $mod) {
      $rpath = APP_DIR . $mod . "/i18n.json";
      if (file_exists($rpath)) {
        self::$translations = array_merge(self::$translations, arrayFromJson($rpath));
      } else {
        foreach ((assetsMap(APP_DIR . $mod, 1) ?: []) as $file) {
          if (str_starts_with($file, "i18n_") && str_ends_with($file, ".json")) {
            $rpath = APP_DIR . $mod . "/" . $file;
            self::$translations = array_merge(self::$translations, arrayFromJson($rpath));
          }
        }
      }
    }
    Site::setTranslator(new Translator(self::$translations));
  }
}
