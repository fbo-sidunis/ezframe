<?php

namespace App\Home\Controller;

class HomeController extends \Core\Controller
{
  protected $authorized = [];
  public function render()
  {
    return $this->display();
  }
}
