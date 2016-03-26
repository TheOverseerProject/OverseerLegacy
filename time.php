<?php
function produceTimeDifference($oldtime,$descriptor) { //Produces a string detailing how long it has been since $oldtime. $descriptor details what $oldtime is.
  $time = time() - $oldtime;
  $seconds = $time % 60;
  $minutes = floor($time/60) % 60;
  $hours = floor($time/3600) % 24;
  $days = floor($time/86400) % 7;
  $years = floor($time/31536000);
  $timestr = strval($years) . " years, " . strval($days) . " days, " . strval($hours) . " hours, " . strval($minutes) . " minutes, and " . strval($seconds) . " seconds since " . $descriptor;
  return $timestr;
}

function produceTimeSinceUpdate($oldtime) { //Produces a string detailing how long it has been since $oldtime. Tailored to submissions.php.
  if ($oldtime == -1) $timestr = "Submission has not been updated since the Alpha 1.5 release.";
  else {
  $time = time() - $oldtime;
  $seconds = $time % 60;
  $minutes = floor($time/60) % 60;
  $hours = floor($time/3600) % 24;
  $days = floor($time/86400) % 365;
  $years = floor($time/31536000);
  $timestr = "Submission was last updated ";
  if ($years > 0) $timestr = $timestr . strval($years) . " years, ";
  if ($days > 0) $timestr = $timestr . strval($days) . " days, ";
  if ($hours > 0) $timestr = $timestr . strval($hours) . " hours, ";
  if ($minutes > 0) $timestr = $timestr . strval($minutes) . " minutes, and ";
  $timestr = $timestr . strval($seconds) . " seconds ago.";
  }
  return $timestr;
}

function produceTimeString($time) { //Produces a string based on how many seconds there are in $time, in traditional (sorta) Homestuck 0000:000:00:00:00 format
  $seconds = $time % 60;
  $minutes = floor($time/60) % 60;
  $hours = floor($time/3600) % 24;
  $days = floor($time/86400) % 7;
  $years = floor($time/31536000);
  $yearstr = strval($years);
  while (strlen($yearstr) < 4) $yearstr = "0" . $yearstr;
  $daystr = strval($days);
  while (strlen($daystr) < 3) $daystr = "0" . $daystr;
  $hourstr = strval($hours);
  while (strlen($hourstr) < 2) $hourstr = "0" . $hourstr;
  $minutestr = strval($minutes);
  while (strlen($minutestr) < 2) $minutestr = "0" . $minutestr;
  $secondstr = strval($seconds);
  while (strlen($secondstr) < 2) $secondstr = "0" . $secondstr;
  $timestr = $yearstr . ":" . $daystr . ":" . $hourstr . ":" . $minutestr . ":" . $secondstr;
  return $timestr;
}

function produceMinutes($time) { //Produces a string based on how many seconds there are in $time, in traditional (sorta) Homestuck 0000:000:00:00:00 format
  $seconds = $time % 60;
  $minutes = floor($time/60) % 60;
  $hours = floor($time/3600) % 24;
  $days = floor($time/86400) % 7;
  $years = floor($time/31536000);
  $yearstr = strval($years);
  while (strlen($yearstr) < 4) $yearstr = "0" . $yearstr;
  $daystr = strval($days);
  while (strlen($daystr) < 3) $daystr = "0" . $daystr;
  $hourstr = strval($hours);
  while (strlen($hourstr) < 2) $hourstr = "0" . $hourstr;
  $minutestr = strval($minutes);
  while (strlen($minutestr) < 2) $minutestr = "0" . $minutestr;
  $secondstr = strval($seconds);
  while (strlen($secondstr) < 2) $secondstr = "0" . $secondstr;
  return $minutestr;
}

function produceSeconds($time) { //Produces a string based on how many seconds there are in $time, in traditional (sorta) Homestuck 0000:000:00:00:00 format
  $seconds = $time % 60;
  $minutes = floor($time/60) % 60;
  $hours = floor($time/3600) % 24;
  $days = floor($time/86400) % 7;
  $years = floor($time/31536000);
  $yearstr = strval($years);
  while (strlen($yearstr) < 4) $yearstr = "0" . $yearstr;
  $daystr = strval($days);
  while (strlen($daystr) < 3) $daystr = "0" . $daystr;
  $hourstr = strval($hours);
  while (strlen($hourstr) < 2) $hourstr = "0" . $hourstr;
  $minutestr = strval($minutes);
  while (strlen($minutestr) < 2) $minutestr = "0" . $minutestr;
  $secondstr = strval($seconds);
  while (strlen($secondstr) < 2) $secondstr = "0" . $secondstr;
  return $secondstr;
}

function produceIST($init) { //Returns Incipisphere Standard Time when given the system initialization time. Doesn't really do much!
  $time = time() - $init;
  $seconds = $time % 60;
  $minutes = floor($time/60) % 60;
  $hours = floor($time/3600) % 24;
  $days = floor($time/86400);
  $timestr = $days . " days since initiation at " . strval($hours) . ":" . strval($minutes) . ":" . strval($seconds) . " IST";
  return $timestr;
}

function initTime($con) { //Grabs the initialization time from the System table (in seconds since the Epoch)
  mysql_select_db("theovers_HS", $con);
  $result = mysql_query("SELECT * FROM System");
  $row = mysql_fetch_array($result);
  return $row['time'];
}
?>