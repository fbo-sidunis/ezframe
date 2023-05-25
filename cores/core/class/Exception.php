<?php

namespace Core;

class Exception extends \Exception
{
  private $_data = null;

  public function __construct($message, $data = null, $code = 0, Exception $previous = null)
  {
    $this->_data = $data;
    parent::__construct($message, $code, $previous);
  }

  public function getData()
  {
    return $this->_data;
  }

  public function __toString()
  {
    return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
  }
}
