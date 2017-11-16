<?php 


require_once("/home/uesp/secrets/legends.secrets");
require_once("legendsCommon.php");


class CUespLegendsCardDataViewer
{
	
	public $inputParams = array();
	public $inputCardName = "";
	
	public $wikiContext = null;
	public $db = null;
	
	public $cards = array();
	public $singleCardData = null;


	public function __construct ()
	{
		$this->inputParams = $_REQUEST;
		$this->ParseInputParams();	
	}
	
	
	public function ParseInputParams()
	{
		if ($this->inputParams['name'] != "")
		{
			$this->inputCardName = $this->inputParams['name'];
		}
		
		if ($this->inputParams['card'] != "")
		{
			$this->inputCardName = $this->inputParams['card'];
		}

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
	
	
	public function GetCardDataQuery()
	{
		if ($this->inputCardName != "")
		{
			$safeName = $this->db->real_escape_string($this->inputCardName);
			$query = "SELECT * FROM cards WHERE name='$safeName';";
		}
		else
		{
			$query = "SELECT * FROM cards;";
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
		$training = $card['training'];
		$magicka = $card['magicka'];
		$power = $card['power'];
		$health = $card['health'];
		
		$image = preg_replace("#.+?/.+?/(.*)#", "$1", $card['image']);
		$imageName = $this->Escape($image);
		$imageLink = "<a href='/wiki/File:$image'>$imageName</a>";
		
		$attribute = $attribute1;
		if ($attribute2 != "") $attribute .= "+$attribute2";
		
		if ($obtainable == 1)
			$obtainable = "Yes";
		else
			$obtainable = "";
		
		if ($training == 1)
			$training = "Yes";
		else
			$training = "";
		
			
		$text = str_replace("\n", "<br/>", $text);
		
		$encodeName = urlencode($card['name']);
		$nameLink = "<a href=\"/wiki/Special:LegendsCardData?card=$encodeName\" class='legendsCardLink' card=\"$name\">$name</a>";
		
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
		$output .= "<td>$imageLink</td>";
		
		$output .= "</tr>";
		return $output;
	}
	
	
	public function GetCardDetailsOutput()
	{
		$output = "";
		$safeName = $this->Escape($this->inputCardName);
		
		if ($this->singleCardData == null)
		{
			return "No card matching '$safeName' found!";
		}
			
		$card = $this->singleCardData;
		
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
		$training = $card['training'];
		$magicka = $card['magicka'];
		$power = $card['power'];
		$health = $card['health'];
		
		$image = preg_replace("#.+?/.+?/(.*)#", "$1", $card['image']);
		$imageName = $this->Escape($image);
		$imageLink = "<a href='/wiki/File:$image'>$imageName</a>";
		$imageSrc = "//en.uesp.net/w/extensions/UespLegendsCards/cardimages/$name.png";
				
		if ($obtainable == 1)
			$obtainable = "Yes";
		else
			$obtainable = "";
		
		if ($training == 1)
			$training = "Yes";
		else
			$training = "";

		$text = str_replace("\n", "<br/>", $text);
		
		$output .= "<img src='$imageSrc' class='eslegCardDetailsImage'><p/>";
		$output .= "<table class='eslegCardDetailsTable'>";
		
		$output .= "<tr><th>Name</th><td>$name</td></tr>";
		$output .= "<tr><th>Type</th><td>$type</td></tr>";
		$output .= "<tr><th>Subtype</th><td>$subtype</td></tr>";
		$output .= "<tr><th>Magicka</th><td>$magicka</td></tr>";
		$output .= "<tr><th>Power</th><td>$power</td></tr>";
		$output .= "<tr><th>Health</th><td>$health</td></tr>";
		$output .= "<tr><th>Attribute 1</th><td>$attribute1</td></tr>";
		$output .= "<tr><th>Attribute 2</th><td>$attribute2</td></tr>";
		$output .= "<tr><th>Class</th><td>$class</td></tr>";
		$output .= "<tr><th>Set</th><td>$set</td></tr>";
		$output .= "<tr><th>Rarity</th><td>$rarity</td></tr>";
		$output .= "<tr><th>Obtainable</th><td>$obtainable</td></tr>";
		$output .= "<tr><th>Traing</th><td>$training</td></tr>";
		$output .= "<tr><th>Uses</th><td>$uses</td></tr>";
		$output .= "<tr><th>Text</th><td>$text</td></tr>";
		$output .= "<tr><th>Wiki Image</th><td>$imageLink</td></tr>";
		
		$output .= "</table>";
		
		return $output;
	}
	
	
	public function GetCardTableOutput()
	{	
		$output = "";
		
		$cardCount = count($this->cards);
		$output .= "Showing data for $cardCount matching cards.";
		
		$output .= "<table class='eslegCardDataTable'>";
		$output .= "<tr>";
		$output .= "<th>Card</th>";
		$output .= "<th>Type</th>";
		$output .= "<th>Subtype</th>";
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
		$output .= "<th>Image</th>";
		$output .= "</tr>";
				
		foreach ($this->cards as $name => $card)
		{
			$output .= $this->getCardOutputRow($card);
		}
		
		$output .= "</table>";
		
		return $output;
	}
	
	
	public function getOutput()
	{
		if (!$this->LoadCardData()) return "Error: Failed to load the Legends card data!";
		
		$output = "";
		
		if ($this->inputCardName != "")
			$output = $this->GetCardDetailsOutput();
		else
			$output = $this->GetCardTableOutput();
		
		return $output;
	}
	
};