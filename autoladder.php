<?php
require_once("header.php");

//require 'log.php';
if (empty($_SESSION['username'])) {
  echo "Log in to dingle your dangling dongle's danger.</br>";
} 
else {
require_once("includes/SQLconnect.php");
  
function randomFromArray(array $array) {
	$rand = array_rand($array,1);
	return $array[$rand];
}

$result = "ERROR"; // in case something breaks, displays error 
echo "Echeladder Filler.</br>";

$class = "Maid";
$aspect = "Light";

//$class =  $userrow['Class'];
//$aspect =  $userrow['Aspect'];


//generic
$noun_generic = array("wonder", "lad", "tyke");
$adj_generic = array("dashing", "handsome");
$verb_generic = array("dingler", "flanger");

//Add generic
$adjs = $adj_generic;
$nouns = $noun_generic;
$verbs = $verb_generic;

$class = randomFromArray(array("Seer", "Knight", "Rogue", "Thief", "Bard", "Prince", "Sylph", "Witch", "Maid", "Mage", "Page", "Heir"));
$aspect = randomFromArray(array("Breath", "Time", "Light", "Space", "Mind", "Heart", "Void", "Rage", "Life", "Doom", "Hope"));


$noun = array(
	"Seer" => array("seer", "eye", "scope", "genius"),
	"Knight" => array("knight", "blade", "sprat", "aide"),
	"Rogue" => array("rogue", "rapscallion", "scalawag", "ne'er-do-well", "blackguard"),
	"Thief" => array("thief", "crook", "sniper", "swindler", "cheat", "kleptomanicac", "spider"),
	"Bard" => array("bard", "minstrel", "poet", "rhapsodist", "artist", "parodist", "dilettante", "metrist", "joker", "jester"),
	"Prince" => array("prince", "archduke", "emir", "monarch", "nobleman", "potentate", "raja", "ruler"),
	"Sylph" => array("sylph", "dryad", "fairy", "mermaid", "naiad", "nymphent", "sprite", "spirit", "physician", "therapist", "doctor"),
	"Witch" => array("witch", "magician", "enchantress", "necromancer", "occultist", "sorcerer"),
	"Maid" => array("maid", "au pair", "chambermaid", "housemaid", "biddy", "damsel", "factoum", "handmaid", "handmaiden", "maidservant"),
	"Mage" => array("mage", "magician", "apprentice", "wizard", "warlock"),
	"Page" => array("page"),
	"Heir" => array("heir"),
	"Muse" => array("muse"),
	"Lord" => array("lord"),
	
	"Breath" => array("breath", "gasp", "eupnea", "insufflation", "inhale", "exhale", "breeze", "wind", "zephyr", "current", "gust"),
	"Time" => array("time", "point", "occasion", "chronology"),
	"Light" => array("light", "flash", "glare", "ray", "aurora", "glint", "glitter", "fortune", "luck"),
	"Space" => array("space", "area", "extent"),
	"Mind" => array("mind", "choice", "thought"),
	"Heart" => array("heart", "soul", "emotion"),
	"Void" => array("void", "emptiness", "limbo"),
	"Rage" => array("rage", "anger", "frustration", "fury"),
	"Life" => array("life", "spirit", "essence"),
	"Hope" => array("hope"),
	"Doom" => array("doom", "death", "decay"),
	"Blood" => array("blood")
	);
$verb = array(
	"Seer" => array("seer", "gazer", "knower", "perceiver", "comprehender"),
	"Knight" => array("slasher", "gleemer", "slicer", "basher", "warrior", "protector", "defender"),
	"Rogue" => array("rogue", "bilk", "sandbag", "flimflam", "fudge", "gull", "pluck", "trim"),
	"Thief" => array("larcener", "scrounger", "robber", "purloiner", "defalcator"),
	"Bard" => array("balladeer", "versifier", "rhymester", "poetaster", "rimer",),
	"Prince" => array("control", "lead", "orderer", "overruler", "overtaker", "administer", "guider", "reigner"),
	"Sylph" => array("curer", "mender", "healer", "dresser", "minister"),
	"Witch" => array("director", "aimer", "bestower", "diffuser", "conjurer", "enchanter"),
	"Maid" => array("deliveror", "distributer", "handler", "arranger", "assister", "dealer", "formulater", "girder", "qualifier", "strengthener", "planner"),
	"Mage" => array("mage"),
	"Page" => array("page"),
	"Heir" => array("heir"),
	"Muse" => array("muse"),
	"Lord" => array("lord"),
	
	"Breath" => array("winder"),
	"Time" => array("timer"),
	"Light" => array("shiner"),
	"Space" => array("spacer"),
	"Mind" => array("thinker"),
	"Heart" => array("lover", "empath"),
	"Void" => array("emptier"),
	"Rage" => array("rager"),
	"Life" => array("liver", "healer", "doctor"),
	"Hope" => array("hoper"),
	"Doom" => array("doomer"),
	"Blood" => array("bleeder")
	);
$adj = array(
	"Seer" => array("understanding", "knowledgeable"),
	"Knight" => array("glimmering", "glimmerous", "demiblade", "shaded", "dumb stupid pointy anime"),
	"Rogue" => array("rogue", "devilish", "impish", "sly", "beguiling", "cunning", "naughty", "puckish"),
	"Thief" => array("artist", "stickup", "punk", "owl"),
	"Bard" => array("strolling", "jolly", "versifing", "balladry", "metrical", "rhyming"),
	"Prince" => array("sovereign", "royal", "noble", "cardinal", "commanding", "pivotal", "regnant", "upper", "imperial", "patrician", "wellborn", "highborn"),
	"Sylph" => array("beguiling", "captivating", "healing", "exciting", "sirenic"),
	"Witch" => array("radiating", "spraying", "spreading", "strewing", "beugiling", "enchanting"),
	"Maid" => array("assisting", "preparing", "convoluting", "planning", "bracing"),
	"Mage" => array("mage", "magical", "arcane", "magic", "mystical"),
	"Page" => array("page"),
	"Heir" => array("heir"),
	"Muse" => array("muse"),
	"Lord" => array("lord"),
	
	"Breath" => array("breathy", "transparent"),
	"Time" => array("timey"),
	"Light" => array("bright"),
	"Space" => array("spacey"),
	"Mind" => array("mindy"),
	"Heart" => array("hearty"),
	"Void" => array("voidey"),
	"Rage" => array("angry", "furious", "frustrated"),
	"Life" => array("lifey"),
	"Hope" => array("hopeing"),
	"Doom" => array("dooming"),
	"Blood" => array("bleeding", "bloody")
	);
	
$verbs = array_merge($verbs, $verb[$class]);
$nouns = array_merge($nouns, $noun[$class]);
$adjs = array_merge($adjs, $adj[$class]);

$verbs = array_merge($verbs, $verb[$aspect]);
$nouns = array_merge($nouns, $noun[$aspect]);
$adjs = array_merge($adjs, $adj[$aspect]);



function generate() //Returns one echeladder title as a string
{
	global $nouns;
	global $verbs;
	global $adjs;

	$method = rand(0,5);
	if ($method == 0) {
		$result = randomFromArray($nouns) . " " . randomFromArray($verbs);
	}
	if ($method == 1) {
		$result = randomFromArray($nouns) . "'s " . randomFromArray($nouns) . randomFromArray($verbs);
	}
	if ($method == 2) {
		$result = randomFromArray($nouns) . randomFromArray($verbs) . "'s " . randomFromArray($nouns);
	}
	if ($method == 3) {
		$result = randomFromArray($adjs) . " " . randomFromArray($nouns);
	}
	if ($method == 4) {
		$result = randomFromArray($verbs) . " of the " . randomFromArray($nouns) . "";
	}	
	if ($method == 5) {
		$result = randomFromArray($verbs) . " of " . randomFromArray($nouns) . "s";
	}
	
	//echo $result;
	
	//sliminator
	if ($result[strlen($result)-1] == s) {
		if ($result[strlen($result)-2] == s) {
		$result = rtrim($result["s"] );
		}
	}
	//capitalizer
	$result = ucfirst($result);
	return $result
	. " (Method " . $method . ")" //Add method tag?
	;
}

function sixtwelve(){
echo generate();
}
  
  //Begin actual page
  echo "Class: " . $class . "<br />";
  echo "Aspect: " . $aspect . "<br />";

$maxrands = count($adjs) * count($nouns) * count($verbs) * 5;
echo "<br />Randomability (612 is minimum. Ideal is 6,120+): " . $maxrands . "<br />";
  
$i = 1;
while ($i <= 611) {
	echo $i . ". " . generate() . "<br />";
	$i++;
}

sixtwelve();

}
require_once("footer.php");
?> 