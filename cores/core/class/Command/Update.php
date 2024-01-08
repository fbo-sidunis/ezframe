<?php

namespace Core\Command;

use Core\Common\Site;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Update extends \Core\CommandHandler
{

  protected $addedFiles = [];
  protected $removedFiles = [];
  protected $modifiedFiles = [];

  protected function addFile($file)
  {
    if (in_array($file, $this->removedFiles)) {
      $this->modifyFile($file);
      return;
    }
    if (in_array($file, $this->modifiedFiles)) {
      return;
    }
    if (!in_array($file, $this->addedFiles)) {
      $this->addedFiles[] = $file;
    }
  }

  protected function removeFile($file)
  {
    if (in_array($file, $this->addedFiles)) {
      $this->addedFiles = array_diff($this->addedFiles, [$file]);
    }
    if (in_array($file, $this->modifiedFiles)) {
      $this->modifiedFiles = array_diff($this->modifiedFiles, [$file]);
    }
    if (!in_array($file, $this->removedFiles)) {
      $this->removedFiles[] = $file;
    }
  }

  protected function modifyFile($file)
  {
    if (in_array($file, $this->addedFiles)) {
      return;
    }
    if (in_array($file, $this->removedFiles)) {
      $this->removedFiles = array_diff($this->removedFiles, [$file]);
    }
    if (!in_array($file, $this->modifiedFiles)) {
      $this->modifiedFiles[] = $file;
    }
  }

  protected function showFiles()
  {
    sort($this->addedFiles);
    sort($this->removedFiles);
    sort($this->modifiedFiles);
    if (count($this->addedFiles) > 0) {
      echo "+ " . implode("\n+ ", $this->addedFiles) . "\n";
    } else {
      echo "No file added\n";
    }
    if (count($this->removedFiles) > 0) {
      echo "- " . implode("\n- ", $this->removedFiles) . "\n";
    } else {
      echo "No file removed\n";
    }
    if (count($this->modifiedFiles) > 0) {
      echo "* " . implode("\n* ", $this->modifiedFiles) . "\n";
    } else {
      echo "No file modified\n";
    }
  }

  protected $logger;

  function __construct()
  {
    $this->logger = new Logger("update", [
      new RotatingFileHandler(
        filename: ROOT_DIR . "var/log/update/update.log",
        maxFiles: 3,
        level: Logger::DEBUG,
      ),
      new StreamHandler(
        stream: "php://stdout",
        level: Logger::DEBUG,
      ),
    ]);
  }

  public function execute()
  {
    $this->logger->info("Start update");
    $this->updateTo1_0();
    $this->updateTo1_5();
    $this->updateTo1_7();
    $this->logger->info("End update");
    $this->showFiles();
  }

  const COMPOSER_FILE = ROOT_DIR . "composer.json";
  const SITE_CONFIG_FILE = ROOT_DIR . "config/site_config.json";

  public function updateTo1_0()
  {
    $composerConfig = json_decode(file_get_contents(self::COMPOSER_FILE), true);
    if (!isset($composerConfig["autoload"]["psr-4"]["Core\\"])) {
      return;
    }
    unset($composerConfig["autoload"]["psr-4"]["Core\\"]);
    unset($composerConfig["autoload"]["psr-4"]["Core\\Common\\"]);
    unset($composerConfig["require"]["twig/twig"]);
    unset($composerConfig["require"]["cbschuld/browser.php"]);
    unset($composerConfig["require"]["components/jquery"]);
    unset($composerConfig["require"]["monolog/monolog"]);
    unset($composerConfig["require"]["nadar/quill-delta-parser"]);
    unset($composerConfig["require"]["phpmailer/phpmailer"]);
    unset($composerConfig["require"]["symfony/dotenv"]);
    unset($composerConfig["require"]["symfony/var-dumper"]);
    unset($composerConfig["require"]["twbs/bootstrap"]);
    unset($composerConfig["require"]["twig/inky-extra"]);
    unset($composerConfig["require"]["twig/twig"]);
    file_put_contents(self::COMPOSER_FILE, json_encode($composerConfig, JSON_PRETTY_PRINT));
    $this->modifyFile(self::COMPOSER_FILE);
    $folderToRemove = ROOT_DIR . "core/";
    rmdir($folderToRemove);
    $this->removeFile($folderToRemove);
  }

  public function updateTo1_5()
  {
    $envFile = ROOT_DIR . ".env";
    if (file_exists($envFile)) {
      return;
    }
    $siteConfigFile = json_decode(file_get_contents(self::SITE_CONFIG_FILE), true);
    $env = $siteConfigFile["env"] ?? "dev";
    unset($siteConfigFile["env"]);
    file_put_contents(self::SITE_CONFIG_FILE, json_encode($siteConfigFile, JSON_PRETTY_PRINT));
    file_put_contents($envFile, "APP_ENV=$env\n");
    $this->addFile($envFile);
    $this->modifyFile(self::SITE_CONFIG_FILE);
  }

  public function updateTo1_7()
  {
    $composerFile = ROOT_DIR . "composer.json";
    $composerConfig = json_decode(file_get_contents(self::COMPOSER_FILE), true);
    if (isset($composerConfig["autoload"]["psr-4"]["Model\\"]) && $composerConfig["autoload"]["psr-4"]["Model\\"] == ["model/"]) {
      return;
    }
    $composerConfig["autoload"]["psr-4"]["Model\\"] = ["model/"];
    file_put_contents($composerFile, json_encode($composerConfig, JSON_PRETTY_PRINT));
    $this->modifyFile($composerFile);
    shell_exec("composer dump-autoload");
  }
}
