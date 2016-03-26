<?php
function randomClass($session) { //Generate a random class, trying to keep things even throughout the session
  $con = mysql_connect("localhost","theovers_DC","pi31415926535");
  if (!$con)
    {
      echo "Connection failed.\n";
      die('Could not connect: ' . mysql_error());
    }

  mysql_select_db("theovers_HS", $con);

  $sesresult = mysql_query("SELECT `username`,`Class` FROM `Players` WHERE `Players`.`session_name` = '$session' ;");
  $classresult = mysql_query("SELECT `Class` FROM `Titles` ;");
  while ($row = mysql_fetch_array($sesresult)) {
    if ($row['Class'] != "") $classcount[$row['Class']]++;
    }
  $min = 999;
  $count = 0;
  while ($row = mysql_fetch_array($classresult)) {
    if ($row['Class'] != "General" && $row['Class'] != "Adjective" && $row['Class'] != "Denizen") {
      //if ($classcount[$row['Class']] < $min) $min = $classcount[$row['Class']];
      $classname[$count] = $row['Class'];
      $count++;
      }
    }
  $min = min($classcount);
  $subcount = 0;
  $available = 0;
  while ($subcount < $count) {
    if ($classcount[$classname[$subcount]] == $min) {
      $classenabled[$classname[$subcount]] = True;
      $available++;
      }
    $subcount++;
    }
  $therandomclass = rand(0,$available);
  $subcount = 0;
  $skipcount = 0;
  while ($subcount < $count) {
    if ($classenabled[$classname[$subcount]] == True) {
      if ($skipcount == $therandomclass) {
        $theclassname = $classname[$subcount];
	$subcount = $count;
        }
      $skipcount++;
      }
    $subcount++;
    }
  if (empty($theclassname)) $theclassname = "ERROR";

  mysql_close($con);
  return $theclassname;
}

function randomAspect($session) { //Generates an aspect same as above
  $con = mysql_connect("localhost","theovers_DC","pi31415926535");
  if (!$con)
    {
      echo "Connection failed.\n";
      die('Could not connect: ' . mysql_error());
    }

  mysql_select_db("theovers_HS", $con);

  $sesresult = mysql_query("SELECT `username`,`Aspect` FROM `Players` WHERE `Players`.`session_name` = '$session' ;");
  $classresult = mysql_query("SELECT * FROM `Titles` LIMIT 1;");
  while ($row = mysql_fetch_array($sesresult)) {
    if ($row['Aspect'] != "") $classcount[$row['Aspect']]++;
    }
  $min = 999;
  $count = 0;
    while ($col = mysql_fetch_field($classresult)) {
      $aspect = $col->name;
      if ($aspect == "Breath") $reachaspect = True;
      if ($aspect == "General") $reachaspect = False;
      if ($reachaspect == True) {
        $classname[$count] = $aspect;
        $count++;
      }
    }
  $min = min($classcount);
  $subcount = 0;
  $available = 0;
  while ($subcount < $count) {
    if ($classcount[$classname[$subcount]] == $min) {
      $classenabled[$classname[$subcount]] = True;
      $available++;
      }
    $subcount++;
    }
  $therandomclass = rand(0,$available);
  $subcount = 0;
  $skipcount = 0;
  while ($subcount < $count) {
    if ($classenabled[$classname[$subcount]] == True) {
      if ($skipcount == $therandomclass) {
        $theclassname = $classname[$subcount];
	$subcount = $count;
        }
      $skipcount++;
      }
    $subcount++;
    }
  if (empty($theclassname)) $theclassname = "ERROR";

  mysql_close($con);
  return $theclassname;
}

function randomGristtype($session) {
  $con = mysql_connect("localhost","theovers_DC","pi31415926535");
  if (!$con)
    {
      echo "Connection failed.\n";
      die('Could not connect: ' . mysql_error());
    }

  mysql_select_db("theovers_HS", $con);
  
  $gristresult = mysql_query("SELECT * FROM Grist_Types");
  $grists = 0;
  while ($gristrow = mysql_fetch_array($gristresult)) {
  	$gristname[$grists] = $gristrow['name'];
  	$gristcount[$gristname[$grists]] = 0;
  	$grists++;
  }
  $count = 0;
  $available = 0;
  $gristpick = mysql_query("SELECT `username`, `grist_type` FROM `Players` WHERE `Players`.`session_name` = '$session' ;");
  while ($sesrow = mysql_fetch_array($gristpick)) {
  	$gristcount[$sesrow['grist_type']]++;
  }
  $gmin = min($gristcount);
  while ($count < $grists) {
  	if ($gristcount[$gristname[$count]] == $gmin) {
  		$gristavailable[$count] = True;
  		$available++;
  	}
  	$count++;
  }
  $therandomgrist = rand(0,$available);
  $subcount = 0;
  $skipcount = 0;
  while ($subcount < $count) {
    if ($gristavailable[$subcount] == True) {
      if ($skipcount == $therandomgrist) {
        $theclassname = $gristname[$subcount];
	      $subcount = $count;
        }
      $skipcount++;
      }
    $subcount++;
    }
  if (empty($theclassname)) $theclassname = "ERROR";
  mysql_close($con);
  return $theclassname;
}

function randomLandname($gristtype, $aspect, $barren) {
	$landresult1 = mysql_query("SELECT * FROM Landjectives WHERE `Landjectives`.`grist` = '$gristtype' OR `Landjectives`.`grist` = 'Any' ;");
	$landresult2 = mysql_query("SELECT * FROM Landjectives WHERE `Landjectives`.`aspect` = '$aspect' OR `Landjectives`.`aspect` = 'Any' ;");
	$count1 = 0;
	$count2 = 0;
	while ($landrow = mysql_fetch_array($landresult1)) {
		$count1++;
		$landname1[$count1] = $landrow['name'];
	}
	while ($landrow = mysql_fetch_array($landresult2)) {
		$count2++;
		$landname2[$count2] = $landrow['name'];
	}
	$landom1 = rand(1,$count1);
	$landom2 = rand(1,$count2);
	$landresult1 = mysql_query("SELECT * FROM Landjectives WHERE `Landjectives`.`name` = '" . $landname1[$landom1] . "';");
	$landresult2 = mysql_query("SELECT * FROM Landjectives WHERE `Landjectives`.`name` = '" . $landname2[$landom2] . "';");
	$landrow1 = mysql_fetch_array($landresult1);
	$landrow2 = mysql_fetch_array($landresult2);
	if ($landrow2['priority'] > $landrow1['priority']) {
		$finalname[1] = $landrow2['name'];
		$finalname[2] = $landrow1['name'];
	} else {
		$finalname[1] = $landrow1['name'];
		$finalname[2] = $landrow2['name'];
	}
	mysql_close($con);
	return $finalname;
}

?>