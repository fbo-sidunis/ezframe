<?php

namespace App\EzTools\Controller;

class ErrorController extends \Core\Controller {
  public function pathError(){
    $filePath = getGet("path");
    header("Content-Type: application/javascript");
    echo "console.error(\"FILE \\\"$filePath\\\" NOT FOUND\");";
    die;
  }
}
