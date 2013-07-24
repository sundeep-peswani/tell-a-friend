<?php defined( 'VALID_INCLUSION' ) or die();

class Database {
	private static $instance = null;

	private $link = null;
	private $engine = 'mysql';
	private $host = '';
	private $username = '';
	private $password = '';
	private $database = '';
	private $prefix = '';

	private function __construct() {
		if ( DB_HOST == '' || DB_USER == '' || DB_PASSWORD == '' || DB_DATABASE == '' ) {
			trigger_error( 'Database settings have not been configured properly.', E_ERROR );
			return false;
		}

		try {
			$dsn = sprintf( '%s:dbname=%s;host=%s', $this->engine, DB_DATABASE, DB_HOST );
			$this->link = new PDO( $dsn, DB_USER, DB_PASSWORD );
		} catch ( Exception $e ) {
			trigger_error( 'Could not connect to the database.', E_ERROR );
			return false;
		}

		$this->link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

		$this->host = DB_HOST;
		$this->username = DB_USER;
		$this->password = DB_PASSWORD;
		$this->database = DB_DATABASE;
		$this->prefix = DB_PREFIX;

		return true;
	}

	public function __destruct() {
		if ( isset( self::$instance ) ) {
			self::$instance = null;
		}

		return true;
	}

	public static function init() {
		if ( isset( self::$instance ) == false ) {
			$class = __CLASS__;
			self::$instance = new $class();
		}

		return self::$instance;
	}

	public function __call( $name, $arguments ) {
		if ( method_exists( $this->link, $name ) == false ) {
			throw new DatabaseException();
		}

		// Prefix replacement
		foreach( $arguments as $ii => $argument ) {
			if ( is_string( $argument ) ) {
				$arguments[ $ii ] = str_replace( '#_', $this->prefix, $argument );
			}
		}

		return call_user_func_array( array( $this->link, $name ), $arguments );
	}
};