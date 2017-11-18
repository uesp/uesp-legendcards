<?php

if (php_sapi_name() != "cli") die("Can only be run from command line!");

print("\tCreating Legends card popup images from wiki images...\n");

	/* Database users, passwords and other secrets */
require_once("/home/uesp/secrets/legends.secrets");
require_once("legendsCommon.php");

$db = new mysqli($uespLegendsReadDBHost, $uespLegendsReadUser, $uespLegendsReadPW, $uespLegendsDatabase);
if ($db->connect_error) exit("Could not connect to legends database!");

$queryResult = $db->query("SELECT * FROM cards;");
if ($queryResult === false) exit("Failed to load card data!");

$cardCount = 0;
$imageCount = 0;

while (($card = $queryResult->fetch_assoc()))
{
	$cardCount++;
	
	$result = CreateLegendsPopupImage($card['name'], $card['image'], "./cardimages/", true);
	if ($result) $imageCount++;
}

print("Loaded $cardCount cards and saved $imageCount images!\n");
