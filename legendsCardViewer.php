<?php
/*
 * TODO:
 *	 	- Attribute icons?
 *
 */


require_once("/home/uesp/secrets/legends.secrets");
require_once("legendsCommon.php");


class CUespLegendsCardDataViewer
{
	
	static $LEGENDS_TYPES = array("Action", "Creature", "Item", "Support");
	static $LEGENDS_SUBTYPES = array("Argonian", "Ash Creature", "Beast", "Breton", "Centaur", "Chaurus", "Daedra", "Dark Elf", "Defense", 
					"Dragon", "Dreugh", "Dwemer", "Fabricant", "Factotum", "Falmer", "Fish", "Gargoyle", "Giant", "Goblin", "Harpy", "High Elf",
					"Imp", "Imperial", "Khajiit", "Kwama", "Lurcher", "Mammoth", "Mantikora","Minotaur", "Mudcrab", "Mummy", "Nereid", "Nord",
					"Ogre", "Orc", "Pastry", "Reachman", "Redguard", "Reptile", "Skeleton", "Spider", "Spirit", "Spriggan", "Troll", "Undead", 
					"Vampire", "Wamasu", "Werewolf", "Wolf", "Wood Elf", "Wraith" );
	static $LEGENDS_ATTRIBUTES = array("Agility", "Endurance", "Intelligence", "Neutral", "Strength", "Willpower");
	static $LEGENDS_RARITIES = array("Common", "Rare", "Epic", "Legendary");
	static $LEGENDS_SETS = array(
				"Clockwork City",
				"Core Set",
				"Dark Brotherhood",
				"Heroes of Skyrim",
				"Madhouse Collection",
				"Monthly Card",
			);
	static $LEGENDS_CLASSES = array(
			"Archer",
			"Assassin",
			"Battlemage",
			"Crusader",
			"Mage",
			"Monk",
			"Scout",
			"Sorcerer",
			"Spellsword",
			"Warrior",			
	);
	
	public $inputParams = array();
	public $inputCardName = "";
	public $inputEditCard = "";
	public $inputSaveCard = false;
	public $inputCreateCard = false;
	
	public $wikiContext = null;
	public $db = null;
	public $errorMsg = "";
	
	public $cards = array();
	public $singleCardData = null;
	public $inputCardData = array(
			'name' => '',
			'text' => '',
			'type' => '',
			'subtype' => '',
			'image' => '',
			'rarity' => '',
			'attribute' => '',
			'attribute2' => '',
			'class' => '',
			'set' => '',
			'magicka' => 0,
			'power' => 0,
			'health' => 0,
			'uses' => '',
			'obtainable' => 0,
			'training1' => '',
			'trainingLevel1' => 0,
			'training2' => '',
			'trainingLevel2' => 0,
		);


