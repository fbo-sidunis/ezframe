<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of nvd3_graph
 *
 * @author sta-sidunis
 */
namespace Core\Common;
class Nvd3Graph {

  public $graph;
  public $series=[];

  function __construct(){

  }

  public function add_serie($serie_name,$data=[],$arr_x=[]){
    if(!empty($data)){
      $dataSerie = [];
      foreach($data as $K=>$V){
        if(!empty($V)){
          $dataSerie[] = array('x'=>$arr_x[$K],'y'=>$V);
        }
      }
      $this->series[]=array('key'=>$serie_name,'values'=>$dataSerie);
    }
  }

  public function get_series(){
    return $this->series;
  }

  public function create_series($arr_data=[]){
    foreach($arr_data as $SN=>$L ){
      $this->add_serie($SN,$L);
    }
  }


}
