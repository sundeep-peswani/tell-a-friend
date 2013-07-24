<?php defined( 'VALID_INCLUSION' ) or die();

function validate_email( $email ) {
	$account = "[a-z0-9!#$%&'*\+\/\=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*\+\/\=?^_`{|}~-]+)*";
	$domain = "(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+";
	$tld = "(?:[A-Z]{2}|com|org|net|gov|mil|biz|info|mobi|name|aero|jobs|museum)";

	if ( preg_match( "/^$account@$domain$tld$/", $email ) == false ) {
		throw new InvalidEmailException();
	}

	$db = Database::init();
	$query = $db->prepare( "SELECT * FROM #_users WHERE `email` = :email" );
	if ( $query->execute( array( 'email' => $email ) ) && $query->rowCount() > 0 ) {
		throw new InvalidEmailException( 'This email has already been used', InvalidEmailException::USED );
	}

	return true;
}

function validate_zip( $zip ) {
	$db = Database::init();

	$query = $db->prepare( "SELECT * FROM #_zips WHERE `zip` = :zip" );
	if ( $query->execute( array( 'zip' => $zip ) ) == false ) {
		throw new InvalidZipException();
	}

	return true;
}

function validate_captcha( $challenge, $response ) {
	if ( function_exists( 'recaptcha_check_answer' ) == false ) {
		return false;
	}

	if ( strlen( RECAPTCHA_PRIVATE_KEY ) == 0 ) {
		return false;
	}

	$response = recaptcha_check_answer( RECAPTCHA_PRIVATE_KEY, $_SERVER[ 'REMOTE_ADDR' ], $challenge, $response );

	if ( $response->is_valid == false ) {
		throw new InvalidCaptchaException( $response->error );
	}

	return true;
}