<?php

namespace App\Auth\Controller;

use Core\Db\User;
use Core\User as CoreUser;
use Helper\Email;

class LoginController extends \Core\Controller {

  protected $template = "auth/login.html.twig";
  public function render(){
    if (!empty($_COOKIE["redirect_url"]) && empty($_COOKIE["redirect_url_no_try_again"])){
      $url = $_COOKIE["redirect_url"];
      if ($this->user){
        return redirectURL($url);
      }
    }
    if ($this->user) $this->route->redirect("home");
    return $this->display();
  }

  public function connexion(){
    $login = strval(filter_input(INPUT_POST, 'login'));
    $pwd = strval(filter_input(INPUT_POST, 'pass'));
    $con = User::login($login, $pwd);
    if (!empty($con)) {
      $idUser = $con['id'];
      CoreUser::init($idUser);
      \Core\Db\Log::insertLog($login, 'SUCCESS', 'CONNEXION');
    } else {
      \Core\Db\Log::insertLog($login, 'ERROR', 'CONNEXION');
      CoreUser::clearSession();
      return errorResponse(["is_logged" => false],"Login et/ou mot de passe invalide");
    }

    return successResponse(["is_logged" => true]);
  }

  /**
   *
   * @return boolean
   */
  public function logout() {
    CoreUser::clearSession();
    return $this->route->redirect("login");
  }

  public function forgotPassword(){
    $result = [];
    $mail = getPost('mail');
    if (!$mail) return errorResponse([],"Aucun mail renseigné");
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

?>