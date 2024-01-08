<?php

namespace App\Auth\Controller;

use Core\Annotation\Route;
use Core\Db\User;
use Core\Exception;
use Core\Response\FileResponse;
use Core\Response\HtmlResponse;
use Core\Response\JsonResponse;
use Core\User as CoreUser;
use Helper\Email;

class LoginController extends \Core\Controller
{

  protected $template = "auth/login.html.twig";


  public function render(): HtmlResponse
  {
    if (!empty($_COOKIE["redirect_url"]) && empty($_COOKIE["redirect_url_no_try_again"])) {
      $url = $_COOKIE["redirect_url"];
      if ($this->user) {
        return redirectURL($url);
      }
    }
    if ($this->user) $this->route->redirect("home");
    return $this->display();
  }

  public function connexion(): JsonResponse
  {
    $login = strval(filter_input(INPUT_POST, 'login'));
    $pwd = strval(filter_input(INPUT_POST, 'pass'));
    try {
      $user = User::login($login, $pwd);
      if (!empty($user)) {
        $idUser = $user['id'];
        CoreUser::init($idUser);
        \Core\Db\Log::insertLog($login, 'SUCCESS', 'CONNEXION');
      } else {
        \Core\Db\Log::insertLog($login, 'ERROR', 'CONNEXION');
        CoreUser::clearSession();
        return errorResponse(["is_logged" => false], "Login et/ou mot de passe invalide");
      }
      return successResponse(["is_logged" => true]);
    } catch (Exception $e) {
      return errorResponse(["is_logged" => false], $e->getMessage());
    }
  }

  /**
   *
   * @return boolean
   */
  public function logout(): void
  {
    CoreUser::clearSession();
    $this->route->redirect("login");
  }

  public function forgotPassword(): JsonResponse
  {
    $result = [];
    $mail = getPost('mail');
    if (!$mail) return errorResponse([], "Aucun mail renseigné");
    $oUserCnx = new User;
    $res = $oUserCnx->renewPass($mail);

    $result['UPDATE'] = $res;
    $oMail = new Email([
      "to" => $mail,
      "subject" => 'Réinitialisation password',
      "body" => "Bonjour,<br>Votre mot de passe a été réinitialisé : " . $res['NEW_PASS'],
      "altBody" => "Votre mot de passe a été réinitialisé",
    ]);
    $result['MAIL'] = $oMail->send();
    return successResponse([]);
  }
}
