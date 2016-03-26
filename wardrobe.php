<?php
require 'additem.php';
require_once("header.php");
require_once("includes/effectprinter.php");
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
function refreshLuck($userrow){
	$slot=0;
	while($slot<6){
		switch($slot){
			case 0:
				$invslot=$userrow['equipped'];
			break;
			case 1:
				$invslot=$userrow['offhand'];
			break;
			case 2:
				$invslot=$userrow['headgear'];
			break;
			case 3:
				$invslot=$userrow['facegear'];
			break;
			case 4:
				$invslot=$userrow['bodygear'];
			break;
			case 5:
				$invslot=$userrow['accessory'];
			break;
		}
		if($slot!="" && $slot!="2HAND"){
			$realname=str_replace("'","\\\\''",$userrow[$invslot]);
			$result=mysql_query("SELECT `name`,`effects` FROM `Captchalogue` WHERE `Captchalogue`.`name` = '$realname' LIMIT 1;");
			$row=mysql_fetch_array($result);
			$realname=str_replace("\\","",$row['name']);
			if($realname==$userrow[$invslot]){
				$effects=$row['effects'];
				$effectarray=explode('|',$effects);
				$effectnumber=0;
				$totalluck=0;
				while(!empty($effectarray[$effectnumber])){
					$currenteffect=$effectarray[$effectnumber];
					$currentarray=explode(':',$currenteffect);
					//Note that what each array entry means depends on the effect.
					switch($currentarray[0]){
						case LUCK:
							$totalluck+=$currentarray[1];
						break;
						default:
						break;
					}
					$effectnumber++;
				}
			}
		}
		$slot++;
	}
	if($totalluck!=$userrow['Luck']){
		mysql_query("UPDATE `Players` SET `Luck` = $totalluck WHERE `Players`.`username` = '".$userrow['username']."'");
	}
}
if(empty($_SESSION['username'])){
	echo "Log in to view and manipulate your strife portfolio and options.</br>";
}
elseif($userrow['dreamingstatus']!="Awake"){
	require_once("includes/SQLconnect.php");
	echo "As your dream self, your only wearables are your dream pajamas and whatever basic eyewear you happen to use.</br>";
}
else{
	require_once("includes/SQLconnect.php");
	//--Begin equipping code here.--
	if(!empty($_POST['equiphead'])){
		//User is equipping an item to their head.
		if (strpos($_POST['equiphead'], "inv") === false && $_POST['equiphead'] != "none") { //player is trying to consume from outside their inventory!
    	echo "Look at you, trying to be clever! Unfortunately, you can only equip items from your inventory.<br />";
    	$_POST['equiphead'] = "none";
    }
		if($_POST['equiphead']=="none"){
			if($userrow['facegear']="2HAND")mysql_query("UPDATE `Players` SET `facegear` = '' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
			autoUnequip($userrow,"none",$userrow['headgear']); //will also remove any granted effects, if any exist
			mysql_query("UPDATE `Players` SET `headgear` = '' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
			echo "You remove your headgear.</br>";
			unset($_SESSION['headrow']);
			$headname="Nothing";
		}
		else{
			$headname=str_replace("'","\\\\''",$userrow[$_POST['equiphead']]);
			//Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
			$itemresult=mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '".$headname."'");
			while($itemrow=mysql_fetch_array($itemresult)){
				$itemname=$itemrow['name'];
				$itemname=str_replace("\\","",$itemname);
				//Remove escape characters.
				$headname=$itemname;
				if($itemname==$userrow[$_POST['equiphead']]){
					if(!strrpos($itemrow['abstratus'],"headgear")){
						echo "Stop trying to be Sollux.</br>";
					}
					else{
						if(itemSize($itemrow['size'])<itemSize("huge")){
							$equippedhead=$_POST['equiphead'];
							//For use later.
							echo "You wear your $itemname on your head.</br>";
							//NOTE - Unauthorized equipping prevented by menu options not being there.
							$_SESSION['headrow']=$itemrow;
							mysql_query("UPDATE `Players` SET `headgear` = '".$_POST['equiphead']."' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
							autoUnequip($userrow,"headgear",$equippedhead);
							$userrow['headgear'] = $_POST['equiphead'];
							compuRefresh($userrow);
							refreshLuck($userrow);
							grantEffects($userrow, $itemrow['effects'], "headgear");
							if($itemrow['size']=="large"){
								//Item takes up both the head and face slots
								mysql_query("UPDATE `Players` SET `facegear` = '2HAND' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
								//Current weapon is two-handed.
								$facename="Covered by headgear";
								$equippedface="2HAND";
							}
						}
						else echo "That item is too big to be worn on the head!</br>";
					}
				}
			}
		}
	}
	if(!empty($_POST['equipface'])){
		//User is equipping an item to their face.
		if (strpos($_POST['equipface'], "inv") === false && $_POST['equipface'] != "none") { //player is trying to consume from outside their inventory!
    	echo "Look at you, trying to be clever! Unfortunately, you can only equip items from your inventory.<br />";
    	$_POST['equipface'] = "none";
    }
		if($_POST['equipface']=="none"){
			autoUnequip($userrow,"none",$userrow['facegear']); //will also remove any granted effects, if any exist
			mysql_query("UPDATE `Players` SET `facegear` = '' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
			echo "You remove your facegear.</br>";
			$facename="Nothing";
			unset($_SESSION['facerow']);
		}
		else{
			$facename=str_replace("'","\\\\''",$userrow[$_POST['equipface']]);
			//Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
			$itemresult=mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '".$facename."'");
			while($itemrow=mysql_fetch_array($itemresult)){
				$itemname=$itemrow['name'];
				$itemname=str_replace("\\","",$itemname);
				//Remove escape characters.
				$facename=$itemname;
				if($itemname==$userrow[$_POST['equipface']]){
					if(!strrpos($itemrow['abstratus'],"facegear")){
						echo "Stop trying to be Sollux.</br>";
					}
					else{
						if(itemSize($itemrow['size'])<itemSize("large")){
							//No putting large. Might need to be removed later
							$equippedface=$_POST['equipface'];
							//For use later.
							echo "You wear your $itemname on your face.</br>";
							$_SESSION['facerow']=$itemrow;
							if($userrow['facegear']=="2HAND"){
								$userrow['headgear']="";
								//Remove large headgear
								mysql_query("UPDATE `Players` SET `headgear` = '' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
							}
							mysql_query("UPDATE `Players` SET `facegear` = '".$_POST['equipface']."' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
							autoUnequip($userrow,"facegear",$equippedface);
							$userrow['facegear'] = $_POST['equipface'];
							compuRefresh($userrow);
							refreshLuck($userrow);
							grantEffects($userrow, $itemrow['effects'], "facegear");
						}
						else{
							echo "That item is too big to be worn on the face!</br>";
						}
					}
				}
			}
		}
	}
	if(!empty($_POST['equipbody'])){
		//User is equipping an item to their head.
		if (strpos($_POST['equipbody'], "inv") === false && $_POST['equipbody'] != "none") { //player is trying to consume from outside their inventory!
    	echo "Look at you, trying to be clever! Unfortunately, you can only equip items from your inventory.<br />";
    	$_POST['equipbody'] = "none";
    }
		if($_POST['equipbody']=="none"){
			autoUnequip($userrow,"none",$userrow['bodygear']); //will also remove any granted effects, if any exist
			mysql_query("UPDATE `Players` SET `bodygear` = '' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
			echo "You remove your bodygear and are now wearing your regular clothes.</br>";
			$bodyname="Basic clothes";
			unset($_SESSION['bodyrow']);
		}
		else{
			$bodyname=str_replace("'","\\\\''",$userrow[$_POST['equipbody']]);
			//Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
			$itemresult=mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '".$bodyname."'");
			while($itemrow=mysql_fetch_array($itemresult)){
				$itemname=$itemrow['name'];
				$itemname=str_replace("\\","",$itemname);
				//Remove escape characters.
				$bodyname=$itemname;
				if($itemname==$userrow[$_POST['equipbody']]){
					if(!strrpos($itemrow['abstratus'],"bodygear")){
						echo "Stop trying to be Sollux.</br>";
					}
					else{
						if(itemSize($itemrow['size'])<itemSize("huge")){
							$equippedbody=$_POST['equipbody'];
							//For use later.
							echo "You wear your $itemname on your body.</br>";
							//NOTE - Unauthorized equipping prevented by menu options not being there.
							$_SESSION['bodyrow']=$itemrow;
							mysql_query("UPDATE `Players` SET `bodygear` = '".$_POST['equipbody']."' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
							autoUnequip($userrow,"bodygear",$equippedbody);
							$userrow['bodygear'] = $_POST['equipbody'];
							compuRefresh($userrow);
							refreshLuck($userrow);
							grantEffects($userrow, $itemrow['effects'], "bodygear");
						}
						else echo "That item is too big to be worn on the body!</br>";
					}
				}
			}
		}
	}
	if(!empty($_POST['equipacc'])){
		//User is equipping an item to their head.
		if (strpos($_POST['equipacc'], "inv") === false && $_POST['equipacc'] != "none") { //player is trying to consume from outside their inventory!
    	echo "Look at you, trying to be clever! Unfortunately, you can only equip items from your inventory.<br />";
    	$_POST['equipacc'] = "none";
    }
		if($_POST['equipacc']=="none"){
			autoUnequip($userrow,"none",$userrow['accessory']); //will also remove any granted effects, if any exist
			mysql_query("UPDATE `Players` SET `accessory` = '' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
			echo "You remove your accessory.</br>";
			unset($_SESSION['accrow']);
			$accname="Nothing";
		}
		else{
			$accname=str_replace("'","\\\\''",$userrow[$_POST['equipacc']]);
			//Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
			$itemresult=mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '".$accname."'");
			while($itemrow=mysql_fetch_array($itemresult)){
				$itemname=$itemrow['name'];
				$itemname=str_replace("\\","",$itemname);
				//Remove escape characters.
				$accname=$itemname;
				if($itemname==$userrow[$_POST['equipacc']]){
					if(!strrpos($itemrow['abstratus'],"accessory")){
						echo "Stop trying to be Sollux.</br>";
					}
					else{
						if(itemSize($itemrow['size'])<itemSize("huge")){
							$equippedacc=$_POST['equipacc'];
							//For use later.
							echo "You wear your $itemname as an accessory.</br>";
							//NOTE - Unauthorized equipping prevented by menu options not being there.
							$_SESSION['accrow']=$itemrow;
							mysql_query("UPDATE `Players` SET `accessory` = '".$_POST['equipacc']."' WHERE `Players`.`username` = '$username' LIMIT 1 ;");
							autoUnequip($userrow,"accessory",$equippedacc);
							$userrow['accessory'] = $_POST['equipacc'];
							compuRefresh($userrow);
							refreshLuck($userrow);
							grantEffects($userrow, $itemrow['effects'], "accessory");
						}
						else echo "That item is too big to be worn as an accessory!</br>";
					}
				}
			}
		}
	}
	//--End equipping code here.--
	if(empty($equippedhead))$equippedhead="";
	if(empty($equippedface))$equippedface="";
	if(empty($equippedbody))$equippedbody="";
	if(empty($equippedacc))$equippedacc="";
	if(empty($headname)){
		if($userrow['headgear']!=""){
			$headname=$userrow[$userrow['headgear']];
		}
		else{
			$headname="Nothing";
		}
	}
	if(empty($facename)){
		if($userrow['facegear']!=""){
			if($userrow['facegear']=="2HAND"){
				$facename="Covered by headgear";
			}
			else{
				$facename=$userrow[$userrow['facegear']];
			}
		}
		else{
			$facename="Nothing";
		}
	}
	if(empty($bodyname)){
		if($userrow['bodygear']!=""){
			$bodyname=$userrow[$userrow['bodygear']];
		}
		else{
			$bodyname="Basic clothes";
		}
	}
	if(empty($accname)){
		if($userrow['accessory']!=""){
			$accname=$userrow[$userrow['accessory']];
		}
		else{
			$accname="Nothing";
		}
	}
	echo "Virtual Wardrobifier v0.0.1w. Please select a captchalogued wearable.</br>";
	echo '<form action="wardrobe.php" method="post"><select name="equiphead">';
	if($headname!="Nothing")echo '<option value="none">Remove</option>';
	$reachinv=false;
	$terminateloop=False;
	$invresult=mysql_query("SELECT * FROM Players LIMIT 1;");
	while(($col=mysql_fetch_field($invresult)) && $terminateloop==False){
		$invslot=$col->name;
		if($invslot=="inv1"){
			//Reached the start of the inventory.
			$reachinv=True;
		}
		if($invslot=="abstratus1"){
			//Reached the end of the inventory.
			$reachinv=False;
			$terminateloop=True;
		}
		if($reachinv==True && $userrow[$invslot]!=""){
			//This is a non-empty inventory slot.
			$itemname=str_replace("'","\\\\''",$userrow[$invslot]);
			//Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
			$captchalogue=mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '".$itemname."'");
			while($row=mysql_fetch_array($captchalogue)){
				$itemname=$row['name'];
				$itemname=str_replace("\\","",$itemname);
				//Remove escape characters.
				$firstabstratus="";
				$foundcomma=False;
				$j=0;
				if(strrchr($row['abstratus'],',')==False){
					$firstabstratus=$row['abstratus'];
				}
				else{
					while($foundcomma!=True){
						$char="";
						$char=substr($row['abstratus'],$j,1);
						if($char==","){
							//Found a comma. We know there is one because of the if statement above. Break off the string as the main abstratus.
							$firstabstratus=substr($row['abstratus'],0,$j);
							$foundcomma=True;
						}
						else{
							$j++;
						}
					}
				}
				if($itemname==$userrow[$invslot]){
					//Item found in captchalogue database (doesn't check for notaweapon status because there can be weapons that double as gear)
					$i=1;
					while($i<=$userrow['abstrati']){
						$itemabstrati=$row['abstratus'];
						while(strrchr($itemabstrati,',')!=False){
							//Comma means there's still another abstratus in there to check.
							$foundcomma=False;
							$j=0;
							while($foundcomma!=True){
								$char="";
								$char=substr($itemabstrati,$j,1);
								if($char==","){
									//Found a comma. We know there is one because of the if statement above. Remove the first abstratus for testing, leave the rest.
									$abstratuscheck=substr($itemabstrati,0,$j);
									$itemabstrati=substr($itemabstrati,($j+2));
									//Assume a space after the comma
									$foundcomma=True;
									if($abstratuscheck=="headgear"){
										//Item can be worn on head
										echo '<option value = "'.$invslot.'">'.$userrow[$invslot];
										if($row['size']=="large")echo " (Entire head)";
										//This will also take up the facegear slot
										echo '</option>';
										$i=$userrow['abstrati'];
										//Done.
									}
								}
								else{
									$j++;
								}
							}
						}
						if($itemabstrati==$userrow[$abstrastr] || $itemabstrati=="headgear"){
							//Item can be worn on head
							echo '<option value = "'.$invslot.'">'.$userrow[$invslot];
							if($row['size']=="large")echo " (Entire head)";
							//This will also take up the facegear slot
							echo '</option>';
							$i=$userrow['abstrati'];
							//Done.
						}
						$i++;
					}
				}
			}
		}
	}
	echo '</select> <input type="submit" value="Wear on head" /> </form>';
	echo '<form action="wardrobe.php" method="post"><select name="equipface">';
	if($facename!="Nothing" && $facename!="Covered by headgear")echo '<option value="none">Remove</option>';
	$reachinv=false;
	$terminateloop=False;
	$invresult=mysql_query("SELECT * FROM Players LIMIT 1;");
	while(($col=mysql_fetch_field($invresult)) && $terminateloop==False){
		$invslot=$col->name;
		if($invslot=="inv1"){
			//Reached the start of the inventory.
			$reachinv=True;
		}
		if($invslot=="abstratus1"){
			//Reached the end of the inventory.
			$reachinv=False;
			$terminateloop=True;
		}
		if($reachinv==True && $userrow[$invslot]!="" && $invslot!=$userrow['headgear'] && $invslot!=$equippedhead){
			//This is a non-empty inventory slot that isn't worn on the head
			$itemname=str_replace("'","\\\\''",$userrow[$invslot]);
			//Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
			$captchalogue=mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '".$itemname."'");
			while($row=mysql_fetch_array($captchalogue)){
				$itemname=$row['name'];
				$itemname=str_replace("\\","",$itemname);
				//Remove escape characters.
				$firstabstratus="";
				$foundcomma=False;
				$j=0;
				if(strrchr($row['abstratus'],',')==False){
					$firstabstratus=$row['abstratus'];
				}
				else{
					while($foundcomma!=True){
						$char="";
						$char=substr($row['abstratus'],$j,1);
						if($char==","){
							//Found a comma. We know there is one because of the if statement above. Break off the string as the main abstratus.
							$firstabstratus=substr($row['abstratus'],0,$j);
							$foundcomma=True;
						}
						else{
							$j++;
						}
					}
				}
				if($itemname==$userrow[$invslot]){
					//Item found in captchalogue database
					$i=1;
					while($i<=$userrow['abstrati']){
						$itemabstrati=$row['abstratus'];
						while(strrchr($itemabstrati,',')!=False){
							//Comma means there's still another abstratus in there to check.
							$foundcomma=False;
							$j=0;
							while($foundcomma!=True){
								$char="";
								$char=substr($itemabstrati,$j,1);
								if($char==","){
									//Found a comma. We know there is one because of the if statement above. Remove the first abstratus for testing, leave the rest.
									$abstratuscheck=substr($itemabstrati,0,$j);
									$itemabstrati=substr($itemabstrati,($j+2));
									//Assume a space after the comma
									$foundcomma=True;
									if($abstratuscheck=="facegear"){
										//User has existing matching abstratus
										echo '<option value = "'.$invslot.'">'.$userrow[$invslot].'</option>';
										$i=$userrow['abstrati'];
										//Done.
									}
								}
								else{
									$j++;
								}
							}
						}
						if($itemabstrati==$userrow[$abstrastr] || $itemabstrati=="facegear"){
							//User has existing matching abstratus
							echo '<option value = "'.$invslot.'">'.$userrow[$invslot].'</option>';
							$i=$userrow['abstrati'];
							//Done.
						}
						$i++;
					}
				}
			}
		}
	}
	echo '</select> <input type="submit" value="Wear on face" /> </form>';
	echo '<form action="wardrobe.php" method="post"><select name="equipbody">';
	if($bodyname!="Basic clothes")echo '<option value="none">Remove</option>';
	$reachinv=false;
	$terminateloop=False;
	$invresult=mysql_query("SELECT * FROM Players LIMIT 1;");
	while(($col=mysql_fetch_field($invresult)) && $terminateloop==False){
		$invslot=$col->name;
		if($invslot=="inv1"){
			//Reached the start of the inventory.
			$reachinv=True;
		}
		if($invslot=="abstratus1"){
			//Reached the end of the inventory.
			$reachinv=False;
			$terminateloop=True;
		}
		if($reachinv==True && $userrow[$invslot]!="" && $invslot!=$userrow['headgear']){
			//This is a non-empty inventory slot that isn't worn on the head
			$itemname=str_replace("'","\\\\''",$userrow[$invslot]);
			//Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
			$captchalogue=mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '".$itemname."'");
			while($row=mysql_fetch_array($captchalogue)){
				$itemname=$row['name'];
				$itemname=str_replace("\\","",$itemname);
				//Remove escape characters.
				$firstabstratus="";
				$foundcomma=False;
				$j=0;
				if(strrchr($row['abstratus'],',')==False){
					$firstabstratus=$row['abstratus'];
				}
				else{
					while($foundcomma!=True){
						$char="";
						$char=substr($row['abstratus'],$j,1);
						if($char==","){
							//Found a comma. We know there is one because of the if statement above. Break off the string as the main abstratus.
							$firstabstratus=substr($row['abstratus'],0,$j);
							$foundcomma=True;
						}
						else{
							$j++;
						}
					}
				}
				if($itemname==$userrow[$invslot]){
					//Item found in captchalogue database
					$i=1;
					while($i<=$userrow['abstrati']){
						$itemabstrati=$row['abstratus'];
						while(strrchr($itemabstrati,',')!=False){
							//Comma means there's still another abstratus in there to check.
							$foundcomma=False;
							$j=0;
							while($foundcomma!=True){
								$char="";
								$char=substr($itemabstrati,$j,1);
								if($char==","){
									//Found a comma. We know there is one because of the if statement above. Remove the first abstratus for testing, leave the rest.
									$abstratuscheck=substr($itemabstrati,0,$j);
									$itemabstrati=substr($itemabstrati,($j+2));
									//Assume a space after the comma
									$foundcomma=True;
									if($abstratuscheck=="bodygear"){
										//User has existing matching abstratus
										echo '<option value = "'.$invslot.'">'.$userrow[$invslot].'</option>';
										$i=$userrow['abstrati'];
										//Done.
									}
								}
								else{
									$j++;
								}
							}
						}
						if($itemabstrati==$userrow[$abstrastr] || $itemabstrati=="bodygear"){
							//User has existing matching abstratus
							echo '<option value = "'.$invslot.'">'.$userrow[$invslot].'</option>';
							$i=$userrow['abstrati'];
							//Done.
						}
						$i++;
					}
				}
			}
		}
	}
	echo '</select> <input type="submit" value="Wear on body" /> </form>';
	echo '<form action="wardrobe.php" method="post"><select name="equipacc">';
	if($accname!="Nothing")echo '<option value="none">Remove</option>';
	$reachinv=false;
	$terminateloop=False;
	$invresult=mysql_query("SELECT * FROM Players LIMIT 1;");
	while(($col=mysql_fetch_field($invresult)) && $terminateloop==False){
		$invslot=$col->name;
		if($invslot=="inv1"){
			//Reached the start of the inventory.
			$reachinv=True;
		}
		if($invslot=="abstratus1"){
			//Reached the end of the inventory.
			$reachinv=False;
			$terminateloop=True;
		}
		if($reachinv==True && $userrow[$invslot]!="" && $invslot!=$userrow['headgear']){
			//This is a non-empty inventory slot that isn't worn on the head
			$itemname=str_replace("'","\\\\''",$userrow[$invslot]);
			//Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
			$captchalogue=mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '".$itemname."'");
			while($row=mysql_fetch_array($captchalogue)){
				$itemname=$row['name'];
				$itemname=str_replace("\\","",$itemname);
				//Remove escape characters.
				$firstabstratus="";
				$foundcomma=False;
				$j=0;
				if(strrchr($row['abstratus'],',')==False){
					$firstabstratus=$row['abstratus'];
				}
				else{
					while($foundcomma!=True){
						$char="";
						$char=substr($row['abstratus'],$j,1);
						if($char==","){
							//Found a comma. We know there is one because of the if statement above. Break off the string as the main abstratus.
							$firstabstratus=substr($row['abstratus'],0,$j);
							$foundcomma=True;
						}
						else{
							$j++;
						}
					}
				}
				if($itemname==$userrow[$invslot]){
					//Item found in captchalogue database
					$i=1;
					while($i<=$userrow['abstrati']){
						$itemabstrati=$row['abstratus'];
						while(strrchr($itemabstrati,',')!=False){
							//Comma means there's still another abstratus in there to check.
							$foundcomma=False;
							$j=0;
							while($foundcomma!=True){
								$char="";
								$char=substr($itemabstrati,$j,1);
								if($char==","){
									//Found a comma. We know there is one because of the if statement above. Remove the first abstratus for testing, leave the rest.
									$abstratuscheck=substr($itemabstrati,0,$j);
									$itemabstrati=substr($itemabstrati,($j+2));
									//Assume a space after the comma
									$foundcomma=True;
									if($abstratuscheck=="accessory"){
										//User has existing matching abstratus
										echo '<option value = "'.$invslot.'">'.$userrow[$invslot].'</option>';
										$i=$userrow['abstrati'];
										//Done.
									}
								}
								else{
									$j++;
								}
							}
						}
						if($itemabstrati==$userrow[$abstrastr] || $itemabstrati=="accessory"){
							//User has existing matching abstratus
							echo '<option value = "'.$invslot.'">'.$userrow[$invslot].'</option>';
							$i=$userrow['abstrati'];
							//Done.
						}
						$i++;
					}
				}
			}
		}
	}
	echo '</select> <input type="submit" value="Wear as accessory" /> </form>';
	$headdef=0;
	$bodydef=0;
	$facedef=0;
	$accdef=0;
	$totaldef=0;
	
	function checkvalues($itemname) {
		if($itemname!="Nothing") { //If the item is a real item
		$itemname = str_replace("'", "\\\\''", $itemname); //tch tch.
			$itemresult=mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '".$itemname."'");
			while($itemrow=mysql_fetch_array($itemresult)){ //Pull itemrow data from MySql array
			// var_dump($itemrow); //DEV, checks for array conents
			//Stolen code from inventory.php START
				$PrintBit = "";
			  $actives = $itemrow['aggrieve'] + $itemrow['aggress'] + $itemrow['assail'] + $itemrow['assault'];
		  if ($actives != 0) $PrintBit = $PrintBit . " Actives: $actives";
		  $passives = $itemrow['abuse'] + $itemrow['accuse'] + $itemrow['abjure'] + $itemrow['abstain'];
		  if ($passives != 0) $PrintBit = $PrintBit . " Passives: $passives";
		  if ($itemrow['power'] != 0) $PrintBit = $PrintBit . " Power: $itemrow[power]";		
		  if ($PrintBit != "") $PrintBit = " (" . $PrintBit . " )";		
			  
			  return $PrintBit;
			  
			//End stolen code
			}	
		}
	}
	
	echo "Currently wearing:</br>";
	echo "Headgear: $headname" . checkvalues($headname) . "<br />";
	echo "Facegear: $facename" . checkvalues($facename) . "<br />";
	echo "Bodygear: $bodyname" . checkvalues($bodyname) . "<br />";
	echo "Accessory: $accname" . checkvalues($accname) . "<br />";
	if($equippedhead!=""){
		$itemname=str_replace("'","\\\\''",$userrow[$equippedhead]);
		//Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
		$itemresult=mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '".$itemname."'");
		while($row=mysql_fetch_array($itemresult)){
			$itemname=$row['name'];
			$itemname=str_replace("\\","",$itemname);
			//Remove escape characters.
			if($itemname==$userrow[$equippedhead]){
				$headdef=$row['power'];
				if($row['hybrid']==1)$headdef=ceil($headdef/30);
			}
		}
	}
	else{
		if($userrow['headgear']!=""){
			$itemname=str_replace("'","\\\\''",$userrow[$userrow['headgear']]);
			//Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
			$itemresult=mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '".$itemname."'");
			while($row=mysql_fetch_array($itemresult)){
				$itemname=$row['name'];
				$itemname=str_replace("\\","",$itemname);
				//Remove escape characters.
				if($itemname==$userrow[$userrow['headgear']]){
					$headdef=$row['power'];
					if($row['hybrid']==1)$headdef=ceil($headdef/30);
				}
			}
		}
		else{
			$headdef=0;
		}
	}
	if($equippedface!=""){
		$itemname=str_replace("'","\\\\''",$userrow[$equippedface]);
		//Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
		$itemresult=mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '".$itemname."'");
		while($row=mysql_fetch_array($itemresult)){
			$itemname=$row['name'];
			$itemname=str_replace("\\","",$itemname);
			//Remove escape characters.
			if($itemname==$userrow[$equippedface]){
				$facedef=($row['power']);
				if($row['hybrid']==1)$facedef=ceil($facedef/30);
			}
		}
	}
	else{
		if($userrow['facegear']!="" && $userrow['facegear']!=$equippedhead && $equippedface!="2HAND"){
			$itemname=str_replace("'","\\\\''",$userrow[$userrow['facegear']]);
			//Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
			$itemresult=mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '".$itemname."'");
			while($row=mysql_fetch_array($itemresult)){
				$itemname=$row['name'];
				$itemname=str_replace("\\","",$itemname);
				//Remove escape characters.
				if($itemname==$userrow[$userrow['facegear']]){
					$facedef=($row['power']);
					if($row['hybrid']==1)$facedef=ceil($facedef/30);
				}
			}
		}
		else{
			$facedef=0;
		}
	}
	if($equippedbody!=""){
		$itemname=str_replace("'","\\\\''",$userrow[$equippedbody]);
		//Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
		$itemresult=mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '".$itemname."'");
		while($row=mysql_fetch_array($itemresult)){
			$itemname=$row['name'];
			$itemname=str_replace("\\","",$itemname);
			//Remove escape characters.
			if($itemname==$userrow[$equippedbody]){
				$bodydef=$row['power'];
				if($row['hybrid']==1)$bodydef=ceil($bodydef/10);
			}
		}
	}
	else{
		if($userrow['bodygear']!=""){
			$itemname=str_replace("'","\\\\''",$userrow[$userrow['bodygear']]);
			//Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
			$itemresult=mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '".$itemname."'");
			while($row=mysql_fetch_array($itemresult)){
				$itemname=$row['name'];
				$itemname=str_replace("\\","",$itemname);
				//Remove escape characters.
				if($itemname==$userrow[$userrow['bodygear']]){
					$bodydef=$row['power'];
					if($row['hybrid']==1)$bodydef=ceil($bodydef/10);
				}
			}
		}
		else{
			$bodydef=0;
		}
	}
	if($equippedacc!=""){
		$itemname=str_replace("'","\\\\''",$userrow[$equippedacc]);
		//Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
		$itemresult=mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '".$itemname."'");
		while($row=mysql_fetch_array($itemresult)){
			$itemname=$row['name'];
			$itemname=str_replace("\\","",$itemname);
			//Remove escape characters.
			if($itemname==$userrow[$equippedacc]){
				$accdef=$row['power'];
				if($row['hybrid']==1)$accdef=ceil($accdef/30);
			}
		}
	}
	else{
		if($userrow['accessory']!=""){
			$itemname=str_replace("'","\\\\''",$userrow[$userrow['accessory']]);
			//Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
			$itemresult=mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '".$itemname."'");
			while($row=mysql_fetch_array($itemresult)){
				$itemname=$row['name'];
				$itemname=str_replace("\\","",$itemname);
				//Remove escape characters.
				if($itemname==$userrow[$userrow['accessory']]){
					$accdef=$row['power'];
					if($row['hybrid']==1)$accdef=ceil($accdef/30);
				}
			}
		}
		else{
			$accdef=0;
		}
	}
}
$totaldef=$headdef+$facedef+$bodydef+$accdef;
echo "Current defense bonus from wearables: $totaldef </br>";
$invresult=mysql_query("SELECT * FROM Players LIMIT 1;");
echo $username;
echo "'s captchalogued wearables:</br></br>";
$reachinv=False;
$terminateloop=False;
while(($col=mysql_fetch_field($invresult)) && $terminateloop==False){
	$invslot=$col->name;
	if($invslot=="inv1"){
		//Reached the start of the inventory.
		$reachinv=True;
	}
	if($invslot=="abstratus1"){
		//Reached the end of the inventory.
		$reachinv=False;
		$terminateloop=True;
	}
	if($reachinv==True && $userrow[$invslot]!=""){
		//This is a non-empty inventory slot.
		$itemname=str_replace("'","\\\\''",$userrow[$invslot]);
		//Add escape characters so we can find item correctly in database. Also those backslashes are retarded.
		$captchalogue=mysql_query("SELECT * FROM Captchalogue WHERE `Captchalogue`.`name` = '".$itemname."'");
		while($row=mysql_fetch_array($captchalogue)){
			$itemname=$row['name'];
			$itemname=str_replace("\\","",$itemname);
			//Remove escape characters.
			$firstabstratus="";
			if($itemname==$userrow[$invslot]){
				//Item found in captchalogue database
				if(strrpos($row['abstratus'],"headgear") || strrpos($row['abstratus'],"facegear") || strrpos($row['abstratus'],"bodygear") || strrpos($row['abstratus'],"accessory")){
					//Item is wearable
					echo "Item: $itemname</br>";
					if($row['art']!=""){
						echo '<img src="Images/Items/'.$row['art'].'" title="Image by '.$row['credit'].'"></br>';
					}
					echo "Type: ".$row['abstratus']."</br>";
					$mypower=strval($row['power']);
					$mybpower="0";
					$theonebonus="none";
					if($row['hybrid']==1){
						$theonebonus=heaviestBonus($row);
						if(strrpos($row['abstratus'],"bodygear")){
							$boddef=ceil($row['power']/10);
							$bboddef=ceil($row[$theonebonus]/10);
						}
						else{
							$boddef=0;
							$bboddef=0;
						}
						if(strrpos($row['abstratus'],"headgear") || strrpos($row['abstratus'],"facegear") || strrpos($row['abstratus'],"accessory")){
							$hfadef=ceil($row['power']/30);
							$bhfadef=ceil($row[$theonebonus]/30);
						}
						else{
							$hfadef=0;
							$bhfadef=0;
						}
						if($hfadef!=0 && $boddef!=0)$mypower=strval($hfadef)." (".strval($boddef)." on body)";
						elseif($hfadef!=0)$mypower=strval($hfadef);
						elseif($boddef!=0)$mypower=strval($boddef);
						else $mypower="0";
						if($bhfadef!=0 && $bboddef!=0)$mybpower=strval($bhfadef)." (".strval($bboddef)." on body)";
						elseif($bhfadef!=0)$mybpower=strval($bhfadef);
						elseif($bboddef!=0)$mybpower=strval($bboddef);
						else $mybpower="0";
					}
					echo "Defense: $mypower</br>";
					if($row['aggrieve']>0 && ($theonebonus=="none" || $theonebonus=="aggrieve")){
						if($mybpower=="0")echo "Aggrieve bonus: $row[aggrieve] </br>";
						else echo "Aggrieve bonus: $mybpower </br>";
					}
					if($row['aggrieve']<0 && $theonebonus=="none"){
						echo "Aggrieve penalty: $row[aggrieve] </br>";
					}
					if($row['aggress']>0 && ($theonebonus=="none" || $theonebonus=="aggress")){
						if($mybpower=="0")echo "Aggress bonus: $row[aggress] </br>";
						else echo "Aggress bonus: $mybpower </br>";
					}
					if($row['aggress']<0 && $theonebonus=="none"){
						echo "Aggress penalty: $row[aggress] </br>";
					}
					if($row['assail']>0 && ($theonebonus=="none" || $theonebonus=="assail")){
						if($mybpower=="0")echo "Assail bonus: $row[assail] </br>";
						else echo "Assail bonus: $mybpower </br>";
					}
					if($row['assail']<0 && $theonebonus=="none"){
						echo "Assail penalty: $row[assail] </br>";
					}
					if($row['assault']>0 && ($theonebonus=="none" || $theonebonus=="assault")){
						if($mybpower=="0")echo "Assault bonus: $row[assault] </br>";
						else echo "Assault bonus: $mybpower </br>";
					}
					if($row['assault']<0 && $theonebonus=="none"){
						echo "Assault penalty: $row[assault] </br>";
					}
					if($row['abuse']>0 && ($theonebonus=="none" || $theonebonus=="abuse")){
						if($mybpower=="0")echo "Abuse bonus: $row[abuse] </br>";
						else echo "Abuse bonus: $mybpower </br>";
					}
					if($row['abuse']<0 && $theonebonus=="none"){
						echo "Abuse penalty: $row[abuse] </br>";
					}
					if($row['accuse']>0 && ($theonebonus=="none" || $theonebonus=="accuse")){
						if($mybpower=="0")echo "Accuse bonus: $row[accuse] </br>";
						else echo "Accuse bonus: $mybpower </br>";
					}
					if($row['accuse']<0 && $theonebonus=="none"){
						echo "Accuse penalty: $row[accuse] </br>";
					}
					if($row['abjure']>0 && ($theonebonus=="none" || $theonebonus=="abjure")){
						if($mybpower=="0")echo "Abjure bonus: $row[abjure] </br>";
						else echo "Abjure bonus: $mybpower </br>";
					}
					if($row['abjure']<0 && $theonebonus=="none"){
						echo "Abjure penalty: $row[abjure] </br>";
					}
					if($row['abstain']>0 && ($theonebonus=="none" || $theonebonus=="abstain")){
						if($mybpower=="0")echo "Abstain bonus: $row[abstain] </br>";
						else echo "Abstain bonus: $mybpower </br>";
					}
					if($row['abstain']<0 && $theonebonus=="none"){
						echo "Abstain penalty: $row[abstain] </br>";
					}
					if (!empty($row['effects'])) { //Item has effects. Print those here.
					$effectarray = explode('|', $row['effects']);
					$effectnumber = 0;
					while (!empty($effectarray[$effectnumber])) {
						$currenteffect = $effectarray[$effectnumber];
						$currentarray = explode(':', $currenteffect);
						$efound = printEffects($currentarray);
						if (!$efound) logDebugMessage($username . " - unrecognized item property $currentarray[0] from $row[name]");
						$effectnumber++;
						}
	  			}
					$desc = descvarConvert($userrow, $row['description'], $row['effects']);
					echo "Description: $desc</br></br>";
				}
			}
		}
	}
}
require_once("footer.php");
?>