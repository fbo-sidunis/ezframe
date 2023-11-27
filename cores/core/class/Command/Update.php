<?php

namespace Core\Command;

use Core\Common\Site;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Update extends \Core\CommandHandler
{

  protected $logger;

  function __construct()
  {
    $this->logger = new Logger("update", [
      new RotatingFileHandler(
        filename: ROOT_DIR . "logs/update/update.log",
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
    $this->updateTo1_5();
    $this->updateTo1_7();
    $this->logger->info("End update");
  }

  public function updateTo1_5()
  {
    $envFile = ROOT_DIR . ".env";
    if (!file_exists($envFile)) {
      return;
    }
    $siteConfigFile = ROOT_DIR . "config/site_config.json";
    $env = $siteConfigFile["env"] ?? "dev";
    unset($siteConfigFile["env"]);
    file_put_contents($siteConfigFile, json_encode($siteConfigFile, JSON_PRETTY_PRINT));
    file_put_contents($envFile, "APP_ENV=$env\n");
    echo "+ \"/.env\"";
    echo "+ \"/config/site_config.json\"\n";
  }

  public function updateTo1_7()
  {
    $composerFile = ROOT_DIR . "composer.json";
    $composerConfig = json_decode(file_get_contents($composerFile), true);
    $composerConfig["autoload"]["psr-4"]["Model\\"] = ["model/"];
    file_put_contents($composerFile, json_encode($composerConfig, JSON_PRETTY_PRINT));
    echo "+ \"/composer.json\"\n";
  }
}
