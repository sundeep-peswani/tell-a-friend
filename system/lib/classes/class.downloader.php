<?php defined( 'VALID_INCLUSION' ) or die();

require_once './system/lib/classes/class.mailer.php';

class Downloader {
	const DOWNLOAD_PATH = '';

	private $id = 0;
	private $sender = null;
	private $salt = '';
	private $code = '';
	private $email = '';

	public function __construct( &$sender = null ) {
		if ( empty( $sender ) ) {
			return false;
		}

		$this->sender = $sender;
		$this->create();
	}

	private function create() {
		if ( empty( $this->sender ) ) {
			return false;
		}

		$this->salt = '';
		$this->code = '';

		$salt = uniqid( rand(), true );
		$code = md5( $salt . $this->sender->id . $salt . date( 'YmdHis', time() ) . $salt . $this->sender->name . $salt . $this->sender->email . $salt );

		$db = Database::init();
		$query = $db->prepare( 'SELECT * FROM #_downloads WHERE `download_salt` = :salt OR `download_code` = :code' );
		$query->execute( array( 'salt' => $salt, 'code' => $code ) );
		if ( $query->rowCount() > 0 ) {
			return $this->create();
		}

		$this->link = $this->link();
		$this->salt = $salt;
		$this->code = $code;
		$this->full = $this->link();
	}

	public function load( &$sender ) {
		if ( empty( $sender ) ) {
			return false;
		}

		$db = Database::init();
		$query = $db->prepare(
			'SELECT `id`, `download_code` AS code, `download_salt` AS salt, `accessed`, UNIX_TIMESTAMP( `ctime` ) AS ctime
			FROM #_downloads WHERE `uid` = :id'
		);
		$query->execute( array( 'id' => $sender->id ) );
		if ( ( $download = $query->fetchObject() ) === false ) {
			return false;
		}

		$this->sender = $sender;
		$this->id = $download->id;
		$this->link = $this->link();
		$this->code = $download->code;
		$this->salt = $download->salt;
		$this->full = $this->link();
		$this->accessed = intval( $download->accessed );
		$this->ctime = intval( $download->ctime );

		$query->closeCursor();

		return true;
	}

	public function save() {
		if ( empty( $this->sender ) || empty( $this->salt ) || empty( $this->code ) ) {
			return false;
		}

		$db = Database::init();
		$query = $db->prepare(
			'INSERT INTO #_downloads ( `uid`, `ctime`, `download_salt`, `download_code`, `ip_addr` )
			VALUES ( :id, NOW(), :salt, :code, :ip )'
		);
		if ( $query->execute( array( 'id' => $this->sender->id, 'salt' => $this->salt, 'code' => $this->code, 'ip' => $this->sender->ip_addr ) ) == false ) {
			return false;
		}

		$query = $db->prepare( 'SELECT `id` FROM #_downloads WHERE `download_code` = :code AND `download_salt` = :salt' );
		if ( $query->execute( array( 'salt' => $this->salt, 'code' => $this->code ) ) == false || $query->rowCount() == 0 ) {
			return false;
		}

		$this->id = intval( $query->fetchColumn() );
		return true;
	}

	# Check if already validated, can use without re-validation
	public static function check() {
		if ( empty( $_GET ) || isset( $_GET[ 'code' ] ) == false ) {
			return false;
		}

		$db = Database::init();
		$query = $db->prepare( 'SELECT `accessed`, UNIX_TIMESTAMP( `ctime` ) AS ctime FROM #_downloads WHERE `download_code` = :code' );
		$query->execute( array( 'code' => $_GET[ 'code' ] ) );
		if ( ( $status = $query->fetch( PDO::FETCH_ASSOC ) ) === false ) {
			return false;
		}
		$query->closeCursor();

		if ( MAX_NUM_DOWNLOADS > 0 && intval( $status[ 'accessed' ] ) > MAX_NUM_DOWNLOADS ) {
			throw new DownloaderSlotException();
		}

		if ( MAX_DOWNLOAD_WINDOW > 0 && ( time() - intval( $status[ 'ctime' ] ) ) >= MAX_DOWNLOAD_WINDOW ) {
			throw new DownloadWindowExpiryException();
		}

		return Downloader::download();
	}

	# Check code, email against database
	public function validate( $code ) {
		if ( empty( $this->sender ) || empty( $this->salt ) || empty( $this->code ) ) {
			return false;
		}

		$code = preg_replace( '/[^0-9a-f]/', '', strtolower( $code ) );

		if ( strlen( $code ) != 32 ) {
			throw new DownloaderException( 'Please enter a valid download code' );
		}

		if ( strtolower( $this->code ) !== $code ) {
			throw new DownloaderException( 'Invalid download code.' );
		}

		if ( MAX_NUM_DOWNLOADS > 0 && intval( $this->accessed ) > MAX_NUM_DOWNLOADS ) {
			throw new DownloaderSlotException();
		}

		if ( MAX_DOWNLOAD_WINDOW > 0 && time() - intval( $this->ctime ) >= MAX_DOWNLOAD_WINDOW ) {
			throw new DownloadWindowExpiryException();
		}

		$db = Database::init();
		$query = $db->prepare( 'UPDATE #_downloads SET `accessed` = `accessed` + 1, `atime` = NOW() WHERE `uid` = :id' );
		if ( $query->execute( array( 'id' => $this->sender->id ) ) == false ) {
			return false;
		}

		return Downloader::download();
	}

	public function send() {
		if ( empty( $this->sender ) || empty( $this->salt ) || empty( $this->code ) ) {
			return false;
		}

		$mailer = new Mailer();

		# Load subject
		$mailer->Subject = 'Download email';

		# Load address
		$mailer->AddAddress( $this->sender->email, $this->sender->name );

		# Load body
		$db = Database::init();
		$query = $db->prepare( 'SELECT `message` FROM #_emails WHERE `type` = :type AND `receipient` = :receipient' );
		if ( $query->execute( array( 'type' => 'download', 'receipient' => $this->sender->email ) ) !== false ) {
			$this->email = $query->fetchColumn();
			$mailer->MsgHTML( $this->email );
		}
		$query->closeCursor();

		if ( empty( $this->email ) ) {
			ob_start();
			require './system/templates/email.download.php';
			$this->email = ob_get_contents();
			ob_end_clean();

			# Template body
			$body = str_replace(
				array(
					'{subject}',
					'{sender.name}',
					'{sender.email}',
					'{download.link}',
					'{download.code}',
					'{download.fullLink}'
				),
				array(
					$mailer->Subject,
					$this->sender->name,
					$this->sender->email,
					$this->link,
					$this->code,
					$this->full
				),
				$this->email
			);

			$mailer->MsgHTML( $body );
		}

		# Send
		return $mailer->Send( $this->id, $this->sender->email, 'download' );
	}

	private function link() {
		return site_link( array( 'view' => 'download', 'code' => $this->code ) );
	}

	# Handles the download
	public function download() {
		if ( strlen( self::DOWNLOAD_PATH ) == 0 ) {
			return true;
		}

		$file = file_get_contents( DOWNLOAD_PATH );
		if ( strlen( $file ) == 0 ) {
			return true;
		}

		header( 'Content-Type: application/force-download' );
		header( 'Content-Length: ' . ( string )( strlen( $file ) ) );
		header( 'Content-Disposition: attachment; filename="' . basename( DOWNLOAD_PATH ) . '"' );
		header( "Content-Transfer-Encoding: binary\n" );
		echo $file;
		exit();

		return true;
	}
};