<?php

use Core\Common\Site;
use Core\Route;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require __DIR__ . "/../../../autoload.php";

Site::initCli(__DIR__ ."/../../../../");

$logger = new Logger("sitemap", [
  new RotatingFileHandler(
    filename : ROOT_DIR. "logs/sitemap/sitemap.log",
    maxFiles : 3,
    level : Logger::DEBUG,
  ),
  new StreamHandler(
    stream : "php://stdout",
    level : Logger::DEBUG,
  ),
]);

$logger->info("Début génération sitemap");
$siteMapFile = ROOT_DIR . "sitemap.xml";
file_put_contents($siteMapFile, "",0);
function addToSiteMap($content){
  global $siteMapFile;
  file_put_contents($siteMapFile, $content . PHP_EOL,FILE_APPEND);
}
$dateYmd = date("Y-m-d");
addToSiteMap("<?xml version=\"1.0\" encoding=\"UTF-8\"?>");
addToSiteMap("<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">");
try{
  $routing = Site::getRouting();
  foreach($routing->getRoutesForSitemap() as $route){
    $config = $route["sitemap"];
    $logger->info("Ajout au sitemap de la route : " . $route["alias"]);
    if (is_array($config)){
      $variables = [];
      foreach ($config as $var=>$varConf){
        foreach($varConf["function"](...($varConf["args"] ?? [])) as $value){
          $variables[$var] = $value;
          $url = $routing->get(
            alias : $route["alias"],
            absolute : true,
            variables : [
              $var => $value,
            ],
          );
          addToSiteMap("\t<url>");
          addToSiteMap("\t\t<loc>$url</loc>");
          addToSiteMap("\t\t<lastmod>$dateYmd</lastmod>");
          addToSiteMap("\t</url>");
        }
      }
    }else{
      $url = $routing->get(
        alias : $route["alias"],
        absolute : true,
      );
      addToSiteMap("\t<url>");
      addToSiteMap("\t\t<loc>$url</loc>");
      addToSiteMap("\t\t<lastmod>$dateYmd</lastmod>");
      addToSiteMap("\t</url>");
    }
    $logger->info("Route ajoutée au sitemap");
  }
}catch(\Exception $e){
  $logger->error($e->getMessage());
}finally{
  addToSiteMap("</urlset>");
  $logger->info("Fin génération sitemap");
}