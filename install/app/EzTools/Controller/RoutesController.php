<?php

/*
 * * 2021-08-16
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\EzTools\Controller;

use App\EzTools\Model\Routes;

class RoutesController extends \Core\Controller {

  protected $template = "admin/routes.html.twig";

  //protected $authorized = ["ADM", "DEV"];
  //--------------------------------------------------------------------//
  // VUES
  //--------------------------------------------------------------------//

  public function render($datas = []) {

    $this->datas['modules'] = Routes::getModules();

    $this->datas['routes'] = Routes::getRoutesFromJson();
    return $this->display();
  }

  /**
   * Retourne les infos d'une route 
   * @param $_POST['alias']
   * @return array
   */
  public function getRouteByAlias($datas = []) {
    $alias = getPost('alias');
    if (!$alias)
      return errorResponse($alias, "Alias manquant", 404);

    $route = Routes::getRouteByAlias($alias);
    return successResponse($route, "Route");
  }

  /**
   * 
   * @return type
   */
  public function getAppControllers($datas = []) {
    $app = getPost('app');
    $ctrl = Routes::getController($app);
    return successResponse($ctrl, "Controllers");
  }

  /**
   * Retourne la liste des méthode d'une class (controller)
   * @param type $datas
   * @return type
   */
  public function getControllerFunctions($datas = []) {
    $app = getPost('app');
    $ctrl = getPost('ctrl');
    $functions = Routes::getControllerFunctions($app, $ctrl);
    return successResponse($functions, "Fonctions");
  }

  /**
   * 
   * @param type $datas
   * @return type
   */
  public function ajax_saveRoute($datas = []) {
    $returndatas = [];
    $url = getPost('url', "/");
    $oldUrl = getPost('oldurl', "/");
    $alias = getPost('alias', "alias");
    $template = getPost('template');
    $fallback = getPost('fallback');
    $m = getPost('m');
    $c = getPost('c');
    $f = getPost('f');

    //Si l'ancienne URL n'est pas la même que la "nouvelle", il faut supprimer l'ancienne
    if ($oldUrl !== $url) {
      $returndatas['DELETE_OLD'] = Routes::deleteRoute($url);
    }
    $returndatas['SAVE'] = Routes::saveRoute($url, $alias, $m, $c, $f, $template, $fallback);
    return successResponse($returndatas, "saveRoute");
  }

  /**
   * 
   * @param type $datas
   * @return type
   */
  public function ajax_deleteRoute($datas = []) {
    $returndatas = [];
    $url = getPost('url', "/");
    $returndatas['DELETE'] = Routes::deleteRoute($url);
    return successResponse($returndatas, "deleteRoute");
  }

}
