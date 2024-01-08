<?php

namespace App\Cms\Controller;

use App\Cms\Controller;
use App\Cms\Model\CmsPages;
use Core\Response\HtmlResponse;
use Core\Response\JsonResponse;
use Helper\Datatable;

class PagesController extends Controller
{

  protected $template = "cms/pages/pages_list.html.twig";

  //--------------------------------------------------------------------//
  // VUES
  //--------------------------------------------------------------------//

  public function render($datas = []): HtmlResponse
  {
    return $this->display($this->template);
  }


  #Route : cms_pages_ajax_list
  public function list(): JsonResponse
  {
    $filtres = [
      "libelle" => getRequest("libelle"),
      "created_by" => getRequest("created_by"),
      "created_date" => getRequest("created_date"),
    ];

    $dt = new Datatable;
    $dt->setData(CmsPages::getListByFilters($dt->getQueryDatas(), $filtres));
    $dt->setRecordsTotal(CmsPages::getCountByFilters());
    $dt->setRecordsFiltered(CmsPages::getCountByFilters($filtres));
    return $dt->jsonResponse();
  }

  public function delete(): JsonResponse
  {
    $id = getRequest('id');
    if (!$id) {
      return errorResponse([], "id manquant", 404);
    }
    if (!CmsPages::getBy($id)) {
      return errorResponse([], "Page introuvable", 404);
    }

    $HTML = CmsPages::removeBy($id);
    return successResponse($HTML, 'Page supprimée');
  }

  #Route : cms_pages_ajax_save
  public function save(): JsonResponse
  {
    $formData = $_POST["page"] ?? [];
    $id = $formData["id"] ?? null;
    $returnData = [];
    $datas = [];
    $assoc = [
      "libelle" => "libelle",
      "type" => "type",
    ];
    foreach ($assoc as $formName => $dbCol) {
      $datas[$dbCol] = $formData[$formName] ?? null;
    }
    $datas["lastupdate_by"] = $this->user->getId();

    if ($id) {
      if (!CmsPages::getBy($id)) {
        return errorResponse($id, "Page introuvable", 404);
      }
      $returnData["UPDATE_PAGE"] = CmsPages::updateBy($id, $datas);
    } else {
      $datas["created_by"] = $this->user->getId();
      $returnData["INSERT_PAGE"] = CmsPages::add($datas);
    }


    return successResponse($returnData, 'Page enregistrée');
  }
}
