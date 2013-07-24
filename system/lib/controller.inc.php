<?php define( 'VALID_INCLUSION', true );

require_once './system/lib/display.inc.php';

class Controller {
	public $error = '';
	public $exception = null;
	public $view = '';

	function __construct() {
		switch( $_GET[ 'view' ] )  {
			case 'admin':
			case 'check':
			case 'confirm':
			case 'dashboard':
			case 'download':
				$this->view = strtolower( $_GET[ 'view' ] );
				break;
			default:
				$this->view = 'sender';
				break;
		}
	}

	function process() {
		try {
			require_once './system/lib/classes/class.sender.php';

			switch( $this->view ) {
				case 'admin':
					require_once './system/lib/classes/class.admin.php';
					$admin = $this->admin();
					break;
				case 'check':
					$this->check();
					break;
				case 'dashboard':
					$user = $this->dashboard();
					break;
				case 'download':
					require_once './system/lib/recaptcha/recaptchalib.php';
					$this->download();
					break;
				case 'confirm':
					require_once './system/lib/recaptcha/recaptchalib.php';
					require_once './system/lib/classes/class.friend.php';
					if ( $this->confirm() ) {
						$this->view = 'confirmed';
					}
					break;
				case 'sender':
					require_once './system/lib/recaptcha/recaptchalib.php';
					$sender = false;
					if ( ( $sender = $this->sender() ) !== false ) {
						$this->view = 'sent';
					}
					break;
				default:
					exit();
			}
		} catch ( PDOException $pde ) {
			$this->error = 'There was an error with the database. Please try again later.';
			$this->exception = $pde;
		} catch ( Exception $e ) {
			$this->error = $e->getMessage();
			$this->exception = $e;
		}

		require_once './system/templates/view.' . $this->view . '.php';
	}

	function admin() {
		$admin = new Admininstration();

		if ( post_exists( 'email', 'string' ) ) {
			$admin->search( $_POST[ 'email' ] );
		}
		else if ( isset( $_GET[ 'user' ] ) && intval( $_GET[ 'user' ] ) > 0 ) {
			$admin->user( intval( $_GET[ 'user' ] ) );
		}

		return $admin;
	}

	function check() {
		$data = new stdClass();
		$data->valid = false;

		if ( empty( $_POST ) == false ) {
			if ( post_exists( 'email', 'string' ) ) {
				try {
					$data->valid = validate_email( $_POST[ 'email' ] );
				} catch ( Exception $e ) {
					$data->valid = false;
				}
			}
		}

		echo json_encode( $data );
		exit;
	}

	function confirm() {
		if ( empty( $_POST ) ) {
			return false;
		}

		if ( post_exists( 'code', 'string' ) == false ) {
			throw new InvalidEntryException( 'Please enter the confirmation code.' );
		}

		if ( post_exists( 'email', 'string' ) == false ) {
			throw new InvalidEntryException( 'Please enter your email address.' );
		}

		if ( ENABLE_CAPTCHA ) {
			if ( post_exists( 'recaptcha_challenge_field', 'string' ) == false || post_exists( 'recaptcha_response_field', 'string' ) == false ) {
				throw new InvalidEntryException( 'Please fill in the CAPTCHA check.' );
			}
			validate_captcha( $_POST[ 'recaptcha_challenge_field' ], $_POST[ 'recaptcha_response_field' ] );
		}

		$friend = new Friend();
		if ( $friend->load( $_POST[ 'email' ] ) === false ) {
			throw new SystemErrorException( 'Could not load your details. Please try again later.' );
		}

		return $friend->confirmation->validate( $_POST[ 'code' ] );
	}

	function dashboard() {
		if ( isset( $_GET[ 'user' ] ) == false ) {
			header( 'Location: ' . SITE_URL );
			exit;
		}

		$user = preg_replace( '/[^a-f0-9]/', '', strtolower( $_GET[ 'user' ] ) );

		if ( strlen( $user ) != 32 ) {
			header( 'Location: ' . SITE_URL );
			exit;
		}

		$db = Database::init();
		$query = $db->prepare( 'SELECT `email` FROM #_users WHERE `dashboard_code` = :code' );
		if ( $query->execute( array( 'code' => $_GET[ 'user' ] ) ) == false || $query->rowCount() == 0 ) {
			header( 'Location: ' . SITE_URL );
			exit;
		}
		$email = $query->fetchColumn();
		$query->closeCursor();

		$sender = new Sender();
		$sender->load( $email );

		if ( post_exists( 'friend', 'integer' ) ) {
			$fid = intval( $_POST[ 'friend' ] );
			foreach( $sender->friends as $friend ) {
				if ( $friend->id == $fid ) {
					$friend->confirmation->send();
				}
			}
		}

		return $sender;
	}

	function download() {
		if ( empty( $_POST ) ) {
			return Downloader::check();
		}

		if ( post_exists( 'code', 'string' ) == false ) {
			throw new InvalidEntryException( 'Please enter the download code.' );
		}

		if ( post_exists( 'email', 'string' ) == false ) {
			throw new InvalidEntryException( 'Please enter your email address.' );
		}

		if ( ENABLE_CAPTCHA ) {
			if ( post_exists( 'recaptcha_challenge_field', 'string' ) == false || post_exists( 'recaptcha_response_field', 'string' ) == false ) {
				throw new InvalidEntryException( 'Please fill in the CAPTCHA check.' );
			}
			validate_captcha( $_POST[ 'recaptcha_challenge_field' ], $_POST[ 'recaptcha_response_field' ] );
		}

		$sender = new Sender();
		if ( $sender->load( $_POST[ 'email' ] ) === false ) {
			throw new SystemErrorException( 'Could not load your details. Please try again later.' );
		}

		if ( $sender->downloader->validate( $_POST[ 'code' ] ) ) {
			return Downloader::download();
		}

		return true;
	}

	function sender() {
		if ( empty( $_POST ) ) {
			return false;
		}

		if ( post_exists( 'sender', 'array', array( 'name', 'email', 'zip' ) ) == false ) {
			throw new InvalidEntryException( 'Please fill in your information.' );
		}

		if ( post_exists( 'friends', 'array' ) == false ) {
			throw new InvalidEntryException( 'Please fill in your friends\' information.' );
		}

		if ( ENABLE_CAPTCHA ) {
			if ( post_exists( 'recaptcha_challenge_field', 'string' ) == false || post_exists( 'recaptcha_response_field', 'string' ) == false ) {
				throw new InvalidEntryException( 'Please fill in the CAPTCHA check.' );
			}
			validate_captcha( $_POST[ 'recaptcha_challenge_field' ], $_POST[ 'recaptcha_response_field' ] );
		}

		$sender = $_POST[ 'sender' ];
		$friends = $_POST[ 'friends' ];

		$sender = new Sender( $sender[ 'name' ], $sender[ 'email' ], $sender[ 'zip' ], isset( $sender[ 'subscribe' ] ) );
		for( $ii = 0; $ii < NUMBER_OF_FRIENDS; $ii++ ) {
			$sender->add( $friends[ 'name' ][ $ii ], $friends[ 'email' ][ $ii ] );
		}

		if ( $sender->validate() == false ) {
			return false;
		}

		if ( $sender->save() == false ) {
			throw new SenderSaveException();
		}

		if ( $sender->invite( $_POST[ 'sender' ][ 'message' ] ) == false ) {
			throw new MailSendException();
		}

		return $sender;
	}
};