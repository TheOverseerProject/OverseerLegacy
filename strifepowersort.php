<?php
require_once("header.php");

function heaviestBonus($workrow){
	$bonusrow['abstain']=$workrow['abstain'];
	$bonusrow['abjure']=$workrow['abjure'];
	$bonusrow['accuse']=$workrow['accuse'];
	$bonusrow['abuse']=$workrow['abuse'];
	$bonusrow['aggrieve']=$workrow['aggrieve'];
	$bonusrow['aggress']=$workrow['aggress'];
	$bonusrow['assail']=$workrow['assail'];
	$bonusrow['assault']=$workrow['assault'];
	$bestbonus=max($bonusrow);
	if($bestbonus==0)return "none";
	elseif($bonusrow['abstain']==$bestbonus)return "abstain";
	elseif($bonusrow['abjure']==$bestbonus)return "abjure";
	elseif($bonusrow['accuse']==$bestbonus)return "accuse";
	elseif($bonusrow['abuse']==$bestbonus)return "abuse";
	elseif($bonusrow['aggrieve']==$bestbonus)return "aggrieve";
	elseif($bonusrow['aggress']==$bestbonus)return "aggress";
	elseif($bonusrow['assail']==$bestbonus)return "assail";
	elseif($bonusrow['assault']==$bestbonus)return "assault";
}

echo 'The following is a list of every weapon of a given abstratus, sorted by power level.</br>When a strife command is listed, that is the command with the highest bonus on the item.<br />';
  if (!empty($_GET['abs'])) { //I HAVE NEW WEAPON!
    echo "<br />";
    $alltotalpower = 0;
      $mainabstratus = $_GET['abs'];
      $absresult = mysql_query("SELECT * FROM `Captchalogue` WHERE `abstratus` LIKE '" . $mainabstratus . "%' OR `abstratus` LIKE '%, " . $mainabstratus . "%' ORDER BY `power` ASC");
      //ensures that we don't catch dartkind with artkind, inflatablekind with tablekind, etc
      $total = 0;
      while ($itemrow = mysql_fetch_array($absresult)) {
        $total++;
        if ($itemrow['size'] == "large") $hands = "2h";
        else $hands = "1h";
        $highbonus = heaviestBonus($itemrow);
        if ($highbonus != "none") {
          if ($itemrow[$highbonus] > 0) {
            $bnstr = "+" . strval($itemrow[$highbonus]);
          } else {
            $bnstr = strval($itemrow[$highbonus]);
          }
          $bnstr = " (" . $bnstr . " " . $highbonus . ")";
        } else $bnstr = "";
        echo $itemrow['name'] . " (" . $hands . ") - " . strval($itemrow['power']) . $bnstr . "<br />";
        $alltotalpower += $itemrow['power'];
      }
      $ordered[$mainabstratus] = $total;
      $abs[$k] = $mainabstratus;
      $k++;
      echo "<br />Total weapons in " . $mainabstratus . ": $total<br />";
      echo "Total power: " . strval($alltotalpower) . "<br />";
    }
    echo '<br /><form action="strifepowersort.php" method="get">Abstratus to look up: <input type="text" name="abs" /><input type="submit" value="Go" /></form>';

  require_once("footer.php");
?>