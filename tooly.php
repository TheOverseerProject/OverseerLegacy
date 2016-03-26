<?php
require 'designix.php';
require 'additem.php';
require 'monstermaker.php'; //lol blade cloud
require_once 'includes/effectprinter.php'; //for printing effects, consolidated into an include for simplicity (also includes glitches)
require_once("header.php");
require_once("includes/grist_icon_parser.php");
$max_items = 50;

function initGrists() {
	$result2 = mysql_query("SELECT * FROM `Captchalogue` LIMIT 1"); //document grist types now so we don't have to do it later
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

  echo "<!DOCTYPE html><html><head><style>gristvalue{color: #FF0000; font-size: 60px;}</style><style>gristvalue2{color: #0FAFF1; font-size: 60px;}</style><style>itemcode{font-family:'Courier New'}</style></head><body>";
  $gristed = false; //will be set to true when grist types are initialized

  //--Begin designix code here.--
    //Grabs names from forms and looks up their captcha codes and stores to $code1 and $code2
  if (!empty($_POST['code1']) && !empty($_POST['code2'])) {
      $itemresult = mysql_query("SELECT * FROM `Captchalogue` WHERE `name` LIKE '" . $_POST['code1'] . "'");
      while ($itemrow = mysql_fetch_array($itemresult)){
      $code1 = $itemrow['captchalogue_code'];
      }
      $itemresult = mysql_query("SELECT * FROM `Captchalogue` WHERE `name` LIKE '" . $_POST['code2'] . "'");
      while ($itemrow = mysql_fetch_array($itemresult)){
      $code2 = $itemrow['captchalogue_code'];
      }
        if (!empty($code1) && !empty($code2)) { //User is performing designix operations.
  	$letthrough = false;
      if ($_POST['combine'] == "or") {
	$code = orcombine($code1, $code2);
      } else {
	$code = andcombine($code1, $code2);
      }
  }
      else {echo "<p>One of these items didnt show up!<p>";}
  }


    //Takes $code1 and $code2 and performs the combination process

//HOLOPAD CODE--------------------------------------------------------------------------------
  if (!empty($code)) { //User is using the holopad.
    $holoCode = $code;
} else if (!empty($_POST['holocode'])) {
      $itemresult = mysql_query("SELECT * FROM `Captchalogue` WHERE `name` LIKE '" . $_POST['holocode'] . "'");
      while ($itemrow = mysql_fetch_array($itemresult)){
      $holoCode = $itemrow['captchalogue_code'];
      }
  }
if (!empty($holoCode)) {
    $itemresult = mysql_query("SELECT * FROM `Captchalogue` WHERE `captchalogue_code` = '" . $holoCode . "'");
    $itemfound = False;
    while ($itemrow = mysql_fetch_array($itemresult)) {
	$itemfound = True;
	$nothing = True;
	$itemname = $itemrow['name'];
	$itemname = str_replace("\\", "", $itemname); //Remove escape characters.
	if ($itemrow['art'] != "") echo '<img src="/Images/Items/' . $itemrow['art'] . '" title="Image by ' . $itemrow['credit'] . '"></br>';
	if (substr($itemname, 0, 2) == "A " || substr($itemname, 0, 3) == "An " || substr($itemname, 0, 4) == "The ")
	echo "The holopad displays $itemname. It also prints out a short description:</br>";
	else
	echo "<b>$itemname</b>:<br>";
	$desc = descvarConvert($userrow, $itemrow['description'], $itemrow['effects']);
	echo $desc . "<br><b>It costs:</b>";
	$reachgrist = False;
	$terminateloop = False; //time-saver
	if ($gristed == false) {
		$gristname = initGrists();
		$totalgrists = count($gristname);
		$gristed = true;
	}
	$gristcount = 0;
	while ($gristcount <= $totalgrists) {
		$gristtype = $gristname[$gristcount];
	  $gristcost = $gristtype . "_Cost";
	  if ($itemrow[$gristcost] != 0) { //Item requires some of this grist. Or produces some. Either way.
	    $nothing = False; //Item costs something.
	      echo '<img src="Images/Grist/' . gristNameToImagePath($gristtype) . '" height="50" width="50" title="' . $gristtype . '"></img>';
	      if ($userrow[$gristtype] >= $itemrow[$gristcost]) {
	        echo " <gristvalue2>$itemrow[$gristcost] </gristvalue2>";
	      } else {
	      	echo " <gristvalue>$itemrow[$gristcost] </gristvalue>";
	      }
	  }
	  $gristcount++;
	}
	if (strpos($itemrow['effects'], "FLAVORCOST") !== false) {
		$i = 0;
		$effectarray = explode("|", $itemrow['effects']);
		while (!empty($effectarray[$i])) {
			$flavarray = explode(":", $effectarray[$i]);
			if ($flavarray[0] == "FLAVORCOST") {
				echo '<img src="Images/Grist/' . gristNameToImagePath($flavarray[1]) . '" height="50" width="50" title="' . $flavarray[1] . '"></img>';
				echo " <gristvalue2>" . $flavarray[2] . " </gristvalue2>";
				$nothing = false;
			}
			$i++;
		}
	}
	if ($nothing) { //Item costs nothing! SORD.....
	  echo '<img src="Images/Grist/Build_Grist.png" height="50" width="50" title="Build_Grist"></img>';
	  echo " <gristvalue2>0 </gristvalue2>";
	}
		echo "</br>";
		echo "Abstratus: $itemrow[abstratus]</br>";
	  echo "Strength: $itemrow[power]</br>";
	  echo "Size: $itemrow[size]</br>";
	  if ($itemrow['aggrieve'] != 0) echo "Aggrieve: $itemrow[aggrieve]</br>";
	  if ($itemrow['aggress'] != 0) echo "Aggress: $itemrow[aggress]</br>";
	  if ($itemrow['assail'] != 0) echo "Assail: $itemrow[assail]</br>";
	  if ($itemrow['assault'] != 0) echo "Assault: $itemrow[assault]</br>";
	  if ($itemrow['abuse'] != 0) echo "Abuse: $itemrow[abuse]</br>";
	  if ($itemrow['accuse'] != 0) echo "Accuse: $itemrow[accuse]</br>";
	  if ($itemrow['abjure'] != 0) echo "Abjure: $itemrow[abjure]</br>";
	  if ($itemrow['abstain'] != 0) echo "Abstain: $itemrow[abstain]</br>";
      }
    }
    if ($itemfound == False) echo 'The code you have inputted refers to an item that does not exist.</br>';
    if ($itemfound == True) echo "</br>";
//FORMS-----------------------------------------------------------------
  echo '<form action="tooly.php" method="post">First item :<input id="code1" name="code1" type="text" /><br>';
  echo 'Second item:<input id="code2" name="code2" type="text" /><br>';
  echo 'Combination to use: <select name="combine"><option value="or">||</option><option value="and">&&</option></select></br>';
  echo '<input type="submit" value="Design it!" /></form></br>';
  echo '<form action="tooly.php" method="post">Item to preview: <input id="holocode" name="holocode" type="text" /><br />';
  echo '<input type="submit" value="Observe it!" /></form></br>';
  echo '</br>';
require_once("footer.php");
?>