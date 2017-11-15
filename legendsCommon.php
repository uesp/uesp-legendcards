<?php 

"CREATE TABLE IF NOT EXISTS logEntry (
						id BIGINT NOT NULL AUTO_INCREMENT,
						gameTime INTEGER NOT NULL,
						timeStamp BIGINT NOT NULL,
						entryHash BIGINT NOT NULL,
						userName TINYTEXT NOT NULL,
						ipAddress TINYTEXT NOT NULL,
						PRIMARY KEY (id),
						INDEX unique_entry (gameTime, timeStamp, entryHash)
					);";


function CreateLegendsTables($db)
{
	$query = "CREATE TABLE IF NOT EXISTS cards (
						name TINYTEXT NOT NULL,
						type TINYTEXT NOT NULL,
						subtype TINYTEXT NOT NULL,
						text TEXT NOT NULL,
						image TINYTEXT NOT NULL,
						magicka INTEGER NOT NULL DEFAULT 0,
						power INTEGER NOT NULL DEFAULT 0,
						health INTEGER NOT NULL DEFAULT 0,
						rarity TINYTEXT NOT NULL,
						attribute TINYTEXT NOT NULL,
						attribute2 TINYTEXT NOT NULL,
						`set` TINYTEXT NOT NULL,
						`class` TINYTEXT NOT NULL,
						obtainable TINYINT(1) NOT NULL DEFAULT 0,
						training TINYINT(1) NOT NULL DEFAULT 0,
						uses TINYTEXT NOT NULL,
						PRIMARY KEY (name(32)),
						INDEX index_type (type(3), subtype(3)),
						INDEX index_subtype (subtype(3)),
						INDEX index_attribute (attribute(3)),
						INDEX index_attribute2 (attribute2(3)),
						INDEX index_set (`set`(16)),
						INDEX index_class (`class`(3)),
						INDEX index_rarity (rarity(3)),
						FULLTEXT (name, text)
					);";
	
	$result = $db->query($query);
	if ($result === false) return "Failed to create the cards table!";
	
	$query = "CREATE TABLE IF NOT EXISTS logInfo (
						id TINYTEXT NOT NULL,
						value TINYTEXT NOT NULL,
						PRIMARY KEY (id(16))
					);";
	
	$result = $db->query($query);
	if ($result === false) return "Failed to create the logInfo table!";
	
	return true;
}


function UpdateLegendsPageViews($id, $db = null)
{
	global $uespLegendsWriteDBHost, $uespLegendsWriteUser, $uespLegendsWritePW, $uespLegendsDatabase;

	$deleteDb = false;

	if ($db == null)
	{
		$deleteDb = true;
		$db = new mysqli($uespLegendsWriteDBHost, $uespLegendsWriteUser, $uespLegendsWritePW, $uespLegendsDatabase);
		if ($db->connect_error) return false;
	}

	$query = "UPDATE logInfo SET value=value+1 WHERE id='$id';";
	$result = $db->query($query);

	if ($deleteDb) $db->close();

	return $result !== false;
}