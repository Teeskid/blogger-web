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
require( dirname(__FILE__) . '/Load.php' );

$option = request( 'id', 'subject' );
if( ! ( $option->id || $option->subject ) )
	die();
if( $option->id ) {
	$termList = $db->prepare( 'SELECT id, master, title, about, objects FROM Term WHERE id=? LIMIT 1' );
	$termList->execute( [ $option->id ] );
	$termList = $termList->fetch(PDO::FETCH_ASSOC);
} else {
	$termList = $db->prepare( 'SELECT id, master, title, subject FROM Term WHERE subject=? ORDER BY objects ASC, title ASC LIMIT 5' );
	$termList->execute( [ $option->subject ] );
	$termList = $termList->fetchAll();
}
jsonOutput( $termList );
