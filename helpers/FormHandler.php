<?php

namespace Helper;

use Exception;
use Helper\FormData;

class FormHandler{

  private ?FormData $formData = null;
  private string $idModificateur;
  private string $formName;
  private string $modelClass;
  private array $association = [];
  private string $idName = "id";
  private $idValue = null;

  function __construct(
    string $idModificateur,
    string $formName,
    string $modelClass,
    array $association = [],
    string $idName = "id",
    $idValue = null
  ){
    $this->idModificateur = $idModificateur;
    $this->formName = $formName;
    $this->modelClass = $modelClass;
    $this->association = $association;
    $this->idName = $idName;
    $this->idValue = $idValue;
  }

  public function addOrUpdate($forceReset = false){

    $id = $this->getFormData()->getId();
    $returnData = [];
    $idModificateur = $this->idModificateur;
    $datas = $this->getFormData()->getDatas($forceReset);
    $datas["lastupdate_by"] = $idModificateur;

    if($id){
      if (!$this->modelClass::getBy($id)) {
        throw new Exception("Cette entrée n'existe pas");
      }
      $returnData['UPDATE'] = $this->modelClass::updateBy($id,$datas);
    }else{
      $datas["created_by"] = $idModificateur;
      $id = $this->modelClass::add($datas);
      $returnData['INSERT'] = $id;
    }

    $returnData["ID"] = $id;
    return $returnData;
  }

  public function getFormData(){
    if (!$this->formData){
      $this->formData = new FormData([
        "formName" => $this->formName,
        "association" => $this->association,
        "idName" => $this->idName,
        "idValue" => $this->idValue,
      ]);
    }
    return $this->formData;
  }

  public function getData(string $key){
    return $this->getFormData()->getData($key);
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

    public function getId(){
      return $this->getFormData()->getId();
    }
}