	public function __construct ()
	{
		$this->inputParams = $_REQUEST;
		$this->ParseInputParams();	
	}
	
	
	public function ParseInputParams()
	{
		if ($this->inputParams['name'] != "") $this->inputCardName = $this->inputParams['name'];
		if ($this->inputParams['card'] != "") $this->inputCardName = $this->inputParams['card'];
		if ($this->inputParams['edit'] != "") $this->inputEditCard = $this->inputParams['edit'];
		if ($this->inputParams['save'] != "") $this->inputSaveCard = intval($this->inputParams['save']) != 0;
		if ($this->inputParams['create'] != "") $this->inputCreateCard = intval($this->inputParams['create']) != 0;
		
		if ($this->inputCreateCard) $this->inputCardName = trim($this->inputCardName);
		
		$this->inputCardData['name'] = $this->inputCardName;
		
		if ($this->inputParams['type'] !== null) $this->inputCardData['type'] = $this->inputParams['type'];
		if ($this->inputParams['subtype'] !== null) $this->inputCardData['subtype'] = $this->inputParams['subtype'];
		if ($this->inputParams['race'] !== null) $this->inputCardData['subtype'] = $this->inputParams['subtype'];
		if ($this->inputParams['class'] !== null) $this->inputCardData['class'] = $this->inputParams['class'];
		if ($this->inputParams['set'] !== null) $this->inputCardData['set'] = $this->inputParams['set'];
		if ($this->inputParams['attribute1'] !== null) $this->inputCardData['attribute'] = $this->inputParams['attribute1'];
		if ($this->inputParams['attribute2'] !== null) $this->inputCardData['attribute2'] = $this->inputParams['attribute2'];
		if ($this->inputParams['rarity'] !== null) $this->inputCardData['rarity'] = $this->inputParams['rarity'];
		if ($this->inputParams['magicka'] !== null) $this->inputCardData['magicka'] = intval($this->inputParams['magicka']);
		if ($this->inputParams['power'] !== null) $this->inputCardData['power'] = intval($this->inputParams['power']);
		if ($this->inputParams['health'] !== null) $this->inputCardData['health'] = intval($this->inputParams['health']);
		if ($this->inputParams['obtainable'] !== null) $this->inputCardData['obtainable'] = intval($this->inputParams['obtainable']);
		if ($this->inputParams['training1'] !== null) $this->inputCardData['training1'] = $this->inputParams['training1'];
		if ($this->inputParams['training2'] !== null) $this->inputCardData['training2'] = $this->inputParams['training2'];
		if ($this->inputParams['trainingLevel1'] !== null) $this->inputCardData['trainingLevel1'] = intval($this->inputParams['trainingLevel1']);
		if ($this->inputParams['trainingLevel2'] !== null) $this->inputCardData['trainingLevel2'] = intval($this->inputParams['trainingLevel2']);
		if ($this->inputParams['uses'] !== null) $this->inputCardData['uses'] = $this->inputParams['uses'];
		if ($this->inputParams['text'] !== null) $this->inputCardData['text'] = $this->inputParams['text'];
		if ($this->inputParams['image'] !== null) $this->inputCardData['image'] = $this->inputParams['image'];		
	}
	

