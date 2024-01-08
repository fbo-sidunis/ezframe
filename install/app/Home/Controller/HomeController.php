<?php

namespace App\Home\Controller;

use Core\Response\HtmlResponse;

class HomeController extends \Core\Controller
{
  protected $authorized = [];
  public function render(): HtmlResponse
  {
    return $this->display();
  }
}
