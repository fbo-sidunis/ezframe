<?php

namespace App\Auth\Controller;

use Core\Db\User;

class CompteController extends \Core\Controller {

  public function render() {
    return $this->display();
  }

  public function getCompte() {
    return $this->display();
  }

  public function ajax_save() {
    $datas = [];
    $nom = getPost('nom');
    $prenom = getPost('prenom');
    $mail = getPost('mail');
    $userid = $this->user->getId();
    $error = NULL;
    if ($userid) {
        $datas['success'] = User::updateUser($userid, $nom, $prenom, $mail);
    } else {
      return errorResponse([],"Vous devez remplir tous les champs");
    }

    $datas['error'] = $error;
    return successResponse($datas);
  }

}

?>