<?php

namespace Core;

/**
 * Classe permettant le log
 * @package Core
 $logger = new Logger();
 $logger->setTargetFile("logfile.log");
 $logger->log("Quelque chose à logger");
 */
class Logger
{
  const LOG_DIR = ROOT_DIR . "var/log/";
  protected $targetFile = self::LOG_DIR . "default.log";
  protected $fileAppend = true;

  /**
   * Constructeur
   * @param array $parameters [
   *  "targetFile" => string,
   *  "fileAppend" => bool,
   * ]
   * @return void 
   */
  function __construct($parameters = [])
  {
    if (!file_exists(self::LOG_DIR)) mkdir(self::LOG_DIR, 0777, true);
    foreach (($parameters ?? []) as $parameter => $value) {
      $setter = "set" . ucfirst($parameter);
      if (method_exists($this, $setter)) $this->$setter($value);
    }
  }

  /**
   * Log le contenu donné en paramètre dans le fichier spécifié à la construction (ou avec setTargetFile)
   * @param mixed $content 
   * @return void 
   */
  public function log($content): void
  {
    file_put_contents($this->getTargetFile(), date("Y-m-d H:i:s") . " | " . print_r($content, true) . PHP_EOL, $this->getFileAppend() ? FILE_APPEND : 0);
  }

  public function clear(): bool
  {
    return unlink($this->getTargetFile());
  }


  /**
   * Get the value of targetFile
   */
  public function getTargetFile()
  {
    return $this->targetFile;
  }

  /**
   * Set the value of targetFile
   *
   * @return  self
   */
  public function setTargetFile($targetFile)
  {
    if (!str_starts_with($targetFile, self::LOG_DIR)) {
      $targetFile = self::LOG_DIR . $targetFile;
    }
    $test = explode("/", str_replace(self::LOG_DIR, "", $targetFile));
    array_pop($test);
    $path = self::LOG_DIR . implode("/", array_filter($test)) . "/";
    if (!file_exists($path)) mkdir($path, 0777, true);
    $this->targetFile = $targetFile;

    return $this;
  }

  /**
   * Get the value of fileAppend
   */
  public function getFileAppend()
  {
    return $this->fileAppend;
  }

  /**
   * Set the value of fileAppend
   *
   * @return  self
   */
  public function setFileAppend($fileAppend)
  {
    $this->fileAppend = $fileAppend;

    return $this;
  }
}
