<?php

namespace App\Auth\Controller;

use Core\Db\User;
use Core\Response\HtmlResponse;
use Core\Response\JsonResponse;

class PasswordController extends \Core\Controller
{

  protected $template = "auth/changePassword.html.twig";

  public function render($datas = []): HtmlResponse
  {
    return $this->display();
  }

  public function getCompte($datas = []): HtmlResponse
  {
    return $this->display();
  }

  public function ajax_save(): JsonResponse
  {
    $pass = getPost('password');
    $pass2 = getPost('password2');
    $userid = $this->user->getId();
    $error = NULL;
    if ($userid && $pass) {
      if ($pass == $pass2) {

        $datas['success'] = User::updatePass($pass, $userid);
      } else {
        $error = "Mots de passe différents";
      }
    } else {
      $error = "Vous devez remplir tous les champs";
    }
    if ($error) return errorResponse([], $error);
    return successResponse([]);
  }
}
