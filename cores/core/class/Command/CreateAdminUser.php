<?php

namespace Core\Command;

use Core\Common\Site;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class CreateAdminUser extends \Core\CommandHandler
{

  protected $logger;

  function __construct()
  {
    $this->logger = new Logger("createAdminUser", [
      new RotatingFileHandler(
        filename: ROOT_DIR . "var/log/createAdminUser/createAdminUser.log",
        maxFiles: 2,
        level: Logger::DEBUG,
      ),
      new StreamHandler(
        stream: "php://stdout",
        level: Logger::DEBUG,
      ),
    ]);
  }

  public function execute()
  {
    $email = $this->getOption("mail") ?? null;

    if (!$email) {
      $this->logger->error("Parameter --mail is required");
      return;
    }

    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $password = '';
    for ($i = 0; $i < 32; $i++) {
      $password .= $characters[rand(0, $charactersLength - 1)];
    }
    $userObjectClass = "\\Model\\DataObject\\User";
    $roleObjectClass = "\\Model\\DataObject\\ConfigRole";
    $userRoleObjectClass = "\\Model\\DataObject\\ConfigUserRole";
    if (!file_exists(ROOT_DIR . "model/DataObject/User.php")) {
      $this->logger->error("User object class not found, create it first with command: php bin/ezframe core:generateDataObject");
      return;
    }
    if (!file_exists(ROOT_DIR . "model/DataObject/ConfigRole.php")) {
      $this->logger->error("Role object class not found, create it first with command: php bin/ezframe core:generateDataObject");
      return;
    }
    if (!file_exists(ROOT_DIR . "model/DataObject/ConfigUserRole.php")) {
      $this->logger->error("UserRole object class not found, create it first with command: php bin/ezframe core:generateDataObject");
      return;
    }
    $user = $userObjectClass::create([
      "mail" => $email,
      "password" => password_hash($password, PASSWORD_DEFAULT),
      "prenom" => $this->getOption("prenom") ?? "Admin",
      "nom" => $this->getOption("nom") ?? "Admin",
      "active" => "Y",
    ]);

    $role = $roleObjectClass::getOneByFilters([
      "code" => "ADM",
    ]) ?? $roleObjectClass::create([
      "code" => "ADM",
      "libelle" => "Administrateur",
    ]);

    $userRoleObjectClass::create([
      "user_id" => $user->getId(),
      "role" => $role->getCode()
    ]);

    echo "User created with success" . PHP_EOL;
    echo "Login: $email" . PHP_EOL;
    echo "Password: $password" . PHP_EOL;
  }
}
