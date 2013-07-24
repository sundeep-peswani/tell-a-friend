<?php defined( 'VALID_INCLUSION' ) or die();

class ConfirmationException extends Exception {
	function __construct( $message = 'You have already been confirmed.' ) {
		parent::__construct( $message );
	}
};

class DatabaseException extends Exception {
	function __construct( $message = 'Database error.' ) {
		parent::__construct( $message );
	}
};

class DownloaderException extends Exception {
};

class DownloaderSlotException extends Exception {
	function __construct( $message = 'You have exhausted your download slots.' ) {
		parent::__construct( $message );
	}
};

class DownloadWindowExpiryException extends Exception {
	function __construct( $message = 'Your download window has expired.' ) {
		parent::__construct( $message );
	}
};

class FriendSaveException extends Exception {
	function __construct( $message = 'Could not save your friend\'s details.', $code = 0 ) {
		parent::__construct( $message, $code );
	}
};

class InvalidCaptchaException extends Exception {
	function __construct( $recaptcha_error, $message = "Error with CAPTCHA test.", $code = 0 ) {
		parent::__construct( $message, $code );
		$this->error = $recaptcha_error;
	}
};

class InvalidConfirmationException extends Exception {
	function __construct( $message = 'Invalid confirmation code.' ) {
		parent::__construct( $message );
	}
};

class InvalidEmailException extends Exception {
	const USED = 1;

	function __construct( $message = "Please enter a valid email.", $code = 0, $email = '' ) {
		if ( empty( $email ) == false ) {
			$message = "The email you have entered, $email, is invalid. $message";
		}

		parent::__construct( $message, $code );
	}
};

class InvalidEntryException extends Exception {
};

class InvalidZipException extends Exception {
	function __construct( $message = "Please enter a valid zip code.", $code = 0, $zip = '' ) {
		if ( empty( $email ) == false ) {
			$message = "The zip code you have entered, $zip, is invalid. $message";
		}

		parent::__construct( $message, $code );
	}
};

class MailException extends Exception {
	function __construct( $message = '' ) {
		parent::__construct( $message );
	}
};

class MailSendException extends Exception {
	function __construct( $message =  'Could not send email. Please try again later.' ) {
		parent::__construct( $message );
	}
}

class SenderSaveException extends Exception {
	function __construct( $message = 'Could not save your details.', $code = 0 ) {
		parent::__construct( $message, $code );
	}
};

class SystemErrorException extends Exception {
	function __construct() {
		parent::__construct( 'System error.' );
	}
};

class UserLoadErrorException extends Exception {
	function __construct( $user = '', $message = 'Could not load user.', $code = 0 ) {
		if ( empty( $user ) == false ) {
			$message = "Could not load user: $user.";
		}
		parent::__construct( $message, $code );
	}
};

class UserNotFoundException extends Exception {
	function __construct( $user = '', $message = 'User not found.', $code = 0 ) {
		if ( empty( $user ) == false ) {
			$message = "User, $user, not found.";
		}
		parent::_construct( $message, $code );
	}
};
