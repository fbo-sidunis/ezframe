<?php

namespace App\Demo\Model\DataObject;

use Model\DataObject\User as DataObjectUser;

class User extends DataObjectUser
{
  public function getFullName(): string
  {
    return $this->getPrenom() . " " . $this->getNom();
  }
}
