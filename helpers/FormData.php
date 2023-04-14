<?php

namespace Helper;

use Closure;

class FormData {

  protected $association = [];
  protected $datas = null;
  protected $formName = "form";
  protected $idName = "id";
  protected $idValue = null;
  protected $errors = [];

  function __construct($parameters = []){
    foreach (($parameters ?? []) as $parameter=>$value){
      $setter = "set".ucfirst($parameter);
      if (method_exists($this,$setter)) $this->$setter($value);
    }
  }

  public function getId(){
    return $this->idValue ?: $this->getFormDatas()[$this->idName] ?? null;
  }

  public function getFormDatas(){
    return $_POST[$this->formName] ?? [];
  }

  public function getDatas($force = false){
    if ($this->datas === null || $force) {
      $this->generateDatas();
    }
    return $this->datas;
  }
  public function getData($name,$force = false){
    if ($this->datas === null || $force) {
      $this->generateDatas();
    }
    return $this->datas[$name] ?? null;
  }
  public function getFormData($name){
    return $this->getFormDatas()[$name] ?? null;
  }

  public function generateDatas(){
    $datas = [];
    $formDatas = $this->getFormDatas();
    foreach ($this->association as $formName => $dbCol) {
      if ($dbCol instanceof Closure) {
        $dbCol($formDatas[$formName] ?? null,$datas,$formDatas);
      } else {
        $datas[$dbCol] = $formDatas[$formName] ?? null;
      }
    }
    foreach ($datas as $key => &$value) {
      if (is_string($value)) {
        $value = trim($value);
      }
    }
    $this->datas = $datas;
  }

  /**
   * Get the value of association
   */ 
  public function getAssociation()
  {
    return $this->association;
  }

  /**
   * Set the value of association
   *
   * @return  self
   */ 
  public function setAssociation($association)
  {
    $this->association = $association;

    return $this;
  }

  /**
   * Get the value of idName
   */ 
  public function getIdName()
  {
    return $this->idName;
  }

  /**
   * Set the value of idName
   *
   * @return  self
   */ 
  public function setIdName($idName)
  {
    $this->idName = $idName;

    return $this;
  }

  /**
   * Get the value of formName
   */ 
  public function getFormName()
  {
    return $this->formName;
  }

  /**
   * Set the value of formName
   *
   * @return  self
   */ 
  public function setFormName($formName)
  {
    $this->formName = $formName;

    return $this;
  }

  public function addError($name,$error){
    $this->errors[$name] = $error;
  }

  public function getErrors(){
    return $this->errors;
  }

  /**
   * Get the value of idValue
   */ 
  public function getIdValue()
  {
    return $this->idValue;
  }

  /**
   * Set the value of idValue
   *
   * @return  self
   */ 
  public function setIdValue($idValue)
  {
    $this->idValue = $idValue;

    return $this;
  }
}