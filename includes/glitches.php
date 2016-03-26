<?php
function horribleMess() {
	$chararray = array(1 => "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", 
	"A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", 
	"!", "@", "#", "$", "%", "^", "&", "*", "+", "=", "?", "/", "\\", " ");
	$whatisit = rand(1,4);
	$rand = rand(1,count($chararray));
	$length = rand(5,15);
	$str = "";
	while ($length > 0) {
		if ($whatisit == 1) {
			$str = $str . $chararray[$rand];
		} else {
			$str = $str . $chararray[rand(1,count($chararray))];
		}
		$length--; //INFINITE LOOPS
	}
	return $str;
}
function generateGlitchString() { //DATA: Holds info on the glitch strings.
	$strarray = array(1 => "You and your opponents trade a series offffffGLITCH",	
	"You hit the GLITCH several times, and it falls over and begins twitching", 
	"GLITCH GLITCH GLITCH", 
	"The Denim GoblinGoblinGoblinGoblinGoblinGoblinGGGGGGGGGGGGGGGGGGLITCH", 
	"The SUPER APOSTROPHE 64 GLITCH\'\'\'\'\'\'\'\'\'", 
	"BEN is getting lonely...", 
	"The GLITCH suddenly gets hyper-realistic eyes and starts bleeding hyper-realistic blood", 
	"The GLITCH Please enter the name of the client player you wish to GLITCH", 
	"The ###########################################################################################################################:(){:|:&};:&%^#", 
	"The GLITCH spiGLITCH and glitch GLITCH throuh a waGLITCH", 
	"The Dirkbot GLITCH I'm sorry Dave, I can't let you do that", 
	"NESS NESS NESS NESS NESS NESS NESS NESS NESS NESS NESS NESS NESS NESS NESS NESS NESS NESS NESS NESS NESS NESS NESS NESS NESS", 
	"The GLITCH tries to GLITCH CAN'T LET YOU DO THAT, STARFGLITCH", 
	"You cannot grasp the true form of GLITCH's attack!", 
	"It doesn't affect GLITCH...", 
	"The GLITCH BROKEN BROKEN I'M SO BROOOOOOOKEN *clank* *clank*", 
	"The GLITCH WANTS TO PLAY A GAME", 
	"BOW WOW WOW WOW WOW WOW WO- woah GLITCH", 
	"The TYPHEUS glitches through the PLUSH RUMPS, and GLITCH%(%#%:(){:|:&};:", 
	"The IRONIC COOL GUGLITCH GLITCH GLITCH PUPPET ASS GLITCHGLITCH", 
	"GLITCH ... is hurt by the burn!", 
	"Wild MEW appeared!", 
	"GLITCH TM TRAINER DITTO", 
	"It puts the lubricant in its GLITCH", 
	"The GLITCHGLITCH MY LITTLE PONY MY LITTLE PONY", 
	"Wild LONLINESS diediediediediediediediedied!",
	"The GLITCH sinks halfway into the floor and beings flailing around at random.",
	"GLITCH cancels Strife: Interrupted by GLITCH.",
	"not a statement",
	"DEBUG: GLITCHGLITCH",
	"Are you sure you wish to GLITCH your account? This cannot be unGLITCH",
	"Thank you GLITCH! But our GLITCH is in another castle!",
	"It's a sad thing that your GLITCHventures have GLITCHed here!!",
	"Congratulations! You have defeated the Black KGLITCHe fight before you are killed oGLITCHies of \"blows\".",
	"Buffalo buffalo buffalo buffalo buffalo buffalo juggalo GLITCHalo.",
	"The GLITCH has died of dysentry.",
	"GLITCHBlahdev (Developer): <font color=#CCCC00>I'd suggest a power level of GLITCH</font>",
	"GLITCH.exe has encountered a problem and must close. We're sorry for any GLITCH",
	"But it failed!",
	"The GLITCH briefly transforms into a GLITCH.",
	"Suddenly, GLITCH floats off of the alchemiter and summons a bunch of other GLITCH from seemingly nowhere, which spiral into a whirlwind of GLITCH"
	);
	$rand = rand(1,count($strarray));
	$str = $strarray[$rand];
	while(strpos($str, 'GLITCH') !== false) {
		$str = preg_replace('/GLITCH/', horribleMess(), $str, 1);
	}
	return $str;
}
function generateStatusGlitchString() {
	$strarray = array(1 => "This enemy GLITCHGLITCH",	
	"This GLITCH doesn't know what it means to love.",
	"420 GLITCH IT",
	"This is probably a bug, please GLITCH",
	"This enemy occasionally GLITCH and can't GLITCH",
	"This enemy reallyreallyreallyreallyreallyreallyreallyGLITCH",
	"This enemy GLITCH beyond all mortal GLITCH",
	"GLITCH before GLITCH except after GLITCH",
	"My God! What have you done to the poor GLITCH??",
	"It's hard being a GLITCH and growing up. It's hard and nobody GLITCH",
	"This enemy has the hiccupsGLITCH and is reallyGLITCH upset abGLITCHout it.",
	"If you're GLITCH and you know it clap your GLITCH",
	"Effect GLITCH unrecognized. The devs have been GLITCH",
	"This enemy has a GLITCH it can't scratch.",
	"I can't GLITCH understand GLITCH your accent. GLITCH",
	"Stop trying to be GLITCH.",
	"GLITCHdidn't ask for this.",
	"GLITCHGLITCHGLITCH",
	"O wrote togs tray dent, ale seems.",
	"Is tart tarter tier hose GLITCH fudge ion doing GLITCH; DF skid kedge Sid o skid GLITCH shrug defog r kid figs GLITCH neuron milk defog.",
	"WHAT DID YOU DO?!",
	"This GLITCH forgot how to turn off NOCLIP.",
	"Item submitted! (ID: GLITCH) <a href='feedback.php?view=GLITCH'>You can view your suggestion here.</a>"
	);
	$rand = rand(1,count($strarray));
	$str = $strarray[$rand];
	while(strpos($str, 'GLITCH') !== false) {
		$str = preg_replace('/GLITCH/', horribleMess(), $str, 1);
	}
	return $str;
}
function generateBuyGlitchString() { //DATA: Info on glitch strings that force you to buy something.
	$strarray = array(1 => "The MILLIE BAYS HERE WITH ANOTHER FANTASTIC GLITCH", 
	"The Stoned Clown offers you some GLITCH potions", 
	"GLITCHGLITCH Welcome to the GLITCH mart!", 
	"GLITCHGLITCH RUMPLED HAT OBJECT GLITCH ONLY GLITCH BOONDOLLARS"
	);
	$rand = rand(1,count($strarray));
	$str = $strarray[$rand];
	str_replace('GLITCH', horribleMess(), $str);
	return $str;
}
?>