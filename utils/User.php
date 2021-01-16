<?php
/**
 * Project: Blog Management System With Sevida-Like UI
 * Developed By: Ahmad Tukur Jikamshi
 *
 * @facebook: amaedyteeskuserId
 * @twitter: amaedyteeskuserId
 * @instagram: amaedyteeskuserId
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
		$userId = $db->prepare( 'SELECT id FROM Person WHERE userName=:value OR email=:value LIMIT 1' );
		$userId->execute( [ 'value' => $value ] );
		if( $userId->rowCount() === 0 )
			return 0;
		$userId = (int) $userId->fetchColumn();
		return $userId;
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
