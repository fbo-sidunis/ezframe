<?php

namespace App\Auth\Controller;

use Core\Annotation\Route;
use Core\Db\User;
use Core\Response\HtmlResponse;
use Core\Response\JsonResponse;

class CompteController extends \Core\Controller
{

  public function render(): HtmlResponse
  {
    return $this->display();
  }

  public function getCompte(): HtmlResponse
  {
    return $this->display();
  }

  public function ajax_save(): JsonResponse
  {
    $datas = [];
    $nom = getPost('nom');
    $prenom = getPost('prenom');
    $mail = getPost('mail');
    $userid = $this->user->getId();
    $error = NULL;
    if ($userid) {
      $datas['success'] = User::updateUser($userid, $nom, $prenom, $mail);
    } else {
      return errorResponse([], "Vous devez remplir tous les champs");
    }

    $datas['error'] = $error;
    return successResponse($datas);
  }
}
