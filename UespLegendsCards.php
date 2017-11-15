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

$wgGroupPermissions['*']['legendscarddata_edit'] = false;
$wgGroupPermissions['*']['legendscarddata_add'] = false;
$wgGroupPermissions['sysop']['legendscarddata_edit'] = true;
$wgGroupPermissions['sysop']['legendscarddata_add'] = true;


function uespLegendsCardData_beforePageDisplay(&$out) 
{
	global $wgScriptPath;
	
	$out->addHeadItem("uesp-legendscards-css", "<link rel='stylesheet' href='//content3.uesp.net/w/extensions/UespLegendsCards/UespLegendsCards.css?version=15Nov2017' />");
	$out->addHeadItem("uesp-legendscards-js", "<script src='//content3.uesp.net/w/extensions/UespLegendsCard/UespLegendsCards?version=15Nov2017'></script>");
	
	return true;
}





