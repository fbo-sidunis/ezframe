<?php

/**
 * Fonctions de gestion des dates en PHP
 */

/**
* 
* @param mixed $timestamp 
* @return string 
*/
function timeStampToDateFR($timestamp) {
  return date('Y-m-d H:i:s', $timestamp);
}

/**
 * 
 * @param mixed $timestamp 
 * @param bool $withTime 
 * @return string 
 */
function timeStampToDateEN($timestamp, $withTime = TRUE) {
  if ($withTime == TRUE) {
    return date('Y-m-d H:i::s', $timestamp);
  } else {
    return date('Y-m-d', $timestamp);
  }
}

/**
 * Converti une date au format Y-m-d en Timestamp
 * @param string $strDate
 * @return int
 */
function dateYMD_toTimestamp($strDate) {
  $date = new DateTime($strDate);
  return $date->getTimestamp();
}

/**
 * Date au format Y-m-d
 * @param string $dateYMD
 * @return string date au format d/m/Y
 */
function dateENtoFR($dateYMD) {
  $exp = explode('-', $dateYMD);
  return $exp[2] . '/' . $exp[1] . '/' . $exp[0];
}

/**
 * Retour le premier jour d'une semaine donnée
 * @param int|string $year
 * @param int|string $weekNumber
 * @return string.
 */
function getFirstDayOfWeek($year, $weekNumber = 1) {
  $date = new DateTime();
  $date->setISODate($year, $weekNumber);
  return $date->format('Y-m-d');
}

/**
 * 
 * @param mixed $year 
 * @param int $month 
 * @return array 
 */
function getAllWeeksInMonth($year, $month = 1) {
  $year = !empty($year) ? $year : date('Y');
  $month = $month < 10 ? '0' . $month : $month;
  $date = strtotime(date('Y-m-d', strtotime($year . '-' . $month . '-01')));
  $firstOfMonth = date('W', ($date));
  $arrWeeks = [];
  $nbW = nbWeeksOfMonth($date) - 1;
  for ($m = $firstOfMonth; $m <= $firstOfMonth + $nbW; $m++) {
    $arrWeeks[] = $m;
  }
  return $arrWeeks;
}

/**
 * 
 * @param mixed $start 
 * @return mixed 
 */
function nbWeeksOfMonth($start) {
  $week_start = date('W', $start); // note that ISO weeks start on Monday
  $end = date("Y-m-t", $start);
  $week_end = date('W', strtotime($end));
  return $week_end - $week_start + 1;
}

/**
 * 
 * @param mixed $year 
 * @param int $weekNumber 
 * @return array 
 */
function getAllDaysOfWeek($year, $weekNumber = 1) {
  $date = new DateTime();
  $date->setISODate($year, $weekNumber);
  $week = [];
  for ($i = 1; $i < 8; $i++) {
    $day = $i;
    $week[$day] = $date->format('Y-m-d');
    $date->modify('+1 day');
  }
  return $week;
}
