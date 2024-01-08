<?php

namespace Core\Command;

use Core\Common\Site;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class GenerateSiteMap extends \Core\CommandHandler
{

  protected $logger;

  protected const SITEMAP_FILE = ROOT_DIR . "sitemap.xml";

  function __construct()
  {
    $this->logger = new Logger("sitemap", [
      new RotatingFileHandler(
        filename: ROOT_DIR . "var/log/sitemap/sitemap.log",
        maxFiles: 3,
        level: Logger::DEBUG,
      ),
      new StreamHandler(
        stream: "php://stdout",
        level: Logger::DEBUG,
      ),
    ]);
  }

  private function addToSiteMap($content)
  {
    file_put_contents(self::SITEMAP_FILE, $content . PHP_EOL, FILE_APPEND);
  }

  public function execute()
  {
    $this->logger->info("Début génération sitemap");
    file_put_contents(self::SITEMAP_FILE, "", 0);
    $dateYmd = date("Y-m-d");
    $this->addToSiteMap("<?xml version=\"1.0\" encoding=\"UTF-8\"?>");
    $this->addToSiteMap("<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">");
    try {
      $routing = Site::getRouting();
      foreach ($routing->getRoutesForSitemap() as $route) {
        $config = $route["sitemap"];
        $this->logger->info("Ajout au sitemap de la route : " . $route["alias"]);
        if (is_array($config)) {
          $variables = [];
          foreach ($config as $var => $varConf) {
            foreach ($varConf["function"](...($varConf["args"] ?? [])) as $value) {
              $variables[$var] = $value;
              $url = $routing->get(
                alias: $route["alias"],
                absolute: true,
                variables: [
                  $var => $value,
                ],
              );
              $this->addToSiteMap("\t<url>");
              $this->addToSiteMap("\t\t<loc>$url</loc>");
              $this->addToSiteMap("\t\t<lastmod>$dateYmd</lastmod>");
              $this->addToSiteMap("\t</url>");
            }
          }
        } else {
          $url = $routing->get(
            alias: $route["alias"],
            absolute: true,
          );
          $this->addToSiteMap("\t<url>");
          $this->addToSiteMap("\t\t<loc>$url</loc>");
          $this->addToSiteMap("\t\t<lastmod>$dateYmd</lastmod>");
          $this->addToSiteMap("\t</url>");
        }
        $this->logger->info("Route ajoutée au sitemap");
      }
    } catch (\Exception $e) {
      $this->logger->error($e->getMessage());
    } finally {
      $this->addToSiteMap("</urlset>");
      $this->logger->info("Fin génération sitemap");
    }
  }
}
