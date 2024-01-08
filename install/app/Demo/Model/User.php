<?php

namespace App\Demo\Model;

use Model\User as ModelUser;

class User extends ModelUser
{
  protected static function getColumns()
  {
    return [
      self::al("id"),
      self::al("actif"),
    ];
  }
}
