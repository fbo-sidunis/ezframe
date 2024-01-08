<?php

namespace App\Cms\Controller;

use App\Admin\Model\Roles;
use App\Cms\Controller;
use App\Cms\Model\CmsMenuItems;
use App\Cms\Model\CmsMenuRoles;
use App\Cms\Model\ConfigRole;
use Core\Response\HtmlResponse;
use Core\Response\JsonResponse;
use Helper\Datatable;
use Helper\FormData;

class MenuController extends Controller
{

  protected $tmpl_details = "cms/menus/menu_details.html.twig";



  //--------------------------------------------------------------------//
  // VUES
  //--------------------------------------------------------------------//

  #Route : cms_menu_list
  public function render(): HtmlResponse
  {
    $getMenu = CmsMenuItems::getList(['libelle', 'ASC']);
    $menu_recursif = CmsMenuItems::buildTree($getMenu);

    $this->datas['MENUITEMS'] = $menu_recursif;
    $this->datas['ROLES'] = Roles::getList();

    return $this->display($this->template);
  }

  #Route : cms_menu_ajax_save
  public function save(): JsonResponse
  {
    $formData = new FormData([
      "formName" => "menu",
      "association" => [
        "libelle" => "libelle",
        "parent" => function ($value, &$datas, $formData) {
          $parent = CmsMenuItems::getBy($value);
          $datas["id_parent"] = $parent ? $parent[CmsMenuItems::$pkey] : 0;
          $datas["niveau"] = $parent ? (intval($parent["niveau"] ?? 0) + 1) : 0;
        },
        "extend_class" => "extend_class",
        "type_link" => function ($value, &$datas, $formData) {
          $link = $formData["link"] ?? null;
          $datas["link"] = $value == 'LINK' ? $link : null;
          $datas["alias"] = $value == 'ALIAS' ? $link : null;
          $datas["id_page"] = $value == 'ID' ? $link : null;
        },
        "link" => "link",
        "title" => "title",
        "description" => "description",
        "public" => function ($value, &$datas) {
          $datas["public"] = $value ?: "N";
        },
        "active" => function ($value, &$datas) {
          $datas["active"] = $value ?: "N";
        },
        "order" => "ordre",
      ]
    ]);

    $returnData = [];
    $id = $formData->getId();
    $datas = $formData->getDatas();

    if ($id) {
      if (!CmsMenuItems::getBy($id)) {
        return errorResponse($id, "Menu introuvable", 404);
      }
      $returnData["UPDATE_MENU"] = CmsMenuItems::updateBy($id, $datas);
    } else {
      $datas["created_by"] = $this->user->getId() ?? 0;
      $id = CmsMenuItems::add($datas);
      $returnData["INSERT_MENU"] = $id;
    }
    $returnData['UPDATE_ROLES'] = $this->updateRoleMenu($id, $formData->getFormData("crud") ?: []);

    return successResponse($returnData, 'Page enregistrée');
  }

  #Route : cms_menu_ajax_list
  public function list(): JsonResponse
  {
    $noTable = getRequest("noTable") ?? false;
    if ($noTable) {
      return successResponse([
        "menus" => CmsMenuItems::getListByFilters([
          "orderCol" => "ordre",
          "direction" => "ASC",
        ])
      ]);
    }
    $dt = new Datatable();
    $nextReorder = getRequest("nextReorder");
    if ($nextReorder) {
      foreach ($nextReorder as $id => $order) {
        CmsMenuItems::updateBy($id, ["ordre" => $order]);
      }
    }
    $filtres = [
      "actif" => getRequest("actif"),
      "public" => getRequest("public"),
      "search" => $dt->getSearch(),
    ];
    $menus = CmsMenuItems::getListByFilters($dt->getQueryDatas(), $filtres);
    $this->addRolesToResult($menus);
    $dt->setData($menus);
    $dt->setRecordsTotal(CmsMenuItems::getCountByFilters());
    $dt->setRecordsFiltered(CmsMenuItems::getCountByFilters($filtres));
    return $dt->jsonResponse();
  }

  #Route : cms_menu_ajax_delete
  public function delete(): JsonResponse
  {
    $id = getGet("id");

    if (!CmsMenuItems::getBy($id)) {
      return errorResponse($id, "Menu introuvable", 404);
    }
    $returnData = [];
    $returnData["DELETE_MENU"] = CmsMenuItems::removeBy($id);

    return successResponse($returnData, 'Page supprimée');
  }

  private function updateRoleMenu($idMenu, $crud): array
  {
    $res = [];
    $res["DELETE"] = CmsMenuRoles::removeBy($idMenu, "id_menu_item");
    foreach ($crud as $R => $V) {
      $V = $V ?: [];
      $res["INSERT"][] = CmsMenuRoles::add([
        'id_menu_item' => $idMenu,
        'code_role' => $R,
        'c' => in_array("c", $V) ? "Y" : "N",
        'r' => in_array("r", $V) ? "Y" : "N",
        'u' => in_array("u", $V) ? "Y" : "N",
        'd' => in_array("d", $V) ? "Y" : "N",
      ]);
    }
    return $res;
  }

  private function addRolesToResult(&$menus): void
  {
    if (!$menus) return;
    $roles = CmsMenuRoles::getByIdsMenuGrouped(array_map(function ($m) {
      return $m["id"];
    }, $menus));
    foreach ($menus as &$menu) {
      $menu["roles"] = $roles[$menu["id"]] ?? [];
    }
  }
}
