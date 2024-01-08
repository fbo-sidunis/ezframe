<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\EzTools\Controller;

class DatabaseController extends \Core\Controller
{

  private $allowedIP = [
    "127.0.0.1", //local
    "79.84.73.151", //mathieu
    "193.248.50.123", //fbo
    "192.168.111.26", //fbo vpn
    "10.85.70.15", //fbo vpn
    "86.236.70.152", //Tuan

  ];

  public function render(): void
  {
    if (!in_array($_SERVER["REMOTE_ADDR"], $this->allowedIP)) exit;
    ini_set('display_errors', FALSE);
    include __DIR__ . "/../include/adminer.php";
    echo "<link rel=\"stylesheet\" href=\"" . relativePathUrl(__DIR__ . "/../include") . "adminer.css\">" . PHP_EOL;
    exit;
  }
}
