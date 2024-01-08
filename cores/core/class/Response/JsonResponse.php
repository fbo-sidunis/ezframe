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

  public function setDatas(array $datas = [])
  {
    $this->datas = $datas;
    return $this;
  }

  public function setSuccess(bool $success)
  {
    $this->success = $success;
    return $this;
  }

  public function setMessage(string $message)
  {
    $this->message = $message;
    return $this;
  }

  public function setFullResponse(?array $fullResponse)
  {
    $this->fullResponse = $fullResponse;
    return $this;
  }

  public function setHeaders(): void
  {
    header("Content-Type: application/json; charset=utf-8");
    return;
  }

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

  public function toString(): string
  {
    return json_encode($this->toArray());
  }

  public function display(): void
  {
    $this->setContent($this->toString());
    parent::display();
    return;
  }

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
