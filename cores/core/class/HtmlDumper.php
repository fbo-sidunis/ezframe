<?php
namespace Core;

use Symfony\Component\VarDumper\Dumper\HtmlDumper as DumperHtmlDumper;

class HtmlDumper extends DumperHtmlDumper{
  public function setLine($name = null,$line=0){
    if ($name){
      $this->line = $this->style('meta', $name). ($line ? ' on line '.$this->style('meta', $line) : "").':<br>';
    }else{
      $this->line = "";
    }
  }
}