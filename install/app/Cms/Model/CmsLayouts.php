<?php

/**
 * CmsMenuItems.php
 * 
 * */

namespace App\Cms\Model;

use Core\SQLBuilder;

class CmsLayouts extends \Core\Db {

  public static $tbl = 'cms_layouts';
  public static $pkey = 'id';
  public const ALIAS = 'CMS_L'; 

  public static function prepareQueryByFilters($filtres = []) {
    $query = new SQLBuilder([
      "table" => self::$tbl." ".self::ALIAS,
    ]);

    return $query;
  }

  public static function getListByFilters($tableParameters = [], $filtres = []) {
    $query = self::prepareQueryByFilters($filtres);
    $query->setLimit($tableParameters["start"] ?? null);
    $query->setOffset($tableParameters["length"] ?? null);

    $query->setColonnes([self::ALIAS.".*"]);

    $orderCol = $tableParameters["orderCol"] ?? null;
    $direction = $tableParameters["direction"] ?? null;

    $orderableColumns = [
      "libelle" => self::ALIAS.".libelle",
      "template_path" => self::ALIAS.".template_path",
      "template_back_path" => self::ALIAS.".template_back_path",
      "type" => self::ALIAS.".type",
      "id" => self::ALIAS.".id",
    ];
    if (in_array($orderCol, array_keys($orderableColumns)) && in_array($direction, ["ASC","DESC"])) {
      $orderCol = $orderableColumns[$orderCol];
      $query->addOrderBy($orderCol." ".$direction);
    }

    if ($orderCol && $direction) {
      $query->addOrderBy($orderCol." ".$direction);
    }

    return self::db_all($query->getSql(), $query->getValues());
  }

  public static function getCountByFilters($filtres = []) {
    $query = self::prepareQueryByFilters($filtres);
    $query->setColonnes(["COUNT(*)"]);
    return intval(self::db_one_col($query->getSql(), $query->getValues()));
  }

  //------------ FIN CLASS ------------------//
}
