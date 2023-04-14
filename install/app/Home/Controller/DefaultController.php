<?php

namespace App\Home\Controller;
class DefaultController extends \Core\Controller {
    protected $template = "home/home.html.twig";
    protected $authorized = [];

    public function render() {
        return $this->display();
    }

}

