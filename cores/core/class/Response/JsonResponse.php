<?php

namespace Core\Response;

use Core\Common\Site;
use Core\Exception;
use Core\Response;

class JsonResponse extends Response
{
  protected array $datas = [];
  protected ?array $fullResponse = null;
  protected string $message = "";
  protected bool $success = true;
  protected bool $result = true;

  public function __construct(
    array $datas = [],
    string $message = "Success",
    bool $success = true,
    ?array $fullResponse = null
  ) {
    $this->setDatas($datas);
    $this->setMessage($message);
    $this->setSuccess($success);
    $this->setFullResponse($fullResponse);
  }

  /**
   * Get the value of datas
   * @return array 
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
   * Set the value of success
   * @param bool $success 
   * @return $this 
   */
  public function setSuccess(bool $success)
  {
    $this->success = $success;
    $this->result = $success;
    return $this;
  }

  /**
   * Set the value of message
   * @param string $message 
   * @return $this 
   */
  public function setMessage(string $message)
  {
    $this->message = $message;
    return $this;
  }

  /**
   * Set the value of fullResponse
   * @param null|array $fullResponse 
   * @return $this 
   */
  public function setFullResponse(?array $fullResponse)
  {
    $this->fullResponse = $fullResponse;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function setHeaders(): void
  {
    header("Content-Type: application/json; charset=utf-8");
    return;
  }

  /**
   * Mise en forme de la réponse
   * @return array 
   */
  public function toArray(): array
  {
    if ($this->fullResponse !== null) {
      return $this->fullResponse;
    }
    return [
      "success" => $this->success ? 1 : 0,
      "message" => $this->message,
      "datas" => $this->datas,
    ];
  }

  /**
   * Mise en forme de la réponse en JSON
   * @return string 
   */
  public function toString(): string
  {
    return json_encode($this->toArray());
  }

  /**
   * @inheritDoc
   */
  public function display(): void
  {
    $this->setContent($this->toString());
    parent::display();
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
  public static function displayErrorResponse(string $message = "Error", array $datas = [], array $backtrace = [], $file = "", $line = 0): void
  {
    if (DEBUG) {
      $response = [
        "success" => 0,
        "message" => $message,
        "datas" => $datas,
        "file" => $file,
        "line" => $line,
        "backtrace" => $backtrace,
      ];
    } else {
      $response = [
        "success" => 0,
        "message" => "Une erreur est survenue",
        "datas" => $datas,
      ];
    }

    $errorMessage = new static([], "", false, $response);
    $errorMessage->display();
    return;
  }
}
