<?php
//Begin hit code here.

if (!empty($_POST['functionweapon'])) {
  $hit = 0;
  $attack = floor(($_POST['pilotattack'] + $_POST['weaponaccuracy']) * $_POST['functionweapon'] * ($_POST['functionweaponmount'] + (1 - ($_POST['functionweaponmount']/2))));
  if ($_POST['attackused'] == "part") $attack = floor($attack * 0.85);
  if ($_POST['attackused'] == "component") $attack = floor($attack * 0.7);
  if ($_POST['defenseused'] == "dodge") { //Dodge defense
    $defend = floor(($_POST['pilotdodge'] + $_POST['mechdodge']) * $_POST['functiondodge']);
  } elseif ($_POST['defenseused'] == "parry") { //Parry defense
    $defend = floor(($_POST['pilotparry'] + $_POST['mechparry']) * ($_POST['functionparry'] + (1 - ($_POST['functionparry']/2))));
  } else { //Soak defense
    $defend = $_POST['pilotsoak'];
  }
  if ($attack < $defend && $_POST['defenseused'] != "soak") { //Attack dodged or parried.
    if ($attack > floor($defend * 0.9) && $_POST['attackused'] = "part") {
      echo "Attacker strikes a random part of the defender's mech.";
    } elseif ($attack > floor($defend * 0.9) && $_POST['attackused'] = "component") {
      echo "Attacker strikes a random component on the targeted component's part.";
    } elseif ($attack > floor($defend * 0.8) && $_POST['attackused'] = "component") {
      echo "Attacker strikes a random part of the defender's mech.";
    } else {
      if ($_POST['defenseused'] == "dodge") { //Attack completely dodgd.
	echo "Defender dodges attacker.";
      } else {
	echo "Attacker strikes parrying part chosen by defender.";
      }
    }
  } elseif ($_POST['defenseused'] == "soak") { //Print mitigation.
    echo "Attacker strikes chosen target, damage is reduced by $defend%";
  } else { //Attack hits.
    echo "Attacker hits their intended target.";
  }
}
//End hit code here. Begin damage code here.
if (!empty($_POST['weaponpower'])) {
}
//End damage code here.
echo "NOTE - Use the armor's defense rating against the type of attack used.</br>";
echo "Additionally, functionality ratings are a number between 0 and 100.";
echo '<form action="mechcalculator.php" method="post">';
echo 'Attack type: <select name="attackused"><option value="wholemech">Whole mech</option><option value="part">Single part</option><option value="component">Single component</option></select><br />';
echo 'Functionality of weapon: <input id="functionweapon" name="functionweapon" type="text" />';
echo 'Functionality of weapon\'s mounted part: <input id="functionweaponmount" name="functionweaponmount" type="text" />';
echo 'Accuracy of weapon: <input id="weaponaccuracy" name="weaponaccuracy" type="text" />';
echo 'Attacker\'s relevant skill: <input id="pilotattack" name="pilotattack" type="text" />';
echo 'Defense applied: <select name="defenseused"><option value="dodge">Dodge</option><option value="parry">Parry</option><option value="soak">Soak</option></select><br />';
echo 'Functionality of mobility parts (average, defender, if dodging): <input id="functiondodge" name="functiondodge" type="text" />';
echo 'Defender\'s dodge skill (if dodging): <input id="pilotdodge" name="pilotdodge" type="text" />';
echo 'Dodge rating of mech: <input id="mechdodge" name="mechdodge" type="text" />';
echo 'Functionality of interposed part\'s mounted part (defender, if parrying): <input id="functionparry" name="functionparry" type="text" />';
echo 'Defender\'s parry skill (if parrying): <input id="pilotdodge" name="pilotdodge" type="text" />';
echo 'Parry rating of interposed item: <input id="mechparry" name="mechparry" type="text" />';
echo 'Defender\'s mitigation skill (if soaking): <input id="pilotsoak" name="pilotsoak" type="text" />';
echo '<input type="submit" value="Calculate hit" /></form></br>';
echo '<form action="mechcalculator.php" method="post">';
echo 'Weapon power: <input id="weaponpower" name="weaponpower" type="text" /><br />';
echo 'Armor protection (struck part): <input id="armorprotection" name="armorprotection" type="text" /><br />';
echo 'Armor resilience (struck part): <input id="armorresilience" name="armorresilience" type="text" /><br />';
echo 'Armor toughness (struck part): <input id="armortoughness" name="armortoughness" type="text" /><br />';
echo '<input type="submit" value="Calculate damage" /></form></br>';
?>