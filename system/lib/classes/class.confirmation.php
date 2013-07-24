<?php defined( 'VALID_INCLUSION' ) or die();

require_once './system/lib/classes/class.mailer.php';

class Confirmation {
	private $id = 0;
	private $friend = null;
	private $salt = '';
	private $code = '';
	private $email = '';
	public $confirmed = false;

	public function __construct( &$friend = null ) {
		if ( empty( $friend ) ) {
			return false;
		}

		$this->friend = $friend;
		$this->create();
	}

	private function create() {
		if ( empty( $this->friend ) ) {
			return false;
		}

		$this->salt = '';
		$this->code = '';

		$salt = uniqid( rand(), true );
		$code = md5( $salt . $this->friend->name . $salt . $this->friend->email . $salt . date( 'YmdHis', time() ) . $salt );

		$db = Database::init();
		$query = $db->prepare( 'SELECT * FROM #_invites WHERE `confirmation_salt` = :salt OR `confirmation_code` = :code' );
		if ( $query->execute( array( 'salt' => $salt, 'code' => $code ) ) && $query->rowCount() > 0 ) {
			return $this->create();
		}

		$this->link = $this->link();
		$this->salt = $salt;
		$this->code = $code;
		$this->full = $this->link();
	}

	public function load( &$friend ) {
		if ( empty( $friend ) ) {
			return false;
		}

		$db = Database::init();
		$query = $db->prepare(
			'SELECT `id`, `sender_id` AS sender, `confirmation_code` AS code, `confirmation_salt` AS salt, `confirmed`
			FROM #_invites WHERE `friend_id` = :id'
		);
		$query->execute( array( 'id' => $friend->id ) );
		if ( ( $confirmation = $query->fetchObject() ) === false ) {
			return false;
		}

		$this->friend = $friend;

		$this->id = $confirmation->id;
		$this->link = $this->link();
		$this->code = $confirmation->code;
		$this->salt = $confirmation->salt;
		$this->full = $this->link();
		$this->confirmed = $confirmation->confirmed == 1;

		$query->closeCursor();

		return true;
	}

	public function save() {
		if ( empty( $this->friend ) || empty( $this->salt ) || empty( $this->code ) ) {
			return false;
		}

		$db = Database::init();
		$query = $db->prepare( '
			INSERT INTO #_invites
			( `sender_id`, `batch_id`, `friend_id`, `invitation_date`, `confirmed`, `confirmation_code`, `confirmation_salt` )
			VALUES ( :sender, :batch, :friend, NOW(), 0, :code, :salt )
			ON DUPLICATE KEY UPDATE `sender_id` = :sender, `batch_id` = :batch, `friend_id` = :friend,
			`confirmation_code` = :code, `confirmation_salt` = :salt
		');
		if ( $query->execute(
			array(
				'sender' => $this->friend->sender->id,
				'batch' => $this->friend->sender->batch,
				'friend' => $this->friend->id,
			 	'salt' => $this->salt,
				'code' => $this->code
			)
		) == false ) {
			throw new SystemErrorException();
		}
		$query->closeCursor();

		$query = $db->prepare( 'SELECT `id` FROM #_invites WHERE `confirmation_code` = :code' );
		if ( $query->execute( array( 'code' => $this->code ) ) == false || $query->rowCount() == 0 ) {
			return false;
		}
		$this->id = intval( $query->fetchColumn() );
		$query->closeCursor();

		return true;
	}

	public function validate( $code ) {
		if ( empty( $this->friend ) || empty( $this->salt ) || empty( $this->code ) ) {
			return false;
		}

		if ( $this->confirmed ) {
			throw new ConfirmationException();
		}

		$code = preg_replace( '/[^0-9a-f]/', '', strtolower( $code ) );

		if ( strlen( $code ) != 32 ) {
			throw new InvalidConfirmationException( 'Please enter a valid confirmation code.' );
		}

		if ( strtolower( $this->code ) !== $code ) {
			throw new InvalidConfirmationException();
		}

		$db = Database::init();
		$query = $db->prepare( 'UPDATE #_invites SET `confirmed` = "1", `confirmation_date` = NOW() WHERE `friend_id` = :id' );
		if ( $query->execute( array( 'id' => $this->friend->id ) ) == false ) {
			return false;
		}

		if ( $this->quotaPassed() ) {
			$downloader = new Downloader( $this->friend->sender );
			$this->friend->sender->downloader = $downloader;
			$this->friend->sender->downloader->save();
			$this->friend->sender->downloader->send();
		}

		return true;
	}

	# Check to see if 10 friends have confirmed
	private function quotaPassed() {
		if ( empty( $this->friend ) ) {
			return false;
		}

		if ( empty( $this->friend->sender->downloader ) == false ) {
			return false;
		}

		$db = Database::init();
		$query = $db->prepare( 'SELECT COUNT( * ) FROM #_invites WHERE `sender_id` = :id AND `confirmed` = 1');
		$query->execute( array( 'id' => $this->friend->sender->id ) );
		if ( ( $count = $query->fetchColumn() ) == false ) {
			return false;
		}

		return ( intval( $count ) >= NUMBER_OF_FRIENDS );
	}

	public function send( $message = '' ) {
		if ( empty( $this->friend ) || empty( $this->salt ) || empty( $this->code ) ) {
			return false;
		}

		$mailer = new Mailer();

		# Load subject
		$mailer->Subject = 'Invitation email';

		# Load address
		$mailer->AddAddress( $this->friend->email, $this->friend->name );

		# Load body
		$db = Database::init();
		$query = $db->prepare( 'SELECT `message` FROM #_emails WHERE `type` = :type AND `receipient` = :receipient' );
		if ( $query->execute( array( 'type' => 'confirmation', 'receipient' => $this->friend->email ) ) !== false && $query->rowCount() > 0 ) {
			$this->email = $query->fetchColumn();
			$mailer->MsgHTML( $this->email );
		}

		$query->closeCursor();
		if ( empty( $this->email ) ) {
			ob_start();
			require './system/templates/email.confirmation.php';
			$this->email = ob_get_contents();
			ob_end_clean();

			# Template body
			$body = str_replace(
				array(
					'{subject}',
					'{sender.name}',
					'{sender.email}',
					'{friend.name}',
					'{friend.email}',
					'{confirmation.link}',
					'{confirmation.code}',
					'{confirmation.fullLink}',
					'{personal.message}'
				),
				array(
					$mailer->Subject,
					$this->friend->sender->name,
					$this->friend->sender->email,
					$this->friend->name,
					$this->friend->email,
					$this->link,
					$this->code,
					$this->full,
					$message
				),
				$this->email
			);

			$mailer->MsgHTML( $body );
		}

		if ( empty( $this->email ) ) {
			throw new MailException( 'Sorry, the emails could not be sent.' );
		}

		# Send
		return $mailer->Send( $this->id, $this->friend->email, 'confirmation' );
	}

	private function link() {
		return site_link( array( 'view' => 'confirm', 'code' => $this->code ) );
	}
};