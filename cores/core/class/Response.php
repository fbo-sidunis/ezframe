<?php

namespace Core;

class Response
{

  protected string $content;
  protected int $errorCode = 200;

  public function __construct($content = "")
  {
    $this->content = $content;
  }

  public function getContent(): string
  {
    return $this->content;
  }

  public function setContent($content): static
  {
    $this->content = $content;
    return $this;
  }

  public function setErrorCode(int $errorCode): static
  {
    $this->errorCode = $errorCode;
    return $this;
  }

  public function setHeaders(): void
  {
    // dd(debug_backtrace());
    header("Content-Type: text/html; charset=utf-8");
    return;
  }

  public function display(): void
  {
    http_response_code($this->errorCode);
    $this->setHeaders();
    echo $this->getContent();
    return;
  }

  public static function displayErrorResponse(string $message = "An error occured", array $datas = [], array $backtrace = [], $file = "", $line = 0): void
  {
    $errorMessage = new static($message . " in " . $file . " on line " . $line . "<br>" . "Datas : <pre>" . print_r($datas, true) . "</pre>" . "<br>" . "Backtrace : <pre>" . print_r($backtrace, true) . "</pre>");
    $errorMessage->setErrorCode(500);
    $errorMessage->display();
    return;
  }
}
