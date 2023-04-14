<?php
namespace Helper;

class Datatable
{
  /**
   * L'id de l'appel, sert uniquement à la librairie
   * @var int
   */
  protected $draw;

  /**
   * Début (offset) des résultats à afficher
   * @var int
   */
  protected $start;

  /**
   * Nombre (limit) de résultats à afficher
   * @var int
   */
  protected $length;

  /**
   * Liste des ordres appliqués au tableau
   * @var array
   */
  protected $order;

  /**
   * Les colonnes du tableau
   * @var array
   */
  protected $columns;

  /**
   * La colonne du tableau sélectionnée pour le tri
   * @var array
   */
  protected $column;

  /**
   * Le nom de la colonne sélectionné pour le tri
   * @var string
   */
  protected $orderCol;

  /**
   * Le sens du tri (ASC ou DESC)
   * @var string
   */
  protected $direction;

  /**
   * Données (duh)
   * @var array
   */
  protected $data;

  /**
   * Nombres de résultats total
   * @var int
   */
  protected $recordsTotal;

  /**
   * Nombres de résultats avec filtres appliqués
   * @var int
   */
  protected $recordsFiltered;

  /**
   * Terme recherché
   * @var string|null
   */
  protected $search;

  /**
   * Termes recherchés par colonne
   * @var string[]
   */
  protected $searches = [];

  function __construct(){
    $this->draw = intval(getRequest("draw", 0));
    $this->start = intval(getRequest("start", 0));
    $this->start = $this->start < 0 ? 0 : $this->start;
    $this->length = intval(getRequest("length", 10));
    $this->length = $this->length < 0 ? null : $this->length;
    $this->search = getRequest('search', [])["value"] ?? null;
    $this->order = getRequest("order", [])[0] ?? [];
    $this->columns = getRequest("columns", []);
    foreach($this->columns as $column){
      $this->searches[($column["name"] ?? null) ?: $column["data"]] = $column["search"]["value"] ?? null;
    }
    $this->column = $this->columns[$this->order["column"]] ?? [];
    $this->orderCol = ($this->column["data"] ?? null) ?: ($this->column["name"] ?? null) ?: NULL;
    $this->direction = strtoupper($this->order["dir"] ?? "ASC");
  }

  /**
   * Get the value of draw
   */ 
  public function getDraw()
  {
    return $this->draw;
  }

  /**
   * Get the value of start
   */ 
  public function getStart()
  {
    return $this->start;
  }

  /**
   * Get the value of length
   */ 
  public function getLength()
  {
    return $this->length;
  }

  /**
   * Get the value of order
   */ 
  public function getOrder()
  {
    return $this->order;
  }

  /**
   * Get the value of columns
   */ 
  public function getColumns()
  {
    return $this->columns;
  }

  /**
   * Get the value of column
   */ 
  public function getColumn()
  {
    return $this->column;
  }

  /**
   * Get the value of orderCol
   */ 
  public function getOrderCol()
  {
    return $this->orderCol;
  }

  /**
   * Get the value of direction
   */ 
  public function getDirection()
  {
    return $this->direction;
  }

  /**
   * Get the value of data
   */ 
  public function getData()
  {
    return $this->data;
  }

  /**
   * Set the value of data
   *
   * @return  self
   */ 
  public function setData($data)
  {
    $this->data = $data;

    return $this;
  }

  /**
   * Get the value of recordsTotal
   */ 
  public function getRecordsTotal()
  {
    return $this->recordsTotal;
  }

  /**
   * Set the value of recordsTotal
   *
   * @return  self
   */ 
  public function setRecordsTotal($recordsTotal)
  {
    $this->recordsTotal = intval($recordsTotal);

    return $this;
  }

  /**
   * Get the value of recordsFiltered
   */ 
  public function getRecordsFiltered()
  {
    return $this->recordsFiltered;
  }

  /**
   * Set the value of recordsFiltered
   *
   * @return  self
   */ 
  public function setRecordsFiltered($recordsFiltered)
  {
    $this->recordsFiltered = intval($recordsFiltered);

    return $this;
  }

  /**
   * Génère la réponse à la requête ajax
   * @param array $additionnalData [Les données qu'on veut rajouter au tableau retourné]
   * @return array 
   */
  public function getReturnData($additionnalData = [])
  {
    return [
      "data" => $this->getData(),
      "draw" => $this->getDraw(),
      "recordsTotal" => $this->getRecordsTotal(),
      "recordsFiltered" => $this->getRecordsFiltered(),
    ] + $additionnalData;
  }

  public function jsonResponse($additionnalData = [])
  {
    return jsonResponse($this->getReturnData($additionnalData));
  }

  public function getQueryDatas()
  {
    return [
      "start" => $this->getStart(),
      "length" => $this->getLength(),
      "orderCol" => $this->getOrderCol(),
      "direction" => $this->getDirection(),
    ];
  }

  /**
   * Get the value of search
   */ 
  public function getSearch()
  {
    return $this->search;
  }

  /**
   * Get termes recherchés par colonne
   *
   * @return  string[]
   */ 
  public function getSearches()
  {
    return $this->searches;
  }
}
