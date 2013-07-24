<?php defined( 'VALID_INCLUSION' ) or die();

class Admininstration {
	const SENDER = 0;
	const FRIEND = 1;

	public $totalSent = 0;
	public $users = null;

	public function __construct() {
		$this->authenticate();
		$this->load();
		return true;
	}

	public function authenticate() {
		if ( strlen( ADMIN_USER ) == 0 || strlen( ADMIN_PASSWORD ) == 0 ) {
			return true;
		}

		if ( isset( $_SERVER[ 'PHP_AUTH_USER' ] ) && isset( $_SERVER[ 'PHP_AUTH_PW' ] ) ) {
			if ( $_SERVER[ 'PHP_AUTH_USER' ] == ADMIN_USER &&  $_SERVER[ 'PHP_AUTH_PW' ] == ADMIN_PASSWORD ) {
				return true;
			}
		}

		header( 'WWW-Authenticate: Basic realm="Administration"' );
		header( 'HTTP/1.0 401 Unauthorized' );
		echo 'No unauthorized access.';
		exit;
	}

	public function load() {
		$db = Database::init();
		$query = $db->prepare( 'SELECT SUM( `sent` ) FROM #_emails' );
		if ( $query->execute() ) {
			$this->totalEmails = intval( $query->fetchColumn() );
		}
		$query->closeCursor();

		$this->users = array();
		$query = $db->prepare(
			'SELECT u.`id` AS id, `name`, `email`, `zip`, IF( u.`id` = `sender_id`, :sender, :friend ) AS type, i.`confirmed`, u.`subscribed`
			FROM #_users AS u LEFT JOIN `taf_invites` AS i ON ( u.`id` = i.`sender_id` OR u.`id` = i.`friend_id` )'
		);
		if ( $query->execute( array( 'sender' => self::SENDER, 'friend' => self::FRIEND ) ) ) {
			while( ( $user = $query->fetchObject() ) ) {
				$user->id = intval( $user->id );
				$user->type = intval( $user->type );
				$this->users[ $user->id ] = $user;
				$this->users[ $user->id ]->link = $this->link( $user->id );
			}
		}
		$query->closeCursor();

		return true;
	}

	public function user( $id ) {
		if ( isset( $this->users[ $id ] ) == false ) {
			throw new UserNotFoundException( $id );
		}

		if ( $this->users[ $id ]->type == self::SENDER ) {
			$this->user = new Sender();
		} else {
			$this->user = new Friend();
		}

		if ( $this->user->load( $this->users[ $id ]->email ) == false ) {
			throw new UserLoadErrorException( $id );
		}

		return true;
	}

	public function search( $email ) {
		$db = Database::init();
		$query = $db->prepare( 'SELECT `id` FROM #_users WHERE `email` LIKE :email ORDER BY `id`' );
		if ( $query->execute( array( 'email' => "%$email%" ) ) == false ) {
			throw new UserNotFoundException( '', 'No users found.' );
		}
		$result = array();
		while( $id = intval( $query->fetchColumn() ) ) {
			array_push( $result, $id );
		}
		$query->closeCursor();

		foreach( $this->users as $id => $user ) {
			if ( in_array( $id, $result ) == false ) {
				unset( $this->users[ $id ] );
			}
		}

		return true;
	}

	public function link( $id = null ) {
		return sprintf(
			( USE_CLEAN_URLS ? '%s%s/%s%s' : '%s/index.php?view=%s&user=%d' ),
			SITE_URL, 'admin', empty( $id ) ? '' : 'view/', empty( $id ) ? '' : $id
		);
	}
};