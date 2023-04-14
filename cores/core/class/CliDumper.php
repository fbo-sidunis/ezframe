<?php
namespace Core;

use Symfony\Component\VarDumper\Dumper\CliDumper as DumperCliDumper;

class CliDumper extends DumperCliDumper{
  public function setLine($name = null,$line=0){
    if ($name){
      $this->line = $this->style('meta', $name). ($line ? ' on line '.$this->style('meta', $line) : "").':'.PHP_EOL;
    }else{
      $this->line = "";
    }
  }
}