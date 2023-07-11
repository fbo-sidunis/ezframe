<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Core;

use Exception;

class CommandHandler
{
  protected $options = [];
  /**
   * Permet de gérer les commandes
   * Ne pas utiliser en dehors de bin/ezframe
   * @return void 
   * @throws Exception 
   */
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
    $handler->retreiveAllOptionsFromCommand();
    $handler->execute();
  }

  /**
   * Permet de récupérer toutes les options de la commande
   * @return void
   */
  public function retreiveAllOptionsFromCommand()
  {
    $options = [];
    foreach ($_SERVER['argv'] as $key => $value) {
      if (preg_match("/^--([a-z]+)=([a-z0-9]+)$/i", $value, $matches)) {
        $options[$matches[1]] = $matches[2];
      }
    }
    $this->options = $options;
  }

  protected function getOption($name)
  {
    return $this->options[$name] ?? null;
  }

  protected function getOptions()
  {
    return $this->options;
  }

  /**
   * Permet de récupérer les arguments de la commande
   * @return array 
   */
  protected function getArguments()
  {
    return $_SERVER['argv'] ?? [];
  }

  /**
   * Permet de récupérer un argument de la commande
   * @param mixed $index 
   * @return string|false|null 
   */
  protected function getArgument($index)
  {
    return $this->getArguments()[$index] ?? null;
  }

  /**
   * Permet de récupérer le nom de la commande
   * @return string 
   */
  protected function getCommandName()
  {
    return $_SERVER['argv'][1] ?? null;
  }

  /**
   * Permet de récupérer le nom du module de la commande
   * @return string 
   */
  protected function getModuleName()
  {
    $command = $this->getCommandName();
    $commandComponents = explode(":", $command);
    return ucfirst($commandComponents[0] ?? "");
  }

  /**
   * Permet de récupérer le nom de l'action de la commande
   * @return string 
   */
  protected function getActionName()
  {
    $command = $this->getCommandName();
    $commandComponents = explode(":", $command);
    return ucfirst($commandComponents[1] ?? "");
  }

  /**
   * Permet de récupérer le nom de la classe de la commande
   * @return string 
   */
  protected function getClassName()
  {
    return get_class($this);
  }
}
