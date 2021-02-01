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
class Database extends PDO {	
	private $dbhost;
	private $dbname;
	private $dbuser;
	private $dbpass;

	public $charset;
	public $connect = false;

	public static $VERSION = 1;
	public static $TABLES = [
		'links',
		'options',
		'posts',
		'posts_meta',
		'Comment',
		'Comment_meta',
		'terms',
		'TermMeta',
		'TermLink',
		'TermInfo',
		'users',
		'UzerMeta'
	];
	function __construct( string $host, string $name, string $charset, string $user, string $pass ) {
		$this->dbhost = $host;
		$this->dbname = $name;
		$this->dbuser = $user;
		$this->dbpass = $pass;
		$this->charset = $charset;
	}
	/**
	 * Instantiates a database connection, displays an error page on connection failure
	 * @return bool
	 */
	function dbConnect() : bool {
		$pdoString = sprintf( 'mysql:host=%s;dbname=%s;charset=%s', $this->dbhost, $this->dbname, $this->charset );
		try {
			PDO::__construct( $pdoString, $this->dbuser, $this->dbpass );
			PDO::setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ );
			PDO::setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			PDO::setAttribute( PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true );
			$this->connect = true;
		} catch( Exception $e ) {
			return false;
		}
		return true;
	}
	function execFile( string $sqlFile ) : bool {
		$mQuery = file_exists($sqlFile) ? file_get_contents($sqlFile) : '';
		if( empty($mQuery) )
			throw( new Exception('IO Error') );
		PDO::exec($mQuery);
		return true;
	}
	function fetchClass( PDOStatement $stmt, $class ) {
		$stmt = $stmt->fetchAll( PDO::FETCH_CLASS, $class );
		if( empty( $stmt ) )
			return false;
		$stmt = $stmt[0];
		return $stmt;
	}
	function fetchMeta( PDOStatement $mQuery, $arrMode = false ) {
		$mQuery = $mQuery->fetchAll( PDO::FETCH_COLUMN|PDO::FETCH_GROUP );
		foreach( $mQuery as $index => &$entry ) {
			$entry = $entry[0];
			$entry = trim($entry);
			if( is_numeric($entry) )
				$entry = (int) $entry;
		}
		if( ! $arrMode )
			$mQuery = (object) $mQuery;
		return $mQuery;
	}
	function quoteList( ...$values ) : string {
		if( is_array($values[0]) )
			$values = $values[0];
		foreach( $values AS &$value ) {
			$value = parent::quote( $value );
		}
		$values = implode( ',', $values );
		return $values;
	}
}