	public function ReportError($errorMsg)
	{
		error_log($errorMsg);
		return false;
	}

	
	public function InitDatabase()
	{
		global $uespLegendsReadDBHost, $uespLegendsReadUser, $uespLegendsReadPW, $uespLegendsDatabase;
		
		$this->db = new mysqli($uespLegendsReadDBHost, $uespLegendsReadUser, $uespLegendsReadPW, $uespLegendsDatabase);
		if ($this->db->connect_error) return $this->ReportError("ERROR: Could not connect to mysql database!");
	
		UpdateLegendsPageViews("cardDataViews");
	
		return true;
	}
	
	
	public function InitDatabaseWrite()
	{
		global $uespLegendsWriteDBHost, $uespLegendsWriteUser, $uespLegendsWritePW, $uespLegendsDatabase;
	
		$this->db = new mysqli($uespLegendsWriteDBHost, $uespLegendsWriteUser, $uespLegendsWritePW, $uespLegendsDatabase);
		if ($this->db->connect_error) return $this->ReportError("ERROR: Could not connect to mysql database!");
	
		UpdateLegendsPageViews("cardDataEdits");
	
		return true;
	}
	
	
	public function DoesCardExist($name)
	{
		$safeName = $this->db->real_escape_string($name);
		$query = "SELECT name FROM cards WHERE name='$safeName';";
		$result = $this->db->query($query);
		if ($result === false || $result->num_rows <= 0) return false;
		return true;
	}
	
	
	public function CanEditCard()
	{
		if ($this->wikiContext == null) return false;
		
		$user = $this->wikiContext->getUser();
		if ($user == null) return false;
		
		if (!$user->isLoggedIn()) return false;
		if (strcasecmp($user->getName(), $this->characterData['wikiUserName']) == 0) return true;
		
		return $user->isAllowedAny('legendscarddata_edit');
	}
	
	
	public function CanCreateCard()
	{
		if ($this->wikiContext == null) return false;
		
		$user = $this->wikiContext->getUser();
		if ($user == null) return false;
		
		if (!$user->isLoggedIn()) return false;
		if (strcasecmp($user->getName(), $this->characterData['wikiUserName']) == 0) return true;
		
		return $user->isAllowedAny('legendscarddata_add');
	}
	
	
	public function CreateEditListOutput($list, $currentValue, $id)
	{
		$name = strtolower($id);
		$output = "<select id='eslegCardInput$id' name='$name'>";
		
		$selected = "";
		if ($currentValue == "") $selected = "selected";
		$output .= "<option value='' $selected>";
		
		foreach ($list as $item)
		{
			$selected = "";
			if ($currentValue == $item) $selected = "selected";
			$output .= "<option value='$item' $selected>$item";
		}
		
		$output .= "</select>";
		return $output;
	}
	
	
	public function GetCardDataQuery()
	{
		if ($this->inputCardName != "")
		{
			$safeName = $this->db->real_escape_string($this->inputCardName);
			$query = "SELECT * FROM cards WHERE name='$safeName';";
		}
		else if ($this->inputEditCard != "")
		{
			$safeName = $this->db->real_escape_string($this->inputEditCard);
			$query = "SELECT * FROM cards WHERE name='$safeName';";
		}
		else
		{
			$query = "SELECT * FROM cards ORDER BY name;";
		}
		
		return $query;
	}
	
	
	public function LoadCardData()
	{
		if (!$this->InitDatabase()) return false;
		
		$query = $this->GetCardDataQuery();
		$result = $this->db->query($query);
		if ($result === false) return $this->ReportError("ERROR: Failed to load card data from table!");
		
		$this->cards = array();
		
		while (($card = $result->fetch_assoc()))
		{
			$name = $card['name'];
			$this->cards[$name] = $card;
			$this->singleCardData = $card;
		}
	
		return true;
	}
	
	
	public function Escape($html)
	{
		return htmlspecialchars($html);	
	}
	
	
	public function GetBreadcrumbTrail()
	{
		$output = "<div class='eslegBreadcrumb'>";
		
		if ($this->inputCardName != "" || $this->inputSaveCard || $this->inputCreateCard)
		{
			$output .= "<a href='/wiki/Special:LegendsCardData'>&laquo; View All Cards</a>";
		}
		else if ($this->inputEditCard != "")
		{
			$safeName = urlencode($this->inputEditCard);
			$output .= "<a href='/wiki/Special:LegendsCardData'>&laquo; View All Cards</a>";
			$output .= " : " . "<a href='/wiki/Special:LegendsCardData?card=$safeName'>View Card</a>";
		}
		
		$output .= "</div>";
		return $output;	
	}
	
	
	public function GetCardLink($card)
	{
		$name = $this->Escape($card);
		$encodeName = urlencode($card);
		$nameLink = "<a href=\"/wiki/Special:LegendsCardData?card=$encodeName\" class='legendsCardLink' card=\"$name\">$name</a>";
		return $nameLink;
	}
	
	
	public function getCardOutputRow($card)
	{
		$output = "<tr>";
		
		$name = $this->Escape($card['name']);
		$type = $this->Escape($card['type']);
		$subtype = $this->Escape($card['subtype']);
		$attribute1 = $this->Escape($card['attribute']);
		$attribute2 = $this->Escape($card['attribute2']);
		$class = $this->Escape($card['class']);
		$set = $this->Escape($card['set']);
		$rarity = $this->Escape($card['rarity']);
		$text = $this->Escape($card['text']);
		$uses = $this->Escape($card['uses']);
		
		if ($uses == "0") $uses = "";
		
		$obtainable = $card['obtainable'];
		$training1 = $card['training1'];
		$training2 = $card['training2'];
		$trainingLevel1 = $card['trainingLevel1'];
		$trainingLevel2 = $card['trainingLevel2'];
		$magicka = $card['magicka'];
		$power = $card['power'];
		$health = $card['health'];
		
		$training = "";
		if ($training1) $training .= $this->GetCardLink($training1) . " @ Lvl $trainingLevel1";
		if ($training2) $training .= "<br/>" . $this->GetCardLink($training2) . " @ Lvl $trainingLevel2";
		
		$image = preg_replace("#.+?/.+?/(.*)#", "$1", $card['image']);
		$imageName = $this->Escape($image);
		$imageLink = "<a href='/wiki/File:$image'>$imageName</a>";
		
		$attribute = $attribute1;
		if ($attribute2 != "") $attribute .= "+$attribute2";
		
		if ($obtainable == 1)
			$obtainable = "Yes";
		else
			$obtainable = "No";
			
		$text = str_replace("\n", "<br/>", $text);
		
		$encodeName = urlencode($card['name']);
		$nameLink = $this->GetCardLink($card['name']);
		$wikiLink = "<a href=\"/wiki/Legends:$name\">Legends:$name</a>";
		
		$output .= "<td>$nameLink</td>";
		$output .= "<td>$type</td>";
		$output .= "<td>$subtype</td>";
		$output .= "<td>$magicka</td>";
		$output .= "<td>$power</td>";
		$output .= "<td>$health</td>";
		$output .= "<td>$attribute</td>";
		$output .= "<td>$class</td>";
		$output .= "<td>$set</td>";
		$output .= "<td>$rarity</td>";
		$output .= "<td>$obtainable</td>";
		$output .= "<td>$training</td>";
		$output .= "<td>$uses</td>";
		$output .= "<td>$text</td>";
		$output .= "<td>$wikiLink<br/>$imageLink</td>";
		
		$output .= "</tr>";
		return $output;
	}
	
	
	public function GetCardEditOutput()
	{
		if (!$this->CanEditCard()) return "You do not have permission to edit card data!";
		
		$output = "";
		$safeName = $this->Escape($this->inputEditCard);
		$card = $this->singleCardData;
		
		if ($this->singleCardData == null) return "No card matching '$safeName' found!";
		
		if ($this->inputCreateCard)
			$output .= "Creating new card.";
		else
			$output .= "Editing card $safeName.";
		
		$name = $this->Escape($card['name']);
		$type = $this->Escape($card['type']);
		$subtype = $this->Escape($card['subtype']);
		$attribute1 = $this->Escape($card['attribute']);
		$attribute2 = $this->Escape($card['attribute2']);
		$class = $this->Escape($card['class']);
		$set = $this->Escape($card['set']);
		$rarity = $this->Escape($card['rarity']);
		$text = $this->Escape($card['text']);
		$uses = $this->Escape($card['uses']);
		
		if ($uses == "0") $uses = "";
		
		$obtainable = $card['obtainable'];
		$training1 = $this->Escape($card['training1']);
		$training2 = $this->Escape($card['training2']);
		$trainingLevel1 = $card['trainingLevel1'];
		$trainingLevel2 = $card['trainingLevel2'];
		$magicka = $card['magicka'];
		$power = $card['power'];
		$health = $card['health'];
		
		$image = preg_replace("#.+?/.+?/(.*)#", "$1", $card['image']);
		$imageName = $this->Escape($image);
		$imageLink = "<a href='/wiki/File:$image'>$imageName</a>";
		$imageSrc = "//en.uesp.net/w/extensions/UespLegendsCards/cardimages/$name.png";
		
		if ($image == "")
		{
			$imageLink = "";
			$imageSrc = "";
		}
		
		if ($obtainable == 1)
			$obtainable = "checked";
		else
			$obtainable = "";
		
		$output .= "<form method='post' action='/wiki/Special:LegendsCardData'>";
		$output .= "<input type='hidden' value='1' name='save'>";
		
		if ($this->inputCreateCard)
		{
			$output .= "<input type='hidden' value='1' name='create'>";
		}
		else
		{
			$output .= "<input type='hidden' value='$name' name='name' id='eslegCardInputName' maxlength='100'>";
		}
		
		$output .= "<img src=\"$imageSrc\" class='eslegCardDetailsImage'><p/>";
		$output .= "<table class='eslegCardDetailsTable'>";
		
		$typeList = $this->CreateEditListOutput(self::$LEGENDS_TYPES, $card['type'], "Type");
		$raceList = $this->CreateEditListOutput(self::$LEGENDS_SUBTYPES, $card['subtype'], "Subtype");
		$attr1List = $this->CreateEditListOutput(self::$LEGENDS_ATTRIBUTES, $card['attribute'], "Attribute1");
		$attr2List = $this->CreateEditListOutput(self::$LEGENDS_ATTRIBUTES, $card['attribute2'], "Attribute2");
		$classList = $this->CreateEditListOutput(self::$LEGENDS_CLASSES, $card['class'], "Class");
		$setList = $this->CreateEditListOutput(self::$LEGENDS_SETS, $card['set'], "Set");
		$rarityList = $this->CreateEditListOutput(self::$LEGENDS_RARITIES, $card['rarity'], "Rarity");
		
		if ($this->inputCreateCard)
		{
			$output .= "<tr><th>Name</th><td><input type='text' value='$name' name='name' id='eslegCardInputName' maxlength='100'> <small>Must be unique and can't be changed later.</small></td></tr>";
		}
		else
		{
			$output .= "<tr><th>Name</th><td>$name</td></tr>";
		}
		
		$output .= "<tr><th>Type</th><td>$typeList</td></tr>";
		$output .= "<tr><th>Race</th><td>$raceList</td></tr>";
		$output .= "<tr><th>Magicka</th><td><input type='text' value='$magicka' name='magicka' id='eslegCardInputMagicka' maxlength='10'></td></tr>";
		$output .= "<tr><th>Power</th><td><input type='text' value='$power' name='power' id='eslegCardInputPower' maxlength='10'></td></tr>";
		$output .= "<tr><th>Health</th><td><input type='text' value='$health' name='health' id='eslegCardInputHealth' maxlength='10'></td></tr>";
		$output .= "<tr><th>Attribute 1</th><td>$attr1List</td></tr>";
		$output .= "<tr><th>Attribute 2</th><td>$attr2List</td></tr>";
		$output .= "<tr><th>Class</th><td>$classList</td></tr>";
		$output .= "<tr><th>Set</th><td>$setList</td></tr>";
		$output .= "<tr><th>Rarity</th><td>$rarityList</td></tr>";
		$output .= "<tr><th>Obtainable</th><td><input type='checkbox' name='obtainable' value='1' id='eslegCardInputObtainable' $obtainable></td></tr>";
		$output .= "<tr><th>Training 1</th><td><input type='text' name='training1' value='$training1' id='eslegCardInputTraining1'> @ Level <input type='text' name='trainingLevel1' value='$trainingLevel1' id='eslegCardInputTrainingLevel1'></td></tr>";
		$output .= "<tr><th>Training 2</th><td><input type='text' name='training2' value='$training2' id='eslegCardInputTraining2'> @ Level <input type='text' name='trainingLevel2' value='$trainingLevel2' id='eslegCardInputTrainingLevel2'></td></tr>";
		$output .= "<tr><th>Uses</th><td><input type='text' value='$uses' name='uses' id='eslegCardInputUses' maxlength='100'></td></tr>";
		$output .= "<tr><th>Text</th><td><textarea name='text' id='eslegCardInputText'>$text</textarea></td></tr>";
		$output .= "<tr><th>Wiki Image</th><td><input type='text' value='$imageName' name='image' id='eslegCardInputImage' maxlength='100'> <small>This will not update the popup image!</small></td></tr>";
		
		$output .= "<tr class='eslegCardRowSave'><td colspan='2' class='eslegCardRowSave'><input type='submit' value='Save'></td></tr>";
		$output .= "</table>";
		$output .= "</form>";
		
		return $output;
	}
	
	
	public function GetCardDetailsOutput()
	{
		$output = "";
		$safeName = $this->Escape($this->inputCardName);
		$card = $this->singleCardData;
		
		if ($this->singleCardData == null) return "No card matching '$safeName' found!";
		
		$output .= "Showing data for card $safeName.";		
		
		$name = $this->Escape($card['name']);
		$type = $this->Escape($card['type']);
		$subtype = $this->Escape($card['subtype']);
		$attribute1 = $this->Escape($card['attribute']);
		$attribute2 = $this->Escape($card['attribute2']);
		$class = $this->Escape($card['class']);
		$set = $this->Escape($card['set']);
		$rarity = $this->Escape($card['rarity']);
		$text = $this->Escape($card['text']);
		$uses = $this->Escape($card['uses']);
		
		if ($uses == "0") $uses = "";
		
		$obtainable = $card['obtainable'];
		$training1 = $card['training1'];
		$training2 = $card['training2'];
		$trainingLevel1 = $card['trainingLevel1'];
		$trainingLevel2 = $card['trainingLevel2'];
		$magicka = $card['magicka'];
		$power = $card['power'];
		$health = $card['health'];
		
		$training = "";
		if ($training1) $training .= $this->GetCardLink($training1) . " @ Lvl $trainingLevel1";
		if ($training2) $training .= "<br/>". $this->GetCardLink($training2) . " @ Lvl $trainingLevel2";
		
		$image = preg_replace("#.+?/.+?/(.*)#", "$1", $card['image']);
		$imageName = $this->Escape($image);
		$imageLink = "<a href='/wiki/File:$image'>$imageName</a>";
		$imageSrc = "//en.uesp.net/w/extensions/UespLegendsCards/cardimages/$name.png";
		
		$encodeName = urlencode($card['name']);
		$wikiLink = "<a href=\"/wiki/Legends:$name\">Legends:$name</a>";
				
		if ($obtainable == 1)
			$obtainable = "Yes";
		else
			$obtainable = "";

		$text = str_replace("\n", "<br/>", $text);
		
		$output .= "<img src=\"$imageSrc\" class='eslegCardDetailsImage'><p/>";
		$output .= "<table class='eslegCardDetailsTable'>";
		
		if ($this->CanEditCard())
		{
			$safeName = urlencode($card['name']);
			$output .= "<tr class='eslegCardRowSave'><td colspan='2' class='eslegCardDetailsEditRow'><a href='/wiki/Special:LegendsCardData?edit=$safeName'>Edit Card</a></td></tr>";
		}
		
		$output .= "<tr><th>Name</th><td>$name</td></tr>";
		$output .= "<tr><th>Type</th><td>$type</td></tr>";
		$output .= "<tr><th>Race</th><td>$subtype</td></tr>";
		$output .= "<tr><th>Magicka</th><td>$magicka</td></tr>";
		$output .= "<tr><th>Power</th><td>$power</td></tr>";
		$output .= "<tr><th>Health</th><td>$health</td></tr>";
		$output .= "<tr><th>Attribute 1</th><td>$attribute1</td></tr>";
		$output .= "<tr><th>Attribute 2</th><td>$attribute2</td></tr>";
		$output .= "<tr><th>Class</th><td>$class</td></tr>";
		$output .= "<tr><th>Set</th><td>$set</td></tr>";
		$output .= "<tr><th>Rarity</th><td>$rarity</td></tr>";
		$output .= "<tr><th>Obtainable</th><td>$obtainable</td></tr>";
		$output .= "<tr><th>Training</th><td>$training</td></tr>";
		$output .= "<tr><th>Uses</th><td>$uses</td></tr>";
		$output .= "<tr><th>Text</th><td>$text</td></tr>";
		$output .= "<tr><th>Wiki Link</th><td>$wikiLink</td></tr>";
		$output .= "<tr><th>Wiki Image</th><td>$imageLink</td></tr>";		
				
		$output .= "</table>";
		
		return $output;
	}
	
	
	public function GetCardTableOutput()
	{	
		$output = "";
		$cardCount = count($this->cards);
				
		if ($this->CanCreateCard())
		{
			$output .= "<div class='eslegCardCreate'><a href='/wiki/Special:LegendsCardData?create=1'>Create Card</a></div>";
		}
		
		$output .= "Showing data for $cardCount matching cards.<p/>";
		
		$output .= "<table class='eslegCardDataTable'>";
		$output .= "<tr>";
		$output .= "<th>Card</th>";
		$output .= "<th>Type</th>";
		$output .= "<th>Race</th>";
		$output .= "<th>Magicka</th>";
		$output .= "<th>Power</th>";
		$output .= "<th>Health</th>";
		$output .= "<th>Attribute</th>";
		$output .= "<th>Class</th>";
		$output .= "<th>Set</th>";
		$output .= "<th>Rarity</th>";
		$output .= "<th>Obtainable</th>";
		$output .= "<th>Training</th>";
		$output .= "<th>Uses</th>";
		$output .= "<th>Description</th>";
		$output .= "<th>Links</th>";
		$output .= "</tr>";
				
		foreach ($this->cards as $name => $card)
		{
			$output .= $this->getCardOutputRow($card);
		}
		
		$output .= "</table>";
		
		return $output;
	}
	
	
	public function UpdateCardImage($name, $image)
	{
		if ($image == "") return true;
		
		$result = CreateLegendsPopupImage($name, $image, "/mnt/uesp/legendscards/");
		
		if (!$result)
		{
			$this->errorMsg = "Failed to update card popup image!";
			return true;
		}
		
		return true;
	}
	
	
	public function SaveCard()
	{
		if (!$this->InitDatabaseWrite()) return false;
		
		$name = $this->db->real_escape_string($this->inputCardName);
		$type = $this->db->real_escape_string($this->inputCardData['type']);
		$subtype = $this->db->real_escape_string($this->inputCardData['subtype']);
		$text = $this->db->real_escape_string($this->inputCardData['text']);
		
		$image = "";
		$imageBase = $this->inputCardData['image'];
		$imageHash = GetLegendsImagePathHash($imageBase);
		if ($imageBase != "") $image = "/" . $imageHash . $imageBase;
		$image = $this->db->real_escape_string($image);
		
		$class = $this->db->real_escape_string($this->inputCardData['class']);
		$set = $this->db->real_escape_string($this->inputCardData['set']);
		$rarity = $this->db->real_escape_string($this->inputCardData['rarity']);
		$uses = $this->db->real_escape_string($this->inputCardData['uses']);
		$attribute1 = $this->db->real_escape_string($this->inputCardData['attribute']);
		$attribute2 = $this->db->real_escape_string($this->inputCardData['attribute2']);
		$training1 = $this->db->real_escape_string($this->inputCardData['training1']);
		$training2 = $this->db->real_escape_string($this->inputCardData['training2']);
		$trainingLevel1 = $this->inputCardData['trainingLevel1'];
		$trainingLevel2 = $this->inputCardData['trainingLevel2'];
		$obtainable = $this->inputCardData['obtainable'];
		$magicka = $this->inputCardData['magicka'];
		$power = $this->inputCardData['power'];
		$health = $this->inputCardData['health'];
		
		$cardExists = $this->DoesCardExist($this->inputCardName);
		
		if ($this->inputCreateCard)
		{
			if ($cardExists) 
			{
				$this->errorMsg = "The card '{$this->inputCardName}' already exists!";
				return false;
			}
			
			$query = "INSERT INTO cards SET ";
			$query .= " name='$name',";
		}
		else
		{
			if (!$cardExists) 
			{
				$this->errorMsg = "The card '{$this->inputCardName}' does not exist!";
				return false;
			}
			
			$query = "UPDATE cards SET ";
		}
		
		$query .= " type='$type',";
		$query .= " subtype='$subtype',";
		$query .= " magicka='$magicka',";
		$query .= " power='$power',";
		$query .= " health='$health',";
		$query .= " uses='$uses',";
		$query .= " attribute='$attribute1',";
		$query .= " attribute2='$attribute2',";
		$query .= " `class`='$class',";
		$query .= " `set`='$set',";
		$query .= " rarity='$rarity',";
		$query .= " text='$text',";
		$query .= " image='$image',";
		$query .= " obtainable='$obtainable',";
		$query .= " training1='$training1',";
		$query .= " training2='$training2',";
		$query .= " trainingLevel1='$trainingLevel1',";
		$query .= " trainingLevel2='$trainingLevel2'";
		
		if (!$this->inputCreateCard) $query .= " WHERE name='$name'";
		$query .= ";";
		
		$result = $this->db->query($query);
		if ($result === false) return false;
		
		return $this->UpdateCardImage($this->inputCardName, $image);
	}
	
	
	public function GetCardSaveOutput()
	{
		$output = "";
		
		if ($this->inputCreateCard && !$this->CanCreateCard()) return "You do not have permission to create card data!";
		if (!$this->CanEditCard()) return "You do not have permission to edit card data!";
		
		if ($this->inputCreateCard && $this->inputCardName == "")
		{
			$output .= "<b>Missing required card name!</b> ";
			
			$this->inputEditCard = $this->inputCardName;
			$this->singleCardData = $this->inputCardData;
			
			$output .= $this->GetCardEditOutput();
			
			return $output;
		}
				
		if (!$this->SaveCard())
		{
			$output .= "<b>Error saving card data!</b> " . $this->errorMsg . " ";
			if ($this->db->error) $output .= "<p>DB Error: " . $this->db->error . "<p>";
			
			$this->inputEditCard = $this->inputCardName;
			$this->singleCardData = $this->inputCardData;
			
			$output .= $this->GetCardEditOutput();
		}
		else
		{
			$this->singleCardData = $this->inputCardData;
			
			if ($this->inputCreateCard)
				$output .= "<b>Saved new card data!</b> " . $this->errorMsg . " ";
			else
				$output .= "<b>Saved card data!</b> " . $this->errorMsg . " ";
			
			$output .= $this->GetCardDetailsOutput();
		}
		
		return $output;
	}
	
	
	public function getOutput()
	{
		$output = $this->GetBreadcrumbTrail();
		
		if ($this->inputSaveCard)
		{
			$output .= $this->GetCardSaveOutput();
		}
		else if ($this->inputCreateCard)
		{
			if (!$this->CanCreateCard()) return "Error: You do not have permission to create card data!";
			$this->singleCardData = $this->inputCardData;
			$output .= $this->GetCardEditOutput();
		}
		else if ($this->inputCardName != "")
		{
			if (!$this->LoadCardData()) return "Error: Failed to load the Legends card data!";
			$output .= $this->GetCardDetailsOutput();
		}
		else if ($this->inputEditCard != "")
		{
			if (!$this->LoadCardData()) return "Error: Failed to load the Legends card data!";
			$output .= $this->GetCardEditOutput();
		}
		else
		{
			if (!$this->LoadCardData()) return "Error: Failed to load the Legends card data!";
			$output .= $this->GetCardTableOutput();
		}
		
		return $output;
	}
	
};