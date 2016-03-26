<?php
require_once("header.php");

function buildTree($tablename) {
	$exresult = mysql_query("SELECT * FROM `$tablename` WHERE 1;");
	while ($exrow = mysql_fetch_array($exresult)) {
		echo $exrow['name'] . "<br />";
		if (!empty($exrow['transform'])) {
			echo "Transform: " . $exrow['transform'] . " (always)<br />";
		}
		$i = 1;
		while ($i <= 5) {
			$randstr = "random" . strval($i);
			if (!empty($exrow[$randstr])) {
				echo "Transform: " . $exrow[$randstr] . " (" . strval($exrow[$randstr . 'chance']) . "% chance)<br />";
			}
			$i++;
		}
		$i = 1;
		while ($i <= 5) {
			$linkstr = "link" . strval($i);
			if (!empty($exrow[$linkstr . 'name'])) {
				echo "==&gt;" . $exrow[$linkstr . 'name'] . "<br />";
			}
			$i++;
		}
		if (!empty($exrow['strifelinkdesc'])) {
			echo "Strife: ";
			$i = 1;
			while ($i <= 5) {
				$enstr = "enemy" . strval($i);
				if (!empty($exrow[$enstr])) {
					if ($i > 1) echo ", ";
					echo $exrow[$enstr];
				}
				$i++;
			}
			echo "<br />";
			echo "Win: " . $exrow['strifelinksuccess'] . "<br />";
			echo "Lose: " . $exrow['strifelinkfailure'] . "<br />";
			echo "Run: " . $exrow['strifelinkabscond'] . "<br />";
		}
		echo "<br />";
	}
}

if ($userrow['session_name'] != "Developers" && $userrow['session_name'] != "Itemods") {
	echo "What are you doing here?";
} else {
	echo "<u>PROSPIT</u><br />";
	buildTree("Explore_Prospit");
	echo "<br /><u>DERSE</u><br />";
	buildTree("Explore_Derse");
}
require_once("footer.php");
?>