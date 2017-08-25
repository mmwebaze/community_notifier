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
}