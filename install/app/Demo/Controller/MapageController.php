<?php

/*
 * * 2021-08-16
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Demo\Controller;

use App\Demo\Model\Maclass;

class MapageController extends \Core\Controller {

  protected $template = "demo/exemple.html.twig";
  protected $tmpl_ajax = "demo/reponse_ajax.html.twig";

  //protected $authorized = ["ADM", "DEV"];
  //--------------------------------------------------------------------//
  // VUES
  //--------------------------------------------------------------------//

  public function render($datas = []) {

    $this->datas['USERS'] = Maclass::getUsers();

    return $this->display();
  }

  /**
   * Retounr les Infos d'un user au format json
   * @param type $datas
   * @return array
   */
  public function ajax_Exemple($datas = []) {
    $id = getPost('id');
    if (!$id) {
      return errorResponse($id, "id manquant", 404);
    }

    $user = MaClass::getById($id);
    return successResponse($user, "Le user");
  }

  /**
   * Retounr les Infos d'un user en utilisant un template twig
   * @param type $datas
   * @return array
   */
  public function ajax_Exemple2($datas = []) {
    $id = getPost('id');
    if (!$id) {
      return errorResponse($id, "id manquant", 404);
    }

    $datas['USER'] = MaClass::getById($id);
    $HTML = $this->oTwig->render($this->tmpl_ajax, $datas);
    return successResponse($HTML, "Le user");
  }

}
