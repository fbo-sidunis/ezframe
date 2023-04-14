<?php

/**
 * CmsMenuItems.php
 * 
 * */

namespace App\Cms\Model;

use Core\SQLBuilder;

class CmsBlocks extends \Core\Db {

  public static $tbl = 'cms_blocks';
  public static $pkey = 'id';

  public static function prepareQueryByFilters($filtres = []) {
    $query = new SQLBuilder([
      "table" => self::$tbl." CMS_B",
    ]);

    $idLayout = $filtres['idLayout'] ?? null;
    if ($idLayout) {
      $query->addCondition("CMS_B.id_layout = :id_layout");
      $query->addValue([":id_layout" => $idLayout]);
    }

    return $query;
  }

  public static function getListByFilters($tableParameters = [], $filtres = []) {
    $query = self::prepareQueryByFilters($filtres);
    $query->setLimit($tableParameters["start"] ?? null);
    $query->setOffset($tableParameters["length"] ?? null);

    $query->setColonnes(["CMS_B.*"]);

    $orderCol = $tableParameters["orderCol"] ?? null;
    $direction = $tableParameters["direction"] ?? null;
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

  /**
   * Transforme un array en tree
   * @param array $elements
   * @param type $parentId
   * @return type
   */
  public static function buildTree(array &$elements, $parentId = 0) {
    $branch = [];

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
