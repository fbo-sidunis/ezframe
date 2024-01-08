<?php

namespace App\Demo\Command;

class DemoCommand extends \Core\CommandHandler
{
  public function execute()
  {
    $nom = $this->getOption("nom");
    dd($this->getOptions());
    echo "Hello " . $nom . PHP_EOL;
  }
}
