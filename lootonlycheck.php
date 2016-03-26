<?php
require_once("header.php");

function initGrists() {
	$result2 = mysql_query("SELECT * FROM `Captchalogue` LIMIT 1;"); //document grist types now so we don't have to do it later
  $reachgrist = False;
  $terminateloop = False;
  $totalgrists = 0;
  while (($col = mysql_fetch_field($result2)) && $terminateloop == False) {
    $gristcost = $col->name;
    $gristtype = substr($gristcost, 0, -5);
    if ($gristcost == "Build_Grist_Cost") { //Reached the start of the grists.
      $reachgrist = True;
    }
    if ($gristcost == "End_Of_Grists") { //Reached the end of the grists.
      $reachgrist = False;
      $terminateloop = True;
    }
    if ($reachgrist == True) {
      $gristname[$totalgrists] = $gristtype;
      $totalgrists++;
    }
  }
  return $gristname;
}

if (empty($_SESSION['username'])) {
  echo "Log in to do stuff.</br>";
} else {
	  if ($userrow['session_name'] != "Developers") {
    echo "Hey! This tool is for the developers only. Nice try, pal.";
  } else {
  	$gristname = initGrists();
  	$totalgrists = count($gristname);
  	$i = 0;
  	while ($i < $totalgrists) {
  		$maxgain[$gristname[$i] . '_Cost'] = 0;
  		$items[$gristname[$i] . '_Cost'] = 0;
  		$i++;
	  }
	  $gateitems[1] = 0;
	  $gateitems[3] = 0;
	  $gateitems[5] = 0;
$result = mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`lootonly` = 1");
echo "running through lootonlies</br>";
while ($row = mysql_fetch_array($result)) {
	$maxgaint = $maxgain;
	echo $row['name'] . " acknowledged";
  $i = 0;
  $totalcost = 0;
  while ($i < $totalgrists) {
  	$gristcost = $gristname[$i] . '_Cost';
  	if ($row[$gristcost] > 0) {
	  	if ($row[$gristcost] > $maxgaint[$gristcost] && $row[$gristcost] <= 800000) {
	  		$maxgaint[$gristcost] = $row[$gristcost];
	  	}
  		$totalcost += $row[$gristcost];
  		$items[$gristcost]++;
  	}
  	$i++;
  }
  if ($totalcost >= 5 && $totalcost <= 2000) {
  	$gateitems[1]++;
  	echo " (gate 1)";
  }
  if ($totalcost >= 1000 && $totalcost <= 250000) {
  	$gateitems[3]++;
  	echo " (gate 3)";
  }
  if ($totalcost >= 100000 && $totalcost <= 800000) {
  	$gateitems[5]++;
  	echo " (gate 5)";
  }
  if ($totalcost < 5 || $totalcost > 800000) {
  	$defunctitems++;
  	echo ", but it can't be looted";
  } else $maxgain = $maxgaint;
  echo "</br>";
}
echo "ANALYSIS:</br>";
echo strval($gateitems[1]) . " gate 1 items</br>";
echo strval($gateitems[3]) . " gate 3 items</br>";
echo strval($gateitems[5]) . " gate 5 items</br>";
echo strval($defunctitems) . " gate x items</br>";
$i = 0;
  	while ($i < $totalgrists) {
  		echo $gristname[$i] . " has " . strval($items[$gristname[$i] . '_Cost']) . " items and " . strval($maxgain[$gristname[$i] . '_Cost']) . " max gain</br>";
  		$i++;
	  }
}
}
?>