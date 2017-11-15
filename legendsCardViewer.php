<?php 


require_once("/home/uesp/secrets/legends.secrets");
require_once("legendsCommon.php");


class CUespLegendsCardDataViewer
{
	
	public $wikiContext = null;
	public $db = null;
	
	public $cards = array();


	public function __construct ()
	{
		
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
	
	
	public function LoadCardData()
	{
		if (!$this->InitDatabase()) return false;
		
		$query = "SELECT * FROM cards;";
		$result = $this->db->query($query);
		if ($result === false) return $this->ReportError("ERROR: Failed to load card data from table!");
		
		$this->cards = array();
		
		while (($card = $result->fetch_assoc()))
		{
			$name = $card['name'];
			$this->cards[$name] = $card;
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
		
		$output .= "<td>$name</td>";
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
	
	
	public function getOutput()
	{
		if (!$this->LoadCardData()) return "Error: Failed to load the Legends card data!";
		
		$output = "";
		
		$cardCount = count($this->cards);
		$output .= "Showing data for $cardCount matching cards.";
		
		$output .= "<table class='esolegCardDataTable'>";
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
	
};