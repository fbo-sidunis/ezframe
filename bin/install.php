<?php
$install_files = realpath(__DIR__ . "/../install");
$project_path = realpath(__DIR__ . "/../../../../");
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
  $project_path_ = str_replace("/", "\\", $project_path);
  $install_files_ = str_replace("/", "\\", $install_files);
  shell_exec("xcopy /i /e  \"$install_files_\" \"$project_path_\" /Y");
} else {
  $install_files_ = $install_files . "/*";
  $project_path_ = $project_path . "/";
  shell_exec("cp -r $install_files_ $project_path_");
}
$composerFile = $project_path . "/composer.json";
$composerConfig = json_decode(file_get_contents($composerFile), true);
$composerConfig["autoload"]["psr-4"]["App\\"] = ["app/"];
$composerConfig["autoload"]["psr-4"]["Helper\\"] = ["helpers/"];
file_put_contents($composerFile, json_encode($composerConfig, JSON_PRETTY_PRINT));
copy($project_path . "/.env.example", $project_path . "/.env");
unlink($project_path . "/.env.example");
shell_exec("composer dump-autoload");
chmod($project_path . "/bin/ezframe", 0755);
