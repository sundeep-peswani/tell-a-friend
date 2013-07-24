<?php defined( 'VALID_INCLUSION' ) or die();

require_once './system/lib/validator.inc.php';
require_once './system/lib/classes/class.friend.php';
require_once './system/lib/classes/class.downloader.php';

class Sender {
	private $id = 0;
	private $name = '';
	private $email = '';
	private $zip = 0;
	private $ip_addr = '';
	private $batch = 0;
	private $subscribed = false;
	private $dashboard_code = '';
	public $friends = null;
	public $downloader = null;

	public function __construct( $name = null, $email = null, $zip = null, $subscribed = false ) {
		if ( empty( $name ) && empty( $email ) && empty( $zip ) ) {
			return true;	# creating a blank Sender
		}

		$this->load( $email );
		$this->name = $name;
		$this->email = $email;
		$this->zip = $zip;
		$this->ip_addr = get_ip();
		$this->subscribed = $subscribed;
		$this->batch = 0;
		if ( empty( $this->dashboard_code ) ) {
			$this->dashboard_code = $this->code();
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
		if ( empty( $this->name ) || empty( $this->email ) || empty( $this->zip ) ) {
			return false;
		}

		if ( is_array( $this->friends ) && count( $this->friends ) < NUMBER_OF_FRIENDS ) {
			throw new InvalidEntryException( 'Please fill in the details of ' . NUMBER_OF_FRIENDS . ' friends.' );
		}

		try {
			validate_email( $this->email );
			validate_zip( $this->zip );
		} catch ( InvalidEmailException $e ) {
			if ( $e->getCode() !== InvalidEmailException::USED ) {
				 throw $e;
			}
		}

		return true;
	}

	public function load( $email ) {
		if ( empty( $email ) ) {
			throw new InvalidEmailException();
		}

		# Get user data
		$db = Database::init();
		$query = $db->prepare( 'SELECT `id`, `name`, `email`, `zip`, `dashboard_code`, `subscribed` FROM #_users WHERE `email` = :email' );
		if ( $query->execute( array( 'email' => $email ) ) == false ) {
			return false;
		}
		if ( ( $user = $query->fetchObject() ) === false ) {
			return false;
		}
		$this->id = $user->id;
		$this->name = $user->name;
		$this->email = $user->email;
		$this->zip = $user->zip;
		$this->dashboard_code = $user->dashboard_code;
		$this->subscribed = $user->subscribed;
		$query->closeCursor();

		# Get new batch ID
		$query = $db->prepare( 'SELECT MAX( `batch_id` ) FROM #_invites WHERE `sender_id` = :sender' );
		$query->execute( array( 'sender' => $this->id ) );
		if ( ( $this->batch = $query->fetchColumn() ) === false ) {
			return false;
		}
		$query->closeCursor();

		# Get user's friends
		$query = $db->prepare( 'SELECT `email` FROM #_users AS u JOIN #_invites AS i ON u.`id` = i.`friend_id` WHERE i.`sender_id` = :sender' );
		if ( $query->execute( array( 'sender' => $this->id ) ) != false ) {
			$this->friends = array();
			while ( ( $email = $query->fetchColumn() ) !== false ) {
				$friend = new Friend();
				if ( $friend->load( $email, $this ) ) {
					array_push( $this->friends, $friend );
				}
			}
		}
		$query->closeCursor();

		# Load download details, if any
		$downloader = new Downloader();
		if ( $downloader->load( $this ) !== false ) {
			$this->downloader = $downloader;
		}

		return true;
	}

	public function save() {
		if ( $this->validate() == false ) {
			return false;
		}

		$db = Database::init();
		$query = $db->prepare(
			'INSERT INTO #_users ( `name`, `email`, `zip`, `ctime`, `dashboard_code`, `subscribed`, `ip_addr` )
			VALUES ( :name, :email, :zip, NOW(), :dashboard, :subscribed, :ip )
			ON DUPLICATE KEY UPDATE `name` = :name, `zip` = :zip, `subscribed` = :subscribed, `ip_addr` = :ip, `dashboard_code` = :dashboard'
		);
		$query->execute(
			array( 'name' => $this->name, 'email' => $this->email, 'zip' => $this->zip,
				'subscribed' => intval( $this->subscribed ), 'ip' => $this->ip_addr,
				'dashboard' => $this->dashboard_code )
		);
		if ( empty( $this->id ) ) {
			$this->id = intval( $db->lastInsertId() );
			if ( empty( $this->id ) ) {
				throw new SenderSaveException();
			}
		}
		$query->closeCursor();

		# update batch for saving
		$this->batch = $this->batch + 1;
		foreach( $this->friends as $friend ) {
			# prevent null friends from being saved
			if ( $friend->save( $this ) == false ) {
				return false;
			}
		}

		# save download data
		if ( empty( $this->downloader ) == false ) {
			$this->downloader->save();
		}

		return true;
	}

	private function code() {
		$salt = uniqid( rand(), true );
		$code = md5( $salt . $this->name . $salt . $this->email . $salt . date( 'YmdHis', time() ) . $salt );

		$db = Database::init();
		$query = $db->prepare( 'SELECT * FROM #_users WHERE `dashboard_code` = :code' );
		if ( $query->execute( array( 'code' => $code ) ) && $query->rowCount() > 0 ) {
			return $this->code();
		}

		return $code;
	}

	public function add( $name, $email ) {
		if ( is_array( $this->friends ) == false ) {
			$this->friends = array();
		}

		$friend = new Friend( $this, $name, $email );
		if ( $friend->validate() == false ) {
			return false;
		}

		array_push( $this->friends, $friend );
		return true;
	}

	public function invite( $message = '' ) {
		if ( is_array( $this->friends ) == false ) {
			return false;
		}

		if ( count( $this->friends ) < NUMBER_OF_FRIENDS ) {
			throw new Exception( 'Please fill in the details of ' . NUMBER_OF_FRIENDS . ' friends.' );
		}

		foreach( $this->friends as $friend ) {
			if ( isset( $friend->confirmation->id  ) ) {
				continue;
			}
			if ( $friend->confirmation->save() == false ) {
				throw new SystemErrorException();
			}
			if ( $friend->confirmation->send( $message ) == false ) {
				throw new MailException( 'Could not send out emails. Please try again.' );
			}
		}

		return true;
	}
};