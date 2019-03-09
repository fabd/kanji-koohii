<?php
/** 
 * Date/time helpers that are not generic enough to be in core/helper.
 *
 * @author   Fabrice Denis
 */

/**
 * Helper adds the user's timezone to localize the UTC timestamp.
 *
 * For MySQL DATETIME, get timestamp with UNIX_TIMESTAMP(datetime_col)
 *
 * @see format_readable_time()
 */
function format_readable_local_time($timestamp)
{
  $timediff = (int)sfContext::getInstance()->getUser()->getUserTimeZone();
  return format_readable_time($timestamp, $timediff);
}

/**
 * Format date and time in a simple, readable format like PunBB forum does.
 *
 * Unix timestamp is localized according to the timezone.
 *
 * Example outputs:
 *
 *   12:35 am
 *   Yesterday, 7:48 am
 *   April 30, 3:56 pm
 *   2006 June 16, 5:45 pm
 *
 * @param  int     $timestamp   Timestamp shoud be unix time GMT (eg. php time())
 * @param  int     $timezone    Time zone difference from GMT
 *
 */
function format_readable_time($timestamp, $timediff = 0, $date_only = false)
{
	if (empty($timestamp)) {
		return 'Never';
  }
  
  $timediff = (int)$timediff * 3600;
	$timestamp += $timediff;

	$s = '';
	$now = time() + $timediff; // localized time
	$date = date('Y-m-d', $timestamp);
	$today = date('Y-m-d', $now);
	$yesterday = date('Y-m-d', $now-86400);

	// today ?
	if ($date == $today) {
		$s = '';  // 'Today'
  }
	// yesterday ?
	else if ($date == $yesterday) {
		$s = 'Yesterday';
  }
	else {
		// different year ?
		$date_y = date('Y', $timestamp);
		$today_y = date('Y', $now);
		if ($date_y != $today_y) {
			$s = $date_y.' ';
    }
		// month, day
		$s .= date('F d', $timestamp);
	}

	if (!$date_only) {
		$s .= ($s ? ', ' : '') . date('g:i a', $timestamp);
  }

	return $s;
}
