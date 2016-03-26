<?php require_once("header.php"); ?>

  <style>
  blahdev{color: #FF8906;}
  overseer{color: #660066;}
  sho{color: #00FF00;}
  vv{color: #FF0000;}
  gh{color: #AA0001;}
  </style>
  The colours are for changes by: <overseer>The Overseer</overseer> | <blahdev>Blahsadfeguie</blahdev> | <sho>Sho</sho> | <gh>GH</gh> | <vv>???</vv></br>
  </br>
  Current version: Alpha 2.3 hot fudge sundae</br>
  </br>
  Version Alpha 2.3 hot fudge sundae:</br>
  <blahdev>- Consort quests have been expanded to allow for quests that aren't just item fetch quests. New quest types:<br>
  &nbsp;&nbsp;- Strife quests: to complete the quest, you must defeat an enemy or group of enemies in strife<br>
  &nbsp;&nbsp;- Rescue quests: similar to strife quests, you are pitted against a group of enemies, but the goal is to reduce their power to 0<br>
  &nbsp;&nbsp;- Dungeon quests: you are taken to a dungeon and must beat the boss and/or reach a specific location within the dungeon<br>
  - Quests can also link to other quests and have unique victory messages<br>
  - Added the ability to have NPC followers contribute power to your strifes, similarly to player assisting<br>
  &nbsp;&nbsp;- You can hire and manage your followers from the new "Followers" page under Explore, including giving them custom names and descriptions<br>
  &nbsp;&nbsp;- Your ability to command NPC followers is determined by your Pulchritude stat, which increases with Echeladder rung<br>
  &nbsp;&nbsp;- You can unlock new follower types by building your land's economy or doing specific questlines<br>
  - Introduced a system that can allow for status effects to be tied to wearables, or last more than one encounter<br>
  - Added item effects: Payday, Piercing, Ammo, Hybrid Mod, Burning, Freezing, Soulsteal, Lockdown, Charmed, Randeffect, Varboost<br>
  - Added enemy status afflictions: Burning, Freezing, Lockdown, Charmed<br>
  - Implemented a use for affinity on wearables; it will now increase your passive resistance to certain aspect-specific attacks (such as those of denizens)<br>
  - There is now a new version of the item submission form where many more details can be specified. The advantage here is that once greenlit, items can be added as-is with no additional effort required 90% of the time!<br>
  - You should now be able to access all dungeons and your Denizen with a flying item<br>
  - A few changes were made to the Blade Cloud to better balance out the effort with the reward:<br>
  &nbsp;&nbsp;- It has a new special ability to summon Animated Blades (new enemy type); chance increases as it gets weaker<br>
  &nbsp;&nbsp;- Spawn rate is halved, maxing out at 50% for endgame weapons, and won't spawn if you are down or already strifing<br>
  &nbsp;&nbsp;- When you defeat it, you are KOed and must spend the next encounter recovering<br>
  - Various efficiency tweaks</blahdev><br>
  <br>
  Version Alpha 2.2 pineapple:</br>
  <blahdev>- Multi-floor dungeons! A dungeon can have up to its gate number in floors, and stairs will spawn in place of where the boss would spawn if it's not the final floor. Later floors will start at the difficulty the last floor left off, so be prepared for a gauntlet!</br>
  - Co-op dungeons! If a session-mate is in a dungeon, you can join them and explore/conquer it together. If a mate enters strife with some dungeon enemies, simply walk into the same square to assist them. This includes the boss!</br>
  - Dungeon generation tweaks! You'll find a bit more variety when it comes to dungeon layouts, which means more baddies and more loot!</br>
  - Three new dungeon enemies have been added during the course of this update: Golems, Constructs, and Wyverns. You've probably seen them already, though :L</br>
  - Three new bosses have been added as well; the Hydra (for 3-floor gate 3 dungeons), the True Hekatonchire (for 5-floor gate 5 dungeons), and one super secret random encounter boss!</br>
  - Dungeon UI update! Movement buttons have actual images and will stay anchored in one spot! The map itself has been tweaked a bit as well!</br>
  - Player symbols! You can select a symbol from a wide array of images (including the canon characters' symbols) to represent yourself on the dungeon map and other places. Custom image uploads are planned but not yet implemented.</br>
  - The Jumper Block Extension and Punch Card Shunts are actually functional now! To get an upgrade for your Alchemiter, use the SBURB Devices page to insert a punched card into an empty shunt, then put it in storage.</br>
  - The strife system should be properly equipped to handle more than 5 enemies at a time now. You can still only manually select 5 at a time to fight, but there are some moments when you may end up against more than that!</br>
  - Additionally, session admins have the ability to force-connect any two players as server and client, allowing them to build entire chains themselves if desired.</br>
  - Added potential weapon effects: Compound Hit</br>
  - Added player status effects: Poison, Blind, Confused</blahdev></br>
  <overseer>- Space II has received significant nerfs owing to a report that it can be used to three-shot Echidna. It should still be possible to take her out with it though, so I'm keeping an eye on that one.
  - Added player status effects: Bonus Action. These are mostly being added for implementation in upcoming roletechs and fraymotifs.</br></overseer>
  </br>
  Version Alpha 2.1 beta:</br>
  <overseer>- The Black King fight has been overhauled and should work and be more interesting now. More details can be found <a href='sessionbossbasics.php'>here</a>. Be aware that this is an open beta test of the battle and please report any oddities!</br>
  - Fixed a few bugs (ironically, one of them was with the text that displays when an enemy is Glitched Out</br></overseer>
  </br>
  Version Alpha 2.0!:</br>
  <overseer>- Added status effect system. During strife, the player and enemies can now have status effects applied to them (although most effects only apply to enemies right now).</br>
  - Added enemy status effects: Time Stop, Watery Gel, Poison, Shrink, Unlucky, Bleeding, Hopeless, Disoriented, Distracted, Enraged, Mellowed Out, Knocked Over, Glitched Out.</br>
  - Added player status effects: No damage cap.</br>
  - Added item effect system. Items can now possess effect tags. At the moment these are mostly being used for weapon effects.</br>
  - Added effects to weapons: Time Stop, Affinity, Watery Gel, Poisoning, Shrinking, Vampirism, Unlucky, Bleeding, Hopeless, Disorienting, Distracting, Enraging, Mellowing, Knockdown, Glitched. These have a chance to trigger on dealing damage (Affinity always triggers) and appear in the item description.</br>
  - Aspect Fighter has slightly reduced power boost values, but grants 5% affinity to compensate.</br>
  - Added Aspect resistance system. Enemies all possess varying resistances to the different Aspects (naturally, most of the grist enemies just have zero in everything). This affects damage taken from attacks with an affinity to that Aspect, chance of effects linked to that Aspect triggering (Time for Time Stop, etc), and may affect other things in the future.</br>
  - To compensate for Aspect resistance, aspect pattern damage and power reduction have been buffed a fair bit.</br>
  - Inventory page now displays a single entry with a "quantity" field instead of displaying the same item over and over.</br>
  - Added <a href="http://www.pesternote.com">Pesternote</a> connectivity. Pesternote is a Homestuck-themed social network, and this update allows you to link an account with them to the Project</br>
  - Added an option to automatically post boss kills to a linked Pesternote account</br>
  - Multiple efficiency fixes (inventory as described below, strife, and a few others)</br></overseer>
  <blahdev>- You can now store a number of items in your house depending on how high you've built it up. Different sized items take up different amounts of space. Some items even have effects when placed in storage.</br>
  - If you run out of inventory space while alchemizing, any additional items made will go straight into storage if there is room. You can also tell it to send all alchemized items to storage regardless of inventory space.</br>
  - Consort quests/dialogue in general can now have a lot more context-sensitive tags in the dialogue (literally any field in the player row can be transcribed)</br>
  - Quests can now be limited by gate (so that you don't get a quest for an item that the consorts can't afford).</br>
  - Added the SBURB Server page. Client connection and house-building has been moved there, and you can also deploy SBURB items into your client's storage.</br>
  - Added the SBURB Devices page. If you possess any device in storage that you are able to actively use, you can do so from there.</br>
  - Added Captchalogue Cards and Cruxite Dowels. Each is capable of holding a code, and can be used with the SBURB devices as in canon.</br>
  - Added GristTorrent CD to Deploy menu. It is now required in order to wire grist to other players.</br>
  - Improved computability, splitting it into 3 levels:</br>
  &nbsp;&nbsp;- Level 1 - You have a computer in storage, and are able to perform tasks while not exploring or in strife.</br>
  &nbsp;&nbsp;- Level 2 - You have a portable computer in your inventory ("average" size or smaller), and you are able to perform tasks while exploring, but not in strife.</br>
  &nbsp;&nbsp;- Level 3 - You have a wearable computer equipped and can perform tasks while exploring and/or in strife.</br>
  - Added effects to items: Storage, Code Holder, Deployable, Randamage.</br>
  - Various improvements to the feedback and submission systems.</br>
  - Sent messages will now show up in your chosen color.</br>
  - You can now apply the "Mark as Read" and "Delete" actions to all messages at once. We'll look into adding a "select all" option so that you can unselect a couple afterward you want to keep, etc.</br></blahdev>
  <vv>- Improved the loading speed of the inventory page significantly. SIGNIFICANTLY! (Blah and Overseer)</br></vv>
  <gh>- Joined the dev team!</br>
  - Fonts are now Courier New!
  - Edit permissions!</br></gh>
  <sho>
  - CSS has been optimized.<br/>
  </sho>
  </br>
	Version Alpha 1.6:</br>
	<overseer>- The Abuse command now multiplies your offensive power by 1.1 directly instead of increasing it by 10% of that of the first enemy in strife.</overseer></br>
	<blahdev>- Consort shops! You can go to any land you can reach via your gates and buy things from the consorts there. The shops refresh every day, and the more gates the land's owner has reached, the better stuff the shop will have in stock! <sho>(Sho designed the visual asthetics)</sho></br>
	- Consort item quests! You can quest on any land, and the consorts will ask you to bring them an item. It's up to you to figure out what they want, and your reward will scale depending on the worth of the item you turn in.</br>
	- You can define what kind of consorts are on your land via the Overview (Player Info) page, giving them a color and species (limited to the four canon species for now).</br>
	- As quests are completed and items are purchased on a land, your session's reputation will increase and your land's economy will grow. Shops will give you gradual discounts, and you will get increased quest rewards!</br>
	- Randomizer now recommends an operation if the other is too unstable (bit count of 8 or less from the "perfect objects")</br>
	- Added a dropdown to gristwire, porkhollow, and messages for quickly selecting a sessionmate to send stuff to (the text box can still be used to send to someone outside the session)</br>
	- Added a "quick reply" when viewing a message, for the heck of it</br>
	- Seeking a dungeon will now pit you against a "Dungeon Guardian" which is an underling that should be relatively weak and easy to take down if you have the power level required to actually stand a chance in the dungeon.</br>
	&nbsp;&nbsp;- This battle will not cost you an additional encounter and will give you an echeladder rung and grist as usual.</br>
	&nbsp;&nbsp;- (in other words, really low leveled players can't abuse overpowered easy-to-grab loot drops anymore :L)</br>
	- Luck is now a contributing factor to encountering strife-card-wielding imps (with luck maxed out, you should have a 5% chance to find one instead of a 1% chance)</br>
	- Weapon/wearable hybrids will now have their wearable defense divided by 30 (or 10 if bodygear) so that they aren't overpowered when worn. Feel free to make wearables that double as weapons!</br>
	- You can now change your password, set your email, and use a password recovery form should you forget your password. You can access player settings from the Player menu. The password recovery form can be accessed from the old login page, or from the SESSIONS menu.</br>
	- The item/art updates page (news.php) got an overhaul. News posts are truncated to the first line break, and you can click on individual posts to view them. While viewing a news post, you can also leave a comment. Use this feature however you wish :L</br></blahdev>
	<vv>- Health and Aspect Vials now properly reflect your color and aspect (a joint effort by Dent, Jordan, and Blah)</vv></br>
	</br>
  Version Alpha 1.5:</br>
  <overseer>- Added new roletech: Broken Record</br>
  - Added new roletech: Fortune's Protection</br>
  - Critical hit update: Chances are now much lower, so no more 50% crits at max Luck. Session bosses can now be critted, although they gain a 1 in 2 chance to prevent the critical after it is rolled. Finally, if Broken Record triggers on a critical, ALL the resulting hits will be criticals. Given how ridiculously devastating a critical hit is in this system (it adds your base power before the enemy subtracts, so it basically just deals your power in damage, which practically makes it the equivalent of a use of the Time I fraymotif), this needed to be done.</br></overseer>
  <blahdev>- Bugfix: Players can no longer assist other players on the battlefield if they don't have battlefield access themselves</br>
  - Bugfix: "Highest Active" on the inventory page should now be 100% accurate instead of sometimes showing the abstain bonus</br>
  - Challenge mode submissions that are suspended (rare as that should be) will now show up as gray instead of blue</br>
  - The message sent when someone is wired grist/boons will now show up as from Gristwire or Porkhollow respectively, as well as provide a link to the respective page; this is to distinguish the messages from regular ones the player can send themselves</br>
  - When editing a submission, anything you put in the 'additional comments' will now overwrite the existing user comments</br>
  - The randomizer's suggested power level formula was nerfed significantly, and it also accounts for bonuses up to 9999</br>
  - There is now an orange submission flag that indicates a submission from the Randomizer, and it will add a message if the power level was suggested by the Randomizer as well</br>
  - Moved randomizer settings (show details, atheneum only, etc) to the URL so that refreshing is much faster</br>
  - Submissions are now timestamped when they are created and/or last updated (flagged, edited, commented on, etc) and it is possible to sort submissions by timestamp</br>
  - You should no longer be able to join a session randomly if its head admin hasn't logged in for a while and is thus considered "inactive" (currently set to 14 days)</br></blahdev>
  <sho>
  -Login screen has been updated with an indication that you have submitted your credentials by clearing the password input as well as saying "Logging you in..." in an orange font.</br>
  -Header (Original mock design by Echo/lethargicDesigner and programming help by Dent) has been updated with these features:</br>
  &nbsp;&nbsp;-Stylized differences.</br>
  &nbsp;&nbsp;-Rounded corners at the top and bottom.</br>
  &nbsp;&nbsp;-New info bar with player information and links.</br>
  &nbsp;&nbsp;-If you are not signed in, then the new info bar will instead display a login form</br>
  &nbsp;&nbsp;-When you are logged in, the Next Encounter Timer will count down the time until your next encounter in real time. When it reaches zero, it'll make your encounters go up if it's not 100.</br>
  &nbsp;&nbsp;-New menu design.</br>
  &nbsp;&nbsp;-Menu design has now been optimized for mobile phones.</br>
  &nbsp;&nbsp;-The messages selection in the menu will tell you how many unread messages you have. IE: MESSAGES(6)</br>
  &nbsp;&nbsp;-The admin menu selection will not show up if you're not an admin.</br>
  </sho>
  <vv>-Cynthia: The banner image is now vertically centered.</vv>
  </br>
  <vv><span style="font-family: 'Comic Sans MS';">-Froont paigee nus fontt hos biin chonged far teh bttter Dawg</span></vv>
  </br>
  <sho>-The Sleep? button has been added to the menu.</sho>
  </br>
  </br>
  
  Version Alpha 1.4:</br>
  <overseer>- Bugfix: One with Nothing now works correctly when assisting</br>
  - Added new roletech: Blood Bonds</br>
  - Added new roletech: Temporal Doppelganger (yes, the umlaut is missing. Technical reasons.)</br>
  - Added new roletech: Light's Favour</br>
  - Added new roletech: Hope Endures</br>
  - Added new roletech: Inevitability</br>
  - Roletech rebalancing: One with Nothing now has a drastically reduced effect while dreaming.</br>
  - Added a "start over" option to exploration sections.</br>
  - Aspect Vial now visible on the player info page like it really should have been all along. Whoops, sorry. Similarly, wasting time now provides a link back to the info page.</br></overseer>
  <vv>- Modified the "fondle chain" action to be less immature</br>
  - Rewrote the sequence for leaving your tower on Derse</br></vv>
  <sho>- Ads are now implemented.</br>
  - Table in Gristwire is now slick CSS.</br>
  - CSS optimized for smaller file sizes.</br>
  - Further mobile optimization.</br>
  - Header Old link has been moved closer to the main content.</sho></br>
  <blahdev>- Fixed various player-reported bugs, including command auto-select not working on Prospit</br>
  - Inventory page shows abstratus designations of items, thus revealing consumables, computers, and flying items</br>
  - Also shown on the inventory page (if non-zero): power level, highest active bonus, highest passive bonus</br>
  - "Suspended" flag added to submissions, used to set aside a submission until a future date</br>
  - Most, if not all item codes should now display in courier new so that certain characters are distunguishable</br>
  - Squashed a very elusive and problematic bug where a session's head admin could accidentally exile/unadmin themselves</br></blahdev>
  </br>
  Version Alpha 1.3:</br>
  <overseer>- This changelog is now linked in the menu! This might be the first time some of you have seen it as a result</br>
  - There's now a colour guide at the top of the changelog</br>
  - Seek Fortune's Path now only works if the Seer has a computer and only affects allies who also have computers</br>
  - Added new roletech: Temporal Warp (Don't worry, Time fans, this isn't all you get!)</br>
  - Added new roletech: One with Nothing</br>
  - Added new roletech: Spatial Warp</br>
  - Added new roletech: Strength of Spirit</br></overseer>
  <blahdev>- You can now preview a code entered into the URL of the inventory page, and link this preview to others</br>
  -- Each item listed in the Atheneum links directly to a preview of the item</br>
  - The previewer and non-remote designix are now back for Challenge Mode players</br>
  - The Randomizer now works for Challenge Mode players, but you are limited to combinations from your Atheneum</br>
  -- Normal session players can also use the Randomizer to pick from their Atheneum only</br>
  - The submissions page can now sort in either ascending or descending order regardless of sorting method</br>
  -- It also now defaults to sorting ID in descending order, so that new submissions are shown first</br>
  - Your combat actions are now auto-selected in the action drop-downs, eliminating the need for the "use the last combat actions" button</br>
  -- They are now saved properly on the round they are used instead of the next</br>
  -- The enemy dropdowns from the strifeselect page also default to the last enemies fought, for quick edits</br>
  - Challenge mode players can no longer reset their strife specibus once it is chosen</br></blahdev>
  <sho>- Logging in doesn't look horrible any more!</br></sho>
  </br>
  Version Alpha 1.2:</br>
  <overseer>- Added new roletech: Battle Fury</br>
  - Fixed bug: Strife messages not displaying properly unless the page was reloaded</br></overseer>
  <blahdev>- Added options to the randomizer</br>
  -- You can specify any abstratus instead of just the AotW</br>
  -- There is now an in-built submission form if you like the result, including a power recommendation if you aren't sure what to put</br>
  - Added a new Challenge mode! Your items are very limited and you can't make anything that hasn't been discovered in your session.</br>
  -- When making a session, you can choose to make it a Challenge Mode session</br>
  -- In Challenge Mode, any item submissions you post get priority over regular submissions because you will definitely need it!</br>
  - Added all kinds of new search filters in the Atheneum</br>
  -- You can show all weapons, non-weapons, wearables, base/non-base, consumables, or even specify a single abstratus</br>
  - Finally figured out that mass recycler bug</br>
  - Item mods can now set flags at the same time as posting a comment (and in fact can't set a flag without posting something)</br>
  - When posting a submission comment, any player has the option to unset a yellow flag so that item mods know there's new information to look at</br>
  - You can now edit your own submissions through the feedback page</br>
  - The strifeselect page now has a dropdown that fills all five enemy slots with the selected grist type/enemy type</br></blahdev>
  </br>
  Version Alpha 1.1:</br>
  <blahdev>- Added a mass recycler (inventory)</br>
  - Added atheneum, which keeps track of all items alchemized by the session and their codes (atheneum, populateatheneum, additem, inventory)</br>
  - Improved item submission navigation, can now sort by a variety of factors and/or show only items that fit certain criteria (submissions)</br>
  - Sessions now have 2 additional options upon creation (sessionform, createsession, admin, playerform, addplayer, overview):</br>
  -- You can opt to allow random players to join (and there is an option when creating an account to join a session that has been marked as such)</br>
  -- An option that requires new classpect assignments to be unique (unless there are too many players)</br>
  -- The session's head admin can change either of these at any time from the administration page</br>
  -- Admins are now automatically assigned, rather than prompting the user on session creation</br>
  - Fixed and improved chain-checking code (strife, strifebegin, strifeselect, strifeaid, overview)</br>
  -- Flying items can now be used to circumvent gate restrictions</br></blahdev>
  </br>
  Version Alpha 1.0:</br>
  The Overseer Project enters the alpha stage!

<?php require_once("footer.php"); ?>