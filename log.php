<?php
function logEvent($event,$username) {
  require_once("includes/SQLconnect.php");

  $result = mysql_query("SELECT * FROM Logs");
  $logslots = mysql_num_fields($result);
  $logslots--; //Remove the username field from consideration.
  $i=1;
  while($row = mysql_fetch_array($result)) {
    if ($row['username'] == $username) {
      $finalrow = $row;
    }
  }
  while ($i < $logslots) {
    $logindex = $logslots - $i;
    $logstr = "log" . strval($logindex);
    $replacestr = "log" . strval($logindex+1);
    if (!empty($finalrow[$logstr])) {
      $log = $finalrow[$logstr];
      mysql_query("UPDATE `Logs` SET `" . $replacestr . "` = '" . $log . "' WHERE `Logs`.`username` = '" . $username . "' LIMIT 1 ;");
    }
    $i++;
  }
  mysql_query("UPDATE `Logs` SET `log1` = '" . $event . "' WHERE `Logs`.`username` = '" . $username . "' LIMIT 1 ;");
}
?>