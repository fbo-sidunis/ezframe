<?php

namespace Helper;

class IntlDateFormatter extends \IntlDateFormatter{
  private const DEFAULT_LOCALE = "fr_FR";
  private const DEFAULT_DATE_TYPE = self::FULL;
  private const DEFAULT_TIME_TYPE = self::FULL;
  private $sidunisTime = null;


  public static function createWithTime(
  $time,
  $locale = null,
  $dateType = null,
  $timeType = null,
  $timezone = null,
  $calendar = null,
  $pattern = ''){
    $formatter = new self(
      $locale ?? self::DEFAULT_LOCALE,
      $dateType ?? self::DEFAULT_DATE_TYPE,
      $timeType ?? self::DEFAULT_TIME_TYPE,
      $timezone ?? null,
      $calendar ?? null,
      $pattern ?? ""
    );
    $formatter->setSidunisTime($time);
    return $formatter;
  }

  public function formatWithPattern($pattern,$time = null){
    $_pattern = $this->getPattern();
    if ($time === null){
      $time = $this->getSidunisTime() ?: time();
    }
    $this->setPattern($pattern);
    $formattedDate = $this->format($time);
    $this->setPattern($_pattern);
    return $formattedDate;
  }

  /**
   * Get the value of sidunisTime
   */ 
  public function getSidunisTime()
  {
    return $this->sidunisTime;
  }

  /**
   * Set the value of sidunisTime
   *
   * @return  self
   */ 
  public function setSidunisTime($sidunisTime)
  {
    $this->sidunisTime = $sidunisTime;

    return $this;
  }
}