<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Core;

class CommandHandler
{
  public static function handle()
  {
    // On récupère le premier argument de la commande
    $command = $_SERVER['argv'][1] ?? null;
    //On sépare les arguments de la commande
    $commandComponents = explode(":", $command);
    //module
    $module = ucfirst($commandComponents[0] ?? "");
    if (!$module) {
      throw new \Exception("Module inexistant");
    }
    //action
    $action = ucfirst($commandComponents[1] ?? "");
    if (!$action) {
      throw new \Exception("Action inexistante");
    }
    if ($module === "Core") {
      $classe = "\\Core\\Command\\" . $action;
    } else {
      $classe = "\\App\\" . $module . "\\Command\\" . $action;
    }
    if (!class_exists($classe)) {
      throw new \Exception("Classe $classe inexistante");
    }
    //Si la classe n'étends pas celle ci, on lève une exception
    if (!is_subclass_of($classe, self::class)) {
      throw new \Exception("La classe donnée n'étends pas CommandHandler");
    }
    //Si la classe n'a pas la fonction publique statique execute on lève une exception
    if (!method_exists($classe, "execute")) {
      throw new \Exception("La classe donnée n'a pas de fonction execute");
    }
    $handler = new $classe;
    $handler->execute();
  }

  protected function getOption($name)
  {
    $options = getopt("", [$name . "::"]);
    return $options[$name] ?? null;
  }
}
