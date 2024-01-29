<?php

namespace Core\Response;

use Core\Common\Site;
use Core\Exception;
use Core\Response;

class HtmlResponse extends Response
{
  protected array $datas = [];
  protected string $templatePath;

  public function __construct(
    string $templatePath,
    array $datas = []
  ) {
    $this->setDatas($datas);
    $this->setTemplatePath($templatePath);
  }

  /**
   * Set the value of datas
   * @param array $datas 
   * @return $this 
   */
  public function setDatas(array $datas = [])
  {
    $this->datas = $datas;
    return $this;
  }

  /**
   * Set the value of an item in datas
   * @param string $key 
   * @param mixed $value 
   * @return $this 
   */
  public function setData(string $key, $value)
  {
    $this->datas[$key] = $value;
    return $this;
  }

  /**
   * Get the value of templatePath
   * @param string $templatePath 
   * @return $this 
   */
  public function setTemplatePath(string $templatePath)
  {
    $this->templatePath = $templatePath;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function display(): void
  {
    if (!$this->templatePath) {
      throw new Exception("Chemin du template non défini");
    }
    $content = Site::getTwigEnvironnment()->render($this->templatePath, $this->datas);
    $this->setContent($content);
    parent::display();
  }

  /**
   * Display an error response
   * @param string $message 
   * @param array $datas 
   * @param array $backtrace 
   * @param string $file 
   * @param int $line 
   * @return void 
   */
  public static function displayErrorResponse(
    string $message = "An error occured",
    array $datas = [],
    array $backtrace = [],
    $file = "",
    $line = 0
  ): void {
    $datas["message"] = $message;
    $datas["backtrace"] = $backtrace;
    $datas["file"] = $file;
    $datas["line"] = $line;
    $errorMessage = new static("500.html.twig", $datas);
    $errorMessage->display();
  }
}
