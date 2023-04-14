<?php

use Core\Db\Cron;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once __DIR__ . "/init.php";

$logger = new Logger("cron_run_queue_priority", [
  new RotatingFileHandler(
    filename : ROOT_DIR. "logs/cron_run_queue_priority/cron_run_queue_priority.log",
    maxFiles : 2,
    level : Logger::DEBUG,
  ),
  new StreamHandler(
    stream : "php://stdout",
    level : Logger::DEBUG,
  ),
]);
//si le fichier lock existe, on quitte 
if (file_exists(ROOT_DIR . "_cron_run_queue.lock")) {
  $logger->notice("Un fichier de lock est actuellement présent...");
  exit;
}

//On créé le lock
$lockFile = fopen(ROOT_DIR . "_cron_run_queue.lock", "w");
fwrite($lockFile, date('Y-m-d H:i:s'));
fclose($lockFile);
$logger->info("Traitement des tâches.");
//on prend le premier script qui ne soit pas "en cours"
$notInProgress = true;
$tasks = Cron::getQueue($notInProgress,true);

if (empty($tasks[0])) {
  $logger->info("Aucune tâche à traiter.");
  exit;
}

$T = $tasks[0];

$cmd = escapeshellcmd($T['script'] . " " . $T['params']);

$logger->info("Lancement de la tâche",[ "id" => $T['id'], "script" => $T['script'], "params" => $T['params']]);

$id = $T['id'];
Cron::setStatus($id, 1);
Cron::setStartDate($id, date('Y-m-d H:i:s'));
exec($cmd, $output, $retval);
Cron::setLog($id, implode(PHP_EOL,$output));
Cron::setEndDate($id, date('Y-m-d H:i:s'));
Cron::setStatus($id, 2);

//on supprime le lock
@unlink(ROOT_DIR . "_cron_run_queue.lock");
die;