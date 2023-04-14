<?php

namespace Core\Start;

use Core\Common\Site;
use Core\Translator;

class I18n{
  public static $translations;
  public static function init(){
    self::$translations = arrayFromJson( ROOT_DIR . "config/i18n.json");
    foreach ((assetsMap(APP_DIR,1) ?: []) as $mod){
      $rpath = APP_DIR.$mod."/i18n.json";
      if (file_exists($rpath)){
        self::$translations = array_merge(self::$translations,arrayFromJson( $rpath));
      }else{
        foreach((assetsMap(APP_DIR.$mod,1) ?: []) as $file){
          if (str_starts_with($file,"i18n_") && str_ends_with($file,".json")){
            $rpath = APP_DIR.$mod."/". $file;
            self::$translations = array_merge(self::$translations,arrayFromJson($rpath));
          }
        }
      }
    }
    Site::setTranslator(new Translator(self::$translations));
  }
}