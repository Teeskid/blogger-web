<?php
/**
 * Project: Blog Management System With Sevida-Like UI
 * Developed By: Ahmad Tukur Jikamshi
 *
 * @facebook: amaedyteeskid
 * @twitter: amaedyteeskid
 * @instagram: amaedyteeskid
 * @whatsapp: +2348145737179
 */
function sql_from_file($db, $file, &$error)
{
	$file = file_get_contents($file);
	sql_from_raw($db, $file, $error);
}
function sql_from_raw($db, $sql, &$error)
{
	$sql = str_replace("\n", '', $sql);
	$sql = preg_split('/\;/', $sql);
	$sql = array_filter($sql, function($elem){ return (strlen($elem) > 5); });
	try
	{
		$db->beginTransaction();
		foreach($sql AS &$exec) { $db->exec($exec); $exec = null; }
		$db->commit();
	}
	catch(Exception $e)
	{
		$db->rollBack();
		$error[] = $e->getMessage();
	}
}
