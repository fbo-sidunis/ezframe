<?php 

namespace App\Cms;

use Core\Twig\Extension;

class Controller extends \Core\Controller {
  function __construct($twig = NULL, $loader = NULL, $route = NULL, $datas = []) {
    parent::__construct(...func_get_args());
    $this->loader->addPath(__DIR__ . '/templates');
    Extension::addAssetPath((__DIR__ . '/assets'));
  }
}