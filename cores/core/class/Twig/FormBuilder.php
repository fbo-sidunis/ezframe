<?php
namespace Core\Twig;

use Exception;
use Twig\Environment;
use Twig\Markup;

class FormBuilder{

  private Environment $env;
  private Extension $twigExt;
  private string $name;
  private $isBegan = false;
  private $isEnded = false;

  function __construct(Environment $env,Extension $twigExt, string $name)
  {
    $this->env = $env;
    $this->twigExt = $twigExt;
    $this->name = $name;
  }

  private function getAttrsString($attributes = [],$noMarkup =false){
    return $this->twigExt->getAttrsString($this->env,$attributes,$noMarkup);
  }
  private function escape($string,$strategy = 'html',$charset = null,$autoescape = false){
    return twig_escape_filter($this->env,$string,$strategy,$charset,$autoescape);
  }
  
  private function checkFormState(){
    if (!$this->isBegan){
      throw new Exception("Form not began");
    }
    if ($this->isEnded){
      throw new Exception("Form already ended");
    }
  }

  public function begin(array $parameters = []){
    if ($this->isBegan){
      throw new Exception("Form already began");
    }
    if ($this->isEnded){
      throw new Exception("Form already ended");
    }
    $this->isBegan = true;
    $attributes = $parameters["attributes"] ?? [];
    $attributes["id"] = "form_".$this->name;
    $attributes["action"] = $attributes["action"] ?? "javascript:void(0);";
    $sAttrs = $this->getAttrsString($attributes,true);
    return new Markup("<form $sAttrs>","UTF-8");
  }

  public function end(){
    $this->checkFormState();
    $this->isEnded = true;
    return new Markup("</form>","UTF-8");
  }

  public function label($name,$parameters = [],$markuped = true){
    $this->checkFormState();
    $required = $parameters["required"] ?? false;
    $label = trim($parameters["label"] ?? $name);
    if ($required && !str_ends_with($label,"*")){
      $label .= " *";
    }
    $label_attributes = $parameters["label_attributes"] ?? [];
    $type = $parameters["type"] ?? "input";
    $prefix = "";
    switch($type){
      case "textarea":
        $prefix = "textarea_";
      break;
      case "select":
        $prefix = "select_";
      break;
      default:
        $prefix = "input_";
      break;
    }
    $label_attributes["for"] = $prefix.$this->name."_".$name;
    $label_attributes["data-attr-name"] = $name;
    $label_attributes["class"] = ($label_attributes["class"] ?? "form-label").($required ? " form-label-required" : "");
    $sAttrsLabel = $this->getAttrsString($label_attributes,true);
    $html = "<label $sAttrsLabel>$label</label>";
    return $markuped ? new Markup($html,"UTF-8") : $html;
  }

  public function input($name,$parameters = [],$markuped = true){
    $this->checkFormState();
    $value = $parameters["value"] ?? "";
    $required = $parameters["required"] ?? false;
    $attributes = $parameters["attributes"] ?? [];
    $attributes["autocomplete"] = $attributes["autocomplete"] ?? "off";
    $attributes["id"] = "input_".$this->name."_".$name;
    $attributes["name"] = $this->name."[".$name."]";
    $attributes["data-attr-name"] = $name;
    $attributes["class"] = ($attributes["class"] ?? "form-control").($required ? " required" : "");
    if ($value !== null){
      $attributes["value"] = $value;
    }
    if ($required){
      $attributes["required"] = "required";
    }
    $sAttrs = $this->getAttrsString($attributes,true);
    $html = "<input $sAttrs>";
    return $markuped ? new Markup($html,"UTF-8") : $html;
  }

  public function textarea($name,$parameters = [],$markuped = true){
    $this->checkFormState();
    $required = $parameters["required"] ?? false;
    $value = $parameters["value"] ?? "";
    $attributes = $parameters["attributes"] ?? [];
    $attributes["autocomplete"] = $attributes["autocomplete"] ?? "off";
    $attributes["class"] = $attributes["class"] ?? "form-control";
    $attributes["id"] = "textarea_".$this->name."_".$name;
    $attributes["data-attr-name"] = $name;
    $attributes["name"] = $this->name."[".$name."]";
    if ($value !== null){
      $attributes["value"] = $value;
    }
    if ($required){
      $attributes["required"] = "required";
    }
    
    $sAttrsInput = $this->getAttrsString($attributes,true);
    $html = "<textarea $sAttrsInput >".$this->escape($value)."</textarea>";
    return $markuped ? new Markup($html,"UTF-8") : $html;
  }

