<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Admin\Controller;

use App\Admin\Model\Roles;
use App\Admin\Model\Users;
use App\Admin\Model\UsersRoles;
use Core\Response\HtmlResponse;
use Core\Response\JsonResponse;

class UserController extends \Core\Controller
{

  protected $template = "admin/users.html.twig";
  protected $authorized = ["ADM"];

  //--------------------------------------------------------------------//
  // VUES
  //--------------------------------------------------------------------//

  public function render(): HtmlResponse
  {
    $this->setData('listUsers', Users::getUser());
    $this->setData('listRoles', Roles::getAll());
    return $this->display();
  }

  public function ajax_insertUser(): JsonResponse
  {
    $name = getPost("name");
    $firstname = getPost("firstname");
    $mail = getPost("mail");
    $pwd = getPost("pwd");
    $lastupdate_by = $this->user->getId();
    $arrRoles = !empty($_POST['roles']) ? json_decode($_POST['roles'], true) : null;

    $id_user = Users::insertNewUser($name, $firstname, $mail, $pwd, $lastupdate_by);
    $result['insert'] = $id_user;
    $result['removeRole'] = Users::removeRoleUser($id_user);
    $result['addRole'] = Users::addRolesUser($id_user, $arrRoles, $lastupdate_by);

    return successResponse($result);
  }

  public function ajax_getUsers(): JsonResponse
  {
    $search = getPost("search");
    $result = Users::getBy_Txt($search);
    return \successResponse($result);
  }

  public function ajax_activate(): JsonResponse
  {
    $user_id = getPost("user_id");
    $etat = getPost("etat", 'N');
    $result = Users::activate($user_id, $etat);
    return successResponse($result);
  }

  public function ajax_delete(): JsonResponse
  {
    $user_id = getPost("user_id");
    $result = Users::deleteUser($user_id);
    return successResponse($result);
  }

  public function ajax_getUserById(): JsonResponse
  {
    $user_id = getPost("user_id");
    $result['USER'] = Users::getUserById($user_id);
    $result['ROLE'] = UsersRoles::getRoles($user_id);
    return jsonResponse($result);
  }

  public function ajax_updateUser(): JsonResponse
  {
    $user_id = getPost("user_id");
    $nom = getPost("name");
    $prenom = getPost("firstname");
    $mail = getPost("mail");

    $lastupdate_by = $this->user->getId() ?? 0;
    $arrRoles = !empty($_POST['roles']) ? json_decode($_POST['roles'], true) : null;

    $result['update'] = Users::updateUser($user_id, $nom, $prenom, $mail);
    $result['removeRole'] = Users::removeRoleUser($user_id);
    $result['insertRole'] = Users::addRolesUser($user_id, $arrRoles, $lastupdate_by);
    return successResponse($result);
  }

  public function ajax_updatePass(): JsonResponse
  {
    $user_id = getPost("user_id");;
    $pass = getPost("pass");;
    $result = Users::updatePass($pass, $user_id);
    return successResponse($result);
  }

  public function sendNewPass(): JsonResponse
  {

    $pass = getPost("pass");
    $mailD = getPost("mail");
    $mailE = 'no-reply@groupefbo.com';
    $subject = 'Réinitialisation de votre mot de passe';
    $message = '
              <html>
               <body>
                <p>Bonjour,</p>
                <p>Voici votre nouveau mot de passe</p>
                <p>' . $pass . '</p>
                <p><a href="' . DOMAIN . '">Connexion</a></p>
               </body>
              </html>
              ';

    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=utf-8';
    $headers[] = 'From: ' . $mailE . '<' . $mailE . '>';
    $headers[] = 'Date: ' . date("r (T)");

    mail($mailD, $subject, $message, implode("\r\n", $headers));
    return successResponse([]);
  }
}
