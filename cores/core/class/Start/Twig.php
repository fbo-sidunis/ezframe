<?php

namespace Core\Start;

use Browser;
use Core\Common\Site;
use Core\Twig\Extension;
use Core\Twig\TranslatorExtension;
use Exception;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Twig
{
  public static function init()
  {
    $templateDir = ROOT_DIR . TEMPLATES;
    try {
    } catch (Exception $e) {
      echo "Erreur !" . $e->getMessage();
      echo "TEMPLATES DIRECTORY :" . $templateDir;
    }
    try {
      $loader = new FilesystemLoader($templateDir);
      $options_twig = [];
      $options_twig['cache'] = false;
    } catch (Exception $e) {
      echo "Erreur !" . $e->getMessage();
      echo "TEMPLATES DIRECTORY :" . $templateDir;
    }

    if (defined('DEBUG') && DEBUG == true) {
      $options_twig['debug'] = true;
    }
    $twig = new Environment($loader, $options_twig);
    $browser = new Browser;
    $twigsGlobals = [
      "Route" => Site::getRouting(),
      "SITE" => ["CONFIG" => Site::getSiteConfig_()],
      "DOMAIN" => DOMAIN,
      "ROOT_URL" => ROOT_URL,
      "SITE_URL" => DOMAIN . ROOT_URL,
      "LIBRARY_URL" => LIBRARY_URL,
      "SITE_TITLE" => SITE_TITLE,
      "browser" => [
        "isMobile" => $browser->isMobile(),
        "isTablet" => $browser->isTablet(),
      ],
    ];
    array_map([$twig, "addGlobal"], array_keys($twigsGlobals), $twigsGlobals);
    $twig->addExtension(new Extension());
    $twig->addExtension(new TranslatorExtension());
    Site::setTwigEnvironnment($twig);
  }
}
