<?php

$BASE_IMAGE_PATH = "/home/uesp/www/w/images/";
$TARGET_PATH = "./cardimages/";
$TARGET_WIDTH = 200;
$TARGET_HEIGHT = 324;

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
		
	$cardName = $card['name'];
	$imageBaseName = $card['image'];
	$imageFilename = $BASE_IMAGE_PATH . $imageBaseName;
	$outputFilename = $TARGET_PATH . $cardName . ".png";
	
	if ($imageBaseName == "")
	{
		print("\t$cardName: Has no image file set!\n");
		continue;
	}
	
	//print("\t$cardName: $imageBaseName\n");
	
	if (!file_exists($imageFilename))
	{
		print("\t$cardName: Image file '$imageFilename' not found!\n");
		continue;
	}
	
	$image = imagecreatefrompng($imageFilename);
	$resizeImage = imagecreatetruecolor($TARGET_WIDTH, $TARGET_HEIGHT);
	
	if ($image == null || $resizeImage == null)
	{
		print("\t$cardName: Failed to create PNG image from file '$imageFilename'!\n");
		continue;
	}
	
	imagealphablending($resizeImage, false);
	imagesavealpha($resizeImage, true);
	$transparent = imagecolorallocatealpha($resizeImage, 255, 255, 255, 127);
	imagefilledrectangle($resizeImage, 0, 0, $TARGET_WIDTH, $TARGET_HEIGHT, $transparent);
	
	imagecopyresampled($resizeImage, $image, 0, 0, 0, 0, $TARGET_WIDTH, $TARGET_HEIGHT, imagesx($image), imagesy($image));
	
	$fileResult = imagepng($resizeImage, $outputFilename);
	
	if (!$fileResult)
	{
		print("\t$cardName: Failed to save resized PNG image to file '$outputFilename'!\n");
		continue;
	}
	
	print("\t$cardName: Saved image to '$outputFilename'!\n");	
	
	$imageCount++;
}


print("Loaded $cardCount cards and saved $imageCount images!\n");
