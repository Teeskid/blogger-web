<?php
/**
 * User Object Model
 * @package Sevida
 * @subpackage Utilities
 */
class User {
	/**
	 * Pattern for a valid namespace
	 * @var string
	 */
	const REGEX_USERNAME = '#^[a-z0-9_]{5,20}$#i';
	/**
	 * Valid syntax for matching user passwords
	 * @var string
	 */
	const REGEX_PASSWORD = '#^[^<>\{\}\[\]]{5,20}$#i';
	/**
	 * Pattern for a valid email address
	 * @var string
	 */
	const REGEX_EMAIL = '#^[\w\d\._]{5,15}\@[\d\w\.]{3,15}$#i';
	/**
	 * @var string
	 */
	const ROLE_OWNER = 'owner';
	/**
	 * @var string
	 */
	const ROLE_AUTHOR = 'author';
	/**
	 * @var int
	 */
	public $id;
	/**
	 * @var int
	 */
	public $picture;
	/**
	 * @var string
	 */
	public $fullName;
	/**
	 * @var string
	 */
	public $userName;
	/**
	 * @var string
	 */
	public $email;
	/**
	 * @var string
	 */
	public $password;
	/**
	 * @var string
	 */
	public $role;
	/**
	 * @var string
	 */
	public $status;
	/**
	 * Finds user id by email or username
	 * @global $_db
	 * @param mixed $value Criteria
	 * @return int
	 */
	public static function findId( string $value ) : int {
		global $_db;
		$userId = $_db->prepare( 'SELECT id FROM Uzer WHERE userName=:value OR email=:value LIMIT 1' );
		$userId->execute( [ 'value' => $value ] );
		if( $userId->rowCount() === 0 )
			return 0;
		$userId = (int) $userId->fetchColumn();
		return $userId;
	}
	/**
	 * Fetches single or multiple user fields
	 * @global $_db
	 * @param int $userId
	 * @param array $fields
	 * @return array|string
	 */
	public static function getFields( int $userId, string ...$fields ) {
		global $_db;
		$values = $_db->prepare( 'SELECT ' . implode( ',', $fields ) .' FROM Uzer WHERE id=? LIMIT 1' );
		$values->execute( [ $userId ] );
		if( isset($fields[1]) )
			$values = $values->fetch();
		else
			$values = $values->fetchColumn();
		return $values;
	}
	public static function getMetaFields( string ...$fields ) {
		global $_db, $_usr;
		if( ! isset($_usr) || ! isset($fields[0]) )
			return false;
		$fields = array_map( 'escQuote', $fields );
		$fields = implode( ',', $fields );
		$fields = $_db->prepare( 'SELECT metaKey, metaValue FROM UzerMeta WHERE userId=? AND metaKey IN(' . $fields . ')' );
		$fields->execute( [ $_usr->id ] );
		$fields = $fields->fetchAll( PDO::FETCH_KEY_PAIR );
		return $fields;
	}
}
