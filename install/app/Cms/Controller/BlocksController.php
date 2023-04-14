<?php 

namespace App\Cms\Controller;

use App\Cms\Controller;
use App\Cms\Model\CmsBlocks;
use App\Cms\Model\CmsLayouts;
use Helper\Datatable;
use Helper\FormData;

class BlocksController extends Controller {

  protected $template = "cms/blocks/blocks_list.html.twig";
 
  //--------------------------------------------------------------------//
  // VUES
  //--------------------------------------------------------------------//

  #Route : cms_blocks_list
  public function render($datas = []) {
    $layouts = CmsLayouts::getList();
    foreach ($layouts as &$layout) {
      $layout["html"] = $this->twig->render($layout["template_back_path"]);
    }
    $this->setData("LAYOUTS", $layouts);
    return $this->display();
  }


  #Route : cms_blocks_ajax_list
  public function list() {
    $dt = new Datatable();
    $filters = [
      "idLayout" => getGet("id_layout"),
    ];
    $dt->setData(CmsBlocks::getListByFilters($dt->getQueryDatas(), $filters));
    $dt->setRecordsTotal(CmsBlocks::getCountByFilters());
    $dt->setRecordsFiltered(CmsBlocks::getCountByFilters($filters));
    return $dt->jsonResponse();
  }
  
  #Route : cms_blocks_ajax_delete
  public function delete(){
    $id = getRequest('id');
    if (!$id) {
      return errorResponse(["id",$id], "Bloc introuvable", 404);
    }
    $HTML = CmsBlocks::removeBy($id);
    return successResponse($HTML, 'Bloc supprimé');
  }
  
  #Route : cms_blocks_ajax_save
  public function save(){
    $formData = new FormData([
      "formName" => "block",
      "association" => [
        "layout" => "id_layout",
        "libelle" => "libelle",
        "content" => "content",
      ]
    ]);
    $id = $formData->getId() ;
    $returnData = [];
    $datas = $formData->getDatas();
    $datas["lastupdate_by"] = $this->user->getId();

    if($id){
      if (!CmsBlocks::getBy($id)) {
        return errorResponse(["id"=>$id], "Page introuvable", 404);
      }
      $returnData["UPDATE_PAGE"] = CmsBlocks::updateBy($id,$datas);
    }else{
      $datas["created_by"] = $this->user->getId();
      $returnData["INSERT_PAGE"] = CmsBlocks::add($datas);
    }
    

    return successResponse($returnData, 'Bloc enregistré');
  }


  

}