<?php defined( 'VALID_INCLUSION' ) or die();

require './system/lib/phpmailer/class.phpmailer.php';

class Mailer extends PHPMailer {
	function __construct() {
		if ( strlen( MAIL_FROM_EMAIL ) == 0 ) {
			throw new MailException();
		}

		$this->From = MAIL_FROM_EMAIL;
		$this->FromName = MAIL_FROM_NAME;
		$this->Host = MAIL_HOST;
		$this->Mailer = MAIL_SYSTEM;
		$this->WordWrap = intval( MAIL_WORDWRAP );
	}

	function error_handler( $message ) {
		throw new MailException( $message );
	}

	function Send( $id, $to, $type = 'other' ) {
		if ( parent::Send() == false ) {
			throw new MailSendException();
		}

		$db = Database::init();
		$query = $db->prepare( '
			INSERT INTO #_emails ( `ext_id`, `sender`, `receipient`, `subject`, `message`, `type`, `stime`, `sent` )
			VALUES ( :extid, :sender, :receipient, :subject, :message, :type, NOW(), :sent )
			ON DUPLICATE KEY UPDATE `stime` = NOW(), `sent` = `sent` + :sent
		');

		return $query->execute( array(
			'extid' => $id,
			'sender' => $this->From,
			'receipient' => $to,
			'subject' => $this->Subject,
			'message' => $this->Body,
			'type' => $type,
			'sent' => intval( $sent )
		) );
	}
};