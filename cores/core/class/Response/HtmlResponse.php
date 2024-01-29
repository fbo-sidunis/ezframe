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

  public function setDatas(array $datas = []): static
  {
    $this->datas = $datas;
    return $this;
  }

  public function setData(string $key, $value): static
  {
    $this->datas[$key] = $value;
    return $this;
  }

  public function setTemplatePath(string $templatePath): static
  {
    $this->templatePath = $templatePath;
    return $this;
  }

  public function display(): void
  {
    if (!$this->templatePath) {
      throw new Exception("Chemin du template non défini");
    }
    $content = Site::getTwigEnvironnment()->render($this->templatePath, $this->datas);
    $this->setContent($content);
    parent::display();
    return;
  }

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
    return;
  }
}
