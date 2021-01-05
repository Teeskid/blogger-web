<?php
/**
 * Project: Blog Management System With Sevida-Like UI
 * Developed By: Ahmad Tukur Jikamshi
 *
 * @facebook: amaedyteeskpersonId
 * @twitter: amaedyteeskpersonId
 * @instagram: amaedyteeskpersonId
 * @whatsapp: +2348145737179
 */
class User {
	public $id;
	public $picture;
	public $fullName;
	public $userName;
	public $email;
	public $password;
	public $role;
	public $status;
	public $meta = [];

	public static function findId( string $value ) : int {
		global $db;
		$personId = $db->prepare( 'SELECT id FROM Person WHERE userName=:value OR email=:value LIMIT 1' );
		$personId->execute( [ 'value' => $value ] );
		$personId = parseInt( $personId->fetchColumn() );
		return $personId;
	}
	public static function getFields( string ...$fields ) {
		global $db, $_login;
		if( ! LOGGED_IN || ! isset($fields[0]) )
			return false;
		$fields = implode( ',', $fields );
		$fields = $db->prepare( 'SELECT ' . $fields .' FROM Person WHERE id=? LIMIT 1' );
		$fields->execute( [ $_login->userId ] );
		$fields = $fields->fetch();
		return $fields;
	}
}
