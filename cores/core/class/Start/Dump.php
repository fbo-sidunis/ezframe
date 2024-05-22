<?php

namespace Core\Start;

use Core\CliDumper;
use Core\HtmlDumper;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\VarDumper;

class Dump
{
  protected static $dumper;
  protected static $cloner;
  public static function init()
  {
    self::$cloner = new VarCloner();
    self::$dumper = 'cli' === PHP_SAPI ? new CliDumper() : new HtmlDumper();
    VarDumper::setHandler(function (...$vars) {
      $backtrace = debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT | \DEBUG_BACKTRACE_IGNORE_ARGS, 9);
      self::dump($backtrace, self::$dumper, ...$vars);
    });
  }

  public static function dump($backtrace, $dumper, ...$vars)
  {
    $name = $backtrace[2]["file"];
    $line = $backtrace[2]["line"];
    $template = null;
    $key_ = 0;
    foreach ($backtrace as $i => $trace) {
      if (!$template && isset($trace['object']) && $trace['object'] instanceof \Twig\Template) {
        $template = $trace['object'];
        $key_ = $i - 1;
      }
    }
    // update template filename
    if (null !== $template) {
      $name = $template->getSourceContext()->getPath();
      $line = $template->getDebugInfo()[$backtrace[$key_]['line'] ?? -1] ?? null;
    }
    $dumper->setLine($name, $line);
    if (count($vars) > 1) {
      $dumper->dump(self::$cloner->cloneVar($vars[0]));
      return $vars[0];
    } else {
      foreach ($vars as $var) {
        $dumper->dump(self::$cloner->cloneVar($var));
      }
      return $vars;
    }
  }

  public static function dumpHtml(...$vars)
  {
    $backtrace = debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT | \DEBUG_BACKTRACE_IGNORE_ARGS, 9);
    return self::dump($backtrace, new HtmlDumper(), ...$vars);
  }

  public static function dumpCli(...$vars)
  {
    $backtrace = debug_backtrace(\DEBUG_BACKTRACE_PROVIDE_OBJECT | \DEBUG_BACKTRACE_IGNORE_ARGS, 9);
    return self::dump($backtrace, new CliDumper(), ...$vars);
  }
}
