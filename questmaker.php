<?php
require_once("header.php");

if (empty($username)) {
	echo "Log in to do things.";
} elseif ($userrow['session_name'] != "Developers" && $userrow['session_name'] != "Itemods") {
	echo "Keep your hands to yourself!";
} else {
	if (!empty($_POST['qprompt'])) {
		$dialogue = mysql_real_escape_string($_POST['qprompt']);
		$keywords = mysql_real_escape_string($_POST['qkeywords']);
		$abstratus = mysql_real_escape_string($_POST['qabstratus']);
		$grist = mysql_real_escape_string($_POST['qgrists']);
		if (!empty($_POST['qpower'])) $power = $_POST['qpower'];
		else $power = 0;
		$base = mysql_real_escape_string($_POST['qbase']);
		$consume = mysql_real_escape_string($_POST['qconsume']);
		$size = mysql_real_escape_string($_POST['qsize']);
		$reward = mysql_real_escape_string($_POST['qreward']);
		$gate = intval($_POST['qgate']);
		if ($gate < 1) $gate = 1;
		elseif ($gate > 7) $gate = 7;
		//echo "INSERT INTO `Consort_Dialogue` (`dialogue`, `context`, `req_keyword`, `req_abstratus`, `req_grist`, `req_base`, `req_consume`, `req_power`, `req_size`, `specialreward`) VALUES ('$dialogue', 'quest', '$keywords', '$abstratus', '$grist', '$base', '$consume', $power, '$size', '$reward')</br>";
		mysql_query("INSERT INTO `Consort_Dialogue` (`dialogue`, `context`, `gate`, `req_keyword`, `req_abstratus`, `req_grist`, `req_base`, `req_consume`, `req_power`, `req_size`, `specialreward`) VALUES ('$dialogue', 'quest', '$gate', '$keywords', '$abstratus', '$grist', '$base', '$consume', $power, '$size', '$reward')");
		echo "Quest inserted!</br>";
	}
	echo "Auto Quest Maker v1001. Fill out the fields below to add a quest to the database.</br></br>";
	echo '<form action="questmaker.php" method="post" id="conquest">Quest prompt (spoken by the consort; be sure to at least drop a hint about what kind of item the consort desires):</br><textarea name="qprompt" rows="6" cols="40" form="conquest"></textarea></br>';
	echo 'Minimum gate for this quest to appear: <input type="text" name="qgate"></br>NOTE: Be sure not to give a gate number below the gate at which a consort will accept the least expensive possible item that fits the requirements.</br>';
	echo 'Requirements: (can stack, use | to separate "or" conditionals (for example putting "Frosting|Rock_Candy" in Grist types will accept an item with a grist cost of Frosting OR Rock_Candy))</br>';
  echo 'Keywords: <input type="text" name="qkeywords"></br>';
  echo 'Abstratus/i: <input type="text" name="qabstratus"></br>';
  echo 'Grist types: <input type="text" name="qgrists"></br>';
  echo 'Minimum power level: <input type="text" name="qpower"></br>';
  echo '(For Base item and Consumable, put "yes" if the item should be a base/consumable, "no" if it shouldn\'t, leave blank if it doesn\'t matter)</br>';
  echo 'Base item: <input type="text" name="qbase"></br>';
  echo 'Consumable: <input type="text" name="qconsume"></br>';
  echo 'Size: <input type="text" name="qsize"></br>';
  echo 'Acceptable sizes, in order from smallest to largest: miniature, tiny, small, average (most items), large (two-handed weapons), huge, immense, ginormous</br>';
  echo 'Reward: <input type="text" name="qreward"></br>';
  echo '(rewards must be put in mysql query search format, example: `Captchalogue`.`power` > 100 will search for any item greater than 100 power. Leave blank for default; see Blah if you need help figuring out what to put for a particular query)</br>';
  echo '<input type="submit" value="Add that sucker"></form></br>';
}

require_once("footer.php");
?>