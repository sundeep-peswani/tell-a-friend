<?php defined( 'VALID_INCLUSION' ) or die();

require_once './system/lib/validator.inc.php';
require_once './system/lib/classes/class.sender.php';
require_once './system/lib/classes/class.confirmation.php';

class Friend {
	private $id = 0;
	private $name = '';
	private $email = '';
	private $zip = '';
	private $ip_addr = '';
	private $sender = null;
	public $confirmation = null;

	public function __construct( &$sender = null, $name = null, $email = null ) {
		if ( empty( $sender ) ) {
			return false;
		}

		if ( empty( $name ) && empty( $email ) ) {
			return false;	# error, if friend details aren't input
		}

		$this->load( $email );
		$this->sender = $sender;
		$this->name = $name;
		$this->email = $email;
		$this->ip_addr = get_ip();
		if ( empty( $this->confirmation ) ) {
			$this->confirmation = new Confirmation( $this );
		}

		return $this->validate();
	}

	public function __get( $name ) {
		return $this->$name;
	}

	public function __set( $name, $value ) {
		return $this->$name;
	}

	public function validate() {
		if ( empty( $this->name ) && empty( $this->email ) ) {
			return false;	# error, if friend details aren't input
		}

		validate_email( $this->email );

		return true;
	}

	public function load( $email, &$sender = null ) {
		if ( empty( $email ) ) {
			return false;
		}

		# Get user data
		$db = Database::init();
		$query = $db->prepare( 'SELECT `id`, `name`, `email`, `zip` FROM #_users WHERE `email` = :email' );
		$query->execute( array( 'email' => $email ) );
		if ( ( $user = $query->fetchObject() ) === false ) {
			return false;
		}
		$this->id = $user->id;
		$this->name = $user->name;
		$this->email = $user->email;
		$this->zip = $user->zip;
		$query->closeCursor();

		if ( empty( $sender ) == false ) {
			$this->sender = $sender;
		}
		else {
			# Get sender
			$query = $db->prepare( 'SELECT `email` FROM #_users AS u JOIN #_invites AS i ON u.`id` = i.`sender_id` WHERE i.`friend_id` = :id' );
			$query->execute( array( 'id' => $this->id ) );
			if ( ( $email = $query->fetchColumn() ) === false ) {
				return false;
			}
			$sender = new Sender();
			if ( $sender->load( $email ) == false ) {
				return false;
			}
			$this->sender = $sender;
		}

		# Load confirmation details, if any
		$confirmation = new Confirmation();
		if ( $confirmation->load( $this ) !== false ) {
			$this->confirmation = $confirmation;
		}

		return true;
	}

	public function save() {
		if ( $this->validate() == false ) {
			return false;
		}

		if ( empty( $this->sender ) ) {
			return false;
		}

		if ( empty( $this->confirmation ) ) {
			return false;
		}

		if ( empty( $this->id ) == false ) {
			return true;
		}

		if ( $this->load( $this->email ) == false ) {
			$db = Database::init();
			$query = $db->prepare( 'INSERT INTO #_users ( `name`, `email`, `ctime`, `ip_addr` ) VALUES ( :name, :email, NOW(), :ip )' );
			$query->execute( array( 'name' => $this->name, 'email' => $this->email, 'ip' => $this->ip_addr ) );
			$this->id = intval( $db->lastInsertId() );
			if ( empty( $this->id ) ) {
				throw new FriendSaveException();
			}
			$query->closeCursor();

			if ( $this->confirmation->save() == false ) {
				throw new FriendSaveException();
			}
		}

		return true;
	}
};