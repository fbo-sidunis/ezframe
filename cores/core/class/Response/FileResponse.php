<?php

namespace Core\Response;

use Core\Exception;
use Core\Response;

class FileResponse extends Response
{

  const MIME_TYPE_PDF = "application/pdf";
  const MIME_TYPE_CSV = "text/csv";
  const MIME_TYPE_XLS = "application/vnd.ms-excel";
  const MIME_TYPE_XLSX = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
  const MIME_TYPE_DOC = "application/msword";
  const MIME_TYPE_DOCX = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
  const MIME_TYPE_PPT = "application/vnd.ms-powerpoint";
  const MIME_TYPE_PPTX = "application/vnd.openxmlformats-officedocument.presentationml.presentation";
  const MIME_TYPE_ZIP = "application/zip";
  const MIME_TYPE_RAR = "application/x-rar-compressed";
  const MIME_TYPE_TAR = "application/x-tar";
  const MIME_TYPE_GZIP = "application/gzip";
  const MIME_TYPE_JPEG = "image/jpeg";
  const MIME_TYPE_JPG = "image/jpg";
  const MIME_TYPE_PNG = "image/png";
  const MIME_TYPE_GIF = "image/gif";
  const MIME_TYPE_MP3 = "audio/mpeg";
  const MIME_TYPE_MP4 = "video/mp4";
  const MIME_TYPE_WAV = "audio/wav";
  const MIME_TYPE_WMA = "audio/x-ms-wma";
  const MIME_TYPE_WMV = "video/x-ms-wmv";
  const MIME_TYPE_FLV = "video/x-flv";
  const MIME_TYPE_AVI = "video/x-msvideo";
  const MIME_TYPE_MKV = "video/x-matroska";
  const MIME_TYPE_3GP = "video/3gpp";
  const MIME_TYPE_3G2 = "video/3gpp2";
  const MIME_TYPE_TXT = "text/plain";
  const MIME_TYPE_HTML = "text/html";
  const MIME_TYPE_XML = "text/xml";
  const MIME_TYPE_JSON = "application/json";
  const MIME_TYPE_JS = "application/javascript";
  const MIME_TYPE_CSS = "text/css";
  const MIME_TYPE_ICS = "text/calendar";
  const MIME_TYPE_VCF = "text/x-vcard";

  protected string $filePath;
  protected string $finalFileName;
  protected bool $forceDownload = false;
  protected ?string $mimeType = null;

  public function __construct(
    string $filePath,
    string $finalFileName = "",
    bool $forceDownload = false,
    ?string $mimeType = null
  ) {
    $this->setFilePath($filePath);
    $this->setFinalFileName($finalFileName ?: basename($filePath));
    $this->setForceDownload($forceDownload);
    $this->setMimeType($mimeType);
  }

  public function setHeaders(): void
  {
    if ($this->forceDownload) {
      header("Content-Type: application/octet-stream");
      header("Content-Transfer-Encoding: Binary");
      header("Content-disposition: attachment; filename=\"" . $this->finalFileName . "\"");
    } else {
      $mimeType = $this->mimeType ?? mime_content_type($this->filePath);
      if (!$mimeType) {
        throw new Exception("Impossible de déterminer le type MIME du fichier");
      }
      if ($mimeType != mime_content_type($this->filePath)) {
        throw new Exception("Le type MIME du fichier ne correspond pas au type MIME défini");
      }
      header("Content-Type: " . $mimeType);
    }
    header("Content-Length: " . filesize($this->filePath));
    return;
  }


  public function display(): void
  {
    if (!$this->filePath) {
      throw new Exception("Chemin du fichier non défini");
    }
    if (!file_exists($this->filePath)) {
      throw new Exception("Le fichier n'existe pas");
    }
    $this->setHeaders();
    readfile($this->filePath);
    return;
  }

  public static function displayErrorResponse(string $message = "An error occured", array $datas = [], array $backtrace = [], $file = "", $line = 0): void
  {
    $response = new HtmlResponse(...func_get_args());
    $response->display();
    return;
  }

  /**
   * Get the value of filePath
   *
   * @return string
   */
  public function getFilePath(): string
  {
    return $this->filePath;
  }

  /**
   * Set the value of filePath
   *
   * @param string $filePath
   *
   * @return self
   */
  public function setFilePath(string $filePath): self
  {
    $this->filePath = $filePath;

    return $this;
  }

  /**
   * Get the value of finalFileName
   *
   * @return string
   */
  public function getFinalFileName(): string
  {
    return $this->finalFileName;
  }

  /**
   * Set the value of finalFileName
   *
   * @param string $finalFileName
   *
   * @return self
   */
  public function setFinalFileName(string $finalFileName): self
  {
    $this->finalFileName = $finalFileName;

    return $this;
  }

  /**
   * Get the value of forceDownload
   *
   * @return bool
   */
  public function getForceDownload(): bool
  {
    return $this->forceDownload;
  }

  /**
   * Set the value of forceDownload
   *
   * @param bool $forceDownload
   *
   * @return self
   */
  public function setForceDownload(bool $forceDownload): self
  {
    $this->forceDownload = $forceDownload;

    return $this;
  }

  /**
   * Get the value of mimeType
   *
   * @return ?string
   */
  public function getMimeType(): ?string
  {
    return $this->mimeType;
  }

  /**
   * Set the value of mimeType
   *
   * @param ?string $mimeType
   *
   * @return self
   */
  public function setMimeType(?string $mimeType): self
  {
    $this->mimeType = $mimeType;

    return $this;
  }
}
