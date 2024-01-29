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

  /**
   * Get the value of content
   * @return string 
   */
  public function getContent(): string
  {
    return $this->content;
  }

  /**
   * Set the value of content
   * @param mixed $content 
   * @return $this 
   */
  public function setContent($content)
  {
    $this->content = $content;
    return $this;
  }

  /**
   * Set the value of errorCode
   * @param int $errorCode 
   * @return $this 
   */
  public function setErrorCode(int $errorCode)
  {
    $this->errorCode = $errorCode;
    return $this;
  }

  /**
   * Set headers
   * @return void 
   */
  public function setHeaders(): void
  {
    // dd(debug_backtrace());
    header("Content-Type: text/html; charset=utf-8");
    return;
  }

  /**
   * Display the response
   * @return void 
   */
  public function display(): void
  {
    http_response_code($this->errorCode);
    $this->setHeaders();
    echo $this->getContent();
    return;
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
    $errorMessage = new static($message . " in " . $file . " on line " . $line . "<br>" . "Datas : <pre>" . print_r($datas, true) . "</pre>" . "<br>" . "Backtrace : <pre>" . print_r($backtrace, true) . "</pre>");
    $errorMessage->setErrorCode(500);
    $errorMessage->display();
    return;
  }
}
