<?php
/*
 * UespLegendsCards -- by Dave Humphrey, dave@uesp.net, November 2017
 * 
  */

if ( !defined( 'MEDIAWIKI' ) ) {
	echo <<<EOT
To install my extension, put the following line in LocalSettings.php:
require_once( "\$IP/extensions/UespLegendsCards/UespLegendsCards.php" );
EOT;
	exit( 1 );
}

require_once("/home/uesp/secrets/legends.secrets");
require_once("legendsCommon.php");


$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'LegendsCardData',
	'author' => 'Dave Humphrey (dave@uesp.net)',
	'url' => '//en.uesp.net/wiki/UESPWiki:Legends Card Data',
	'descriptionmsg' => 'legendscarddata-desc',
	'version' => '0.1.0',
);

$wgAutoloadClasses['SpecialLegendsCardData'] = __DIR__ . '/SpecialLegendsCardData.php';
$wgMessagesDirs['LegendsCardData'] = __DIR__ . "/i18n";
$wgExtensionMessagesFiles['LegendsCardDataAlias'] = __DIR__ . '/UespLegendsCards.alias.php';
$wgSpecialPages['LegendsCardData'] = 'SpecialLegendsCardData';

$wgHooks['BeforePageDisplay'][] = 'uespLegendsCardData_beforePageDisplay';
$wgHooks['ParserFirstCallInit'][] = 'uespLegendsCardData_ParserInit';

$wgGroupPermissions['*']['legendscarddata_edit'] = false;
$wgGroupPermissions['*']['legendscarddata_add'] = false;
$wgGroupPermissions['sysop']['legendscarddata_edit'] = true;
$wgGroupPermissions['sysop']['legendscarddata_add'] = true;


function uespLegendsCardData_beforePageDisplay(&$out) 
{
	global $wgScriptPath;
	
	$out->addHeadItem("uesp-legendscards-css", "<link rel='stylesheet' href='//en.uesp.net/w/extensions/UespLegendsCards/UespLegendsCards.css?version=16Nov2017' />");
	$out->addHeadItem("uesp-legendscards-js", "<script src='//en.uesp.net/w/extensions/UespLegendsCards/UespLegendsCards.js?version=16Nov2017'></script>");
	
	return true;
}


function uespLegendsCardData_ParserInit(Parser $parser)
{
	$parser->setHook('legendscard', 'uespRenderLegendsCard');
	return true;
}


function uespRenderLegendsCard($input, array $args, Parser $parser, PPFrame $frame)
{
	$output = "";
	$cardName = "";
	
	foreach ($args as $name => $value)
	{
		$name = strtolower($name);

		if ($name == "card" || $name == "name")
			$cardName = $value;

	}

	$cardURL = "/wiki/Special:LegendsCardData?";
	
	if ($cardName != "") 
	{
		$cardURL .= "&card=" . urlencode($cardName);
		$attributes .= "card=\"" . htmlspecialchars($cardName) . "\" ";
	}
	
	$output = "<a href='$cardURL' class='legendsCardLink' $attributes>$input</a>";

	return $output;
}
