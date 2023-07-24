<?php

namespace Helper;

class ChronoDebugger
{
  private array $times = [];
  private array $memory = [];

  public function start(string $name)
  {
    $this->times[$name] = microtime(true);
  }

  public function stop(string $name)
  {
    if (!isset($this->times[$name])) {
      return;
    }
    $time = microtime(true) - $this->times[$name];
    unset($this->times[$name]);
    $this->memory[$name][] = $time;
  }

  public function getLastTime(string $name): float
  {
    return $this->memory[$name][count($this->memory[$name]) - 1];
  }
  public function getLastTimeMinuteSeconds(string $name): string
  {
    return $this->formatTimeMinutesSeconds($this->getLastTime($name));
  }

  public function getTimes(string $name): array
  {
    return $this->memory[$name] ?? [];
  }

  public function getAverage(string $name): float
  {
    $times = $this->getTimes($name);
    return array_sum($times) / count($times);
  }

  public function getAllAverage(): array
  {
    $averages = [];
    foreach ($this->memory as $name => $times) {
      $averages[$name] = $this->formatTime(array_sum($times) / count($times));
    }
    return $averages;
  }

  public function getFullTimesSpent(): array
  {
    $fullTimesSpent = [];
    foreach ($this->memory as $name => $times) {
      $fullTimesSpent[$name] = $this->formatTime(array_sum($times));
    }
    return $fullTimesSpent;
  }

  private function formatTime(float $time): string
  {
    return number_format($time * 1000, 2) . "ms";
  }

  private function formatTimeMinutesSeconds(float $time): string
  {
    return gmdate("H\\h i\\m s\\s", intval($time));
  }
}
