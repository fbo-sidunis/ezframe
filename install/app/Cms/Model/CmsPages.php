<?php

/**
 * CmsMenuItems.php
 * 
 * */

namespace App\Cms\Model;

use Core\SQLBuilder;

class CmsPages extends \Core\Db {

  public static $tbl = 'cms_pages';
  public static $pkey = 'id';

  public static function prepareQueryByFilters($filtres = []) {
    $query = new SQLBuilder([
      "table" => self::$tbl." CMS_P",
    ]);

    //-----------------------------------------------------
    //Application filtres ICI

    //-----------------------------------------------------

    return $query;
  }

  public static function getListByFilters($tableParameters = [], $filtres = []) {
    $query = self::prepareQueryByFilters($filtres);
    $query->setLimit($tableParameters["start"] ?? null);
    $query->setOffset($tableParameters["length"] ?? null);

    $query->setColonnes(["CMS_P.*"]);

    $orderCol = $tableParameters["orderCol"] ?? null;
    $direction = $tableParameters["direction"] ?? null;
    $orderableColumns = [
      "id_layout" => "CMS_P.id_layout",
    ];
    if (in_array($orderCol, array_keys($orderableColumns)) && in_array($direction, ["ASC","DESC"])) {
      $orderCol = $orderableColumns[$orderCol];
      $query->addOrderBy($orderCol." ".$direction);
    }
    return self::db_all($query->getSql(), $query->getValues());
  }

  public static function getCountByFilters($filtres = []) {
    $query = self::prepareQueryByFilters($filtres);
    $query->setColonnes(["COUNT(*)"]);
    return intval(self::db_one_col($query->getSql(), $query->getValues()));
  }

  /**
   * Transforme un array en tree
   * @param array $elements
   * @param type $parentId
   * @return type
   */
  public static function buildTree(array &$elements, $parentId = 0) {
    $branch = array();

    foreach ($elements as $element) {
      if ($element['id_parent'] == $parentId) {
        $children = self::buildTree($elements, $element['id']);
        if ($children) {
          $element['children'] = $children;
        }
        $branch[$element['id']] = $element;
        unset($elements[$element['id']]);
      }
    }
    return $branch;
  }

  //------------ FIN CLASS ------------------//
}
