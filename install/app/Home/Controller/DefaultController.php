<?php

namespace App\Home\Controller;

use Core\Response\HtmlResponse;

class DefaultController extends \Core\Controller
{
    protected $template = "home/home.html.twig";
    protected $authorized = [];

    public function render(): HtmlResponse
    {
        return $this->display();
    }
}
