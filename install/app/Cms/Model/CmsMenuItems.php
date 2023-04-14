<?php

/**
 * CmsMenuItems.php
 * 
 * */

namespace App\Cms\Model;

use Core\SQLBuilder;

class CmsMenuItems extends \Core\Db {

  public static $tbl = 'cms_menu_items';
  public static $pkey = 'id';

  public static function prepareQueryByFilters($filtres = []) {
    $query = new SQLBuilder([
      "table" => self::$tbl." CMS_MI",
    ]);

    $actif = $filtres['actif'] ?? null;
    $public = $filtres['public'] ?? null;
    $search = $filtres['search'] ?? null;

    if ($actif) {
      $query->addCondition("CMS_MI.actif = :actif");
      $query->addValue([":actif" => $actif]);
    }

    if ($public) {
      $query->addCondition("CMS_MI.public = :public");
      $query->addValue([":public" => $public]);
    }

    if ($search) {
      //Les colonnes à tester en cas recherche
      $colsSearch = [
        "CMS_MI.libelle",
        "CMS_MI.title",
        "CMS_MI.description",
      ];
      $callbackCondSearch = function($col){
        return "$col LIKE :search";
      };
      $query->addCondition("(".implode(" OR ",array_map($callbackCondSearch,$colsSearch)).")");
      $query->addValue([":search" => "%".$search."%"]);
    }

    return $query;
  }

  public static function getListByFilters($tableParameters = [], $filtres = []) {
    $query = self::prepareQueryByFilters($filtres);
    $query->setLimit($tableParameters["start"] ?? null);
    $query->setOffset($tableParameters["length"] ?? null);

    $query->setColonnes(["CMS_MI.*"]);

    $orderCol = $tableParameters["orderCol"] ?? null;
    $direction = $tableParameters["direction"] ?? null;
    $orderableColumns = [
      "libelle" => "CMS_MI.libelle",
      "active" => "CMS_MI.active",
      "public" => "CMS_MI.public",
      "ordre" => "CMS_MI.ordre",
      "id" => "CMS_MI.id",
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
