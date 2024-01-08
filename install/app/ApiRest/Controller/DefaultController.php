<?php

namespace App\Auth\Controller;

use Core\Db\User;
use Core\Response\JsonResponse;

class DefaultController extends \Core\Controller
{

  public function login(): JsonResponse
  {
    return successResponse();
  }
  public function api(): JsonResponse
  {
    return successResponse();
  }
}