  public function select($name,$parameters = [],$markuped = true){
    $this->checkFormState();
    $required = $parameters["required"] ?? false;
    $value = $parameters["value"] ?? null;
    $multiple = $parameters["multiple"] ?? false;
    $attributes = $parameters["attributes"] ?? [];
    $attributes["autocomplete"] = $attributes["autocomplete"] ?? "off";
    $attributes["class"] = $attributes["class"] ?? "form-control selectpicker";
    $attributes["id"] = "select_".$this->name."_".$name;
    $attributes["data-attr-name"] = $name;
    $labelHTML = $attributes["label_html"] ?? false;
    $attributes["name"] = $this->name."[".$name."]";
    if ($multiple){
      $attributes["multiple"] = "multiple";
    }
    $emptyChoice = $parameters["emptyChoice"] ?? null;
    $choices = "";
    if ($emptyChoice){
      $choices .= "<option value=\"\">".$this->escape($emptyChoice,"html")."</option>";
    }
    if ($required){
      $attributes["required"] = "required";
    }
    $choices .= implode("",array_map(function($choice) use ($multiple,$value,$labelHTML){
      $label = $choice["label"] ?? "";
      $optionValue = $choice["value"] ?? "";
      $attributes = $choice["attributes"] ?? [];
      $attributes["value"] = $optionValue;
      if ($multiple && ((is_array($value) && in_array($optionValue,$value)) || $value == $optionValue)){
        $attributes["selected"] = "selected";
      }else if (!$multiple && $value == $optionValue){
        $attributes["selected"] = "selected";
      }
      $sAttrs = $this->getAttrsString($attributes,true);
      return "<option $sAttrs>".($labelHTML ? $label : $this->escape($label,"html"))."</option>";
    },$parameters["choices"] ?? []));
    
    $sAttrs = $this->getAttrsString($attributes,true);
    $html = "<select $sAttrs>$choices</select>";
    return $markuped ? new Markup($html,"UTF-8") : $html;
  }

  public function checkboxes($name,$parameters = [],$markuped = true){
    $this->checkFormState();
    $value = $parameters["value"] ?? null;
    $required = $parameters["required"] ?? false;
    $inline = $parameters["inline"] ?? false;
    $multiple = $parameters["multiple"] ?? false;
    $attributes = $parameters["attributes"] ?? [];
    $attributes["autocomplete"] = $attributes["autocomplete"] ?? "off";
    $attributes["type"] = $multiple ? "checkbox" : "radio";
    $attributes["name"] = $this->name."[".$name."]".($multiple ? "[]" : "");
    $attributes["class"] = $attributes["class"] ?? "form-check-input";
    $attributes["data-attr-name"] = $name;
    $emptyChoice = $parameters["emptyChoice"] ?? null;
    $choices = $parameters["choices"] ?? [];
    if ($emptyChoice){
      $choices = array_merge([["label" => $emptyChoice,"value" => ""]],$choices);
    }
    if ($required){
      $attributes["required"] = "required";
    }
    $choices = array_values($choices);
    $indexes = array_keys($choices);
    $id = "check_".$this->name."_".$name;
    $html = implode("",array_map(function($choice,$index) use ($multiple,$value,$attributes,$id,$inline){

      $label = $choice["label"] ?? "";
      $optionValue = $choice["value"] ?? "";
      foreach($choice["attributes"] ?? [] as $key => $value){
        $attributes[$key] = $value;
      }
      $label_attributes = $choice["label_attributes"] ?? [];
      $labelHTML = $choice["label_html"] ?? [];
      $attributes["id"] = $id."_".$index;
      $attributes["value"] = $optionValue;
      if ($multiple && ((is_array($value) && in_array($optionValue,$value)) || $value == $optionValue)){
        $attributes["checked"] = "checked";
      }else if (!$multiple && $value == $optionValue){
        $attributes["checked"] = "checked";
      }
      $sAttrs = $this->getAttrsString($attributes,true);
      $label_attributes["for"] = $id."_".$index;
      $label_attributes["class"] = $label_attributes["class"] ?? "form-check-label";
      $labelAttrs = $this->getAttrsString($label_attributes,true);
      $sContAttrs = $this->getAttrsString([
        "class" => "form-check".($inline ? " form-check-inline" : ""),
        "id" => $id."_".$index."_container"
      ],true);
      $input = "<input $sAttrs />";
      $label = "<label $labelAttrs>".($labelHTML ? $label : $this->escape($label,"html"))."</label>";
      return "<div $sContAttrs>$input$label</div>";
    },$choices,$indexes));
    $sAttrsContainer = $this->getAttrsString([
      "class" => "form-check-group",
      "id" => $id."_container"
    ],true);
    $html = "<div $sAttrsContainer>$html</div>";
    return $markuped ? new Markup($html,"UTF-8") : $html;
  }

  public function group($name,$parameters = []){
    $this->checkFormState();
    $group_attributes = $parameters["group_attributes"] ?? [];
    $classes = array_filter([$group_attributes["override_class"] ?? "mb-3 form-group",$group_attributes["class"] ?? null]);
    if (isset($group_attributes["override_class"])){
      unset($group_attributes["override_class"]);
    }
    $group_attributes["class"] = implode(" ",$classes);
    $type = $parameters["type"] ?? "input";
    $labelEnabled = $parameters["label"] ?? false;
    $sAttrsGroup = $this->getAttrsString($group_attributes,false);
    $input = null;
    switch ($type){
      case "input":
        $input = $this->input($name,$parameters,false);
      break;
      case "textarea":
        $input = $this->textarea($name,$parameters,false);
      break;
      case "select":
        $input = $this->select($name,$parameters,false);
      break;
      case "checkboxes":
        $input = $this->checkboxes($name,$parameters,false);
      break;
    }
    if (!$input){
      throw new \Exception("Invalid group type: ".$type);
    }
    return new Markup("<div $sAttrsGroup>".($labelEnabled ? $this->label($name,$parameters,false) : "").$input."</div>","UTF-8");
  }

}