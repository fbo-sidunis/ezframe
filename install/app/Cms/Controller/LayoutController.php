<?php 

namespace App\Cms\Controller;

use App\Cms\Controller;
use App\Cms\Model\CmsLayouts;
use App\Cms\Model\CmsPages;
use Helper\Datatable;
use Helper\FormData;

class LayoutController extends Controller {

  protected $template = "cms/layouts/layout_list.html.twig";

 
  //--------------------------------------------------------------------//
  // VUES
  //--------------------------------------------------------------------//

  public function render($datas = []) {
    return $this->display($this->template);
  }


  #Route : cms_pages_ajax_list
  public function list() {
    $filtres = [];
    $dt = new Datatable;
    $dt->setData(CmsLayouts::getListByFilters($dt->getQueryDatas(), $filtres));
    $dt->setRecordsTotal(CmsLayouts::getCountByFilters());
    $dt->setRecordsFiltered(CmsLayouts::getCountByFilters($filtres));
    return $dt->jsonResponse();
  }

  public function delete(){
    $id = getRequest('id');
    if (!$id) {
      return errorResponse([], "id manquant", 404);
    }
    if (!CmsLayouts::getBy($id)) {
      return errorResponse([], " introuvable", 404);
    }

    $HTML = CmsPages::removeBy($id);
    return successResponse($HTML, 'Layout supprimé');

  }

  #Route : cms_pages_ajax_save
  public function save(){
    $formData = new FormData([
      "formName" => "layout",
      "association" => [
        "libelle" => "libelle",
        "template_path" => "template_path",
        "template_back_path" => "template_back_path",
        "nb_content" => "nb_content",
        "type" => "type",
      ]
    ]);
    $returnData = [];
    $id = $formData->getId();
    $datas = $formData->getDatas();
    
    if($id){
      if (!CmsLayouts::getBy($id)) {
        return errorResponse($id, "Layout introuvable", 404);
      }
      $returnData["UPDATE_LAYOUT"] = CmsLayouts::updateBy($id,$datas);
    }else{
      $datas["created_by"] = $this->user->getId() ?? 0;
      $id = CmsLayouts::add($datas);
      $returnData["INSERT_LAYOUT"] = $id;
    }
  
    return successResponse($returnData, 'Layout enregistré');
  }
}