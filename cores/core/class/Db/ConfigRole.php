<?php

/**
 * config_roles.class.php
 *
 * */
namespace Core\Db;
class ConfigRole extends \Core\Db {

  public static $tbl = 'config_role';
  public static $pkey = 'code';
  protected $code = null;
  protected $libelle = null;
  protected $defaut = null;
  protected $actif = null;

  function __construct() {
    // parent::__construct();
  }

  public function set_code($pArg = NULL) {
    $this->code = $pArg;
  }

  public function set_libelle($pArg = NULL) {
    $this->libelle = $pArg;
  }

  public function set_defaut($pArg = NULL) {
    $this->defaut = $pArg;
  }

  public function set_actif($pArg = NULL) {
    $this->actif = $pArg;
  }

  public function get_code() {
    return (string) $this->code;
  }

  public function get_libelle() {
    return (string) $this->libelle;
  }

  public function get_defaut() {
    return (string) $this->defaut;
  }

  public function get_actif() {
    return (string) $this->actif;
  }

//------------ FIN CLASS ------------------//
}
