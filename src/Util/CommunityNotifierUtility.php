<?php

namespace Drupal\community_notifier\Util;

class CommunityNotifierUtility {
  /**
   * @param $timestamp Unix timestamp
   * @return bool
   */
  public static function isCurrentDayOfWeek($timestamp){
    $currentDay = date("N");

    if ($currentDay == date('N', $timestamp)){
      return TRUE;
    }
    return FALSE;
  }

  /**
   * @param $frequency (daily, weekly)
   * @return array of date time range
   */
  public static function frequencyDateRange($frequency){
    $end = strtotime(date('Y-m-d 23:59:59', strtotime('-1 days'))); //change to -1..-8 added for testing purposes only
    if ($frequency == 'daily'){
      $start = strtotime(date('Y-m-d 00:00:00', strtotime('-1 days'))); //change to -1

      return [$start, $end];
    }
    else{
      $start = strtotime(date('Y-m-d 00:00:00', strtotime('-7 days')));

      return [$start, $end];
    }
  }
  public static function mergeComments(array $comments){
    $bodyTemp = [];
    var_dump(count($comments));die();
    foreach ($comments as $comment){
      array_push($bodyTemp, $comment['body']);
    }

    return implode('<br>', $bodyTemp);
  }
}