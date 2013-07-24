<?php defined( 'VALID_INCLUSION' ) or die();

function display_captcha( $exception = '' ) {
	if ( ENABLE_CAPTCHA == false ) {
		return true;
	}

	if ( is_a( $exception, 'Exception' ) ) {
		$exception = $exception->error;
	}

	echo recaptcha_get_html( RECAPTCHA_PUBLIC_KEY, $exception );
}

function display_post( $key, $display = null, $default = '' ) {
	$keys = explode( '.', $key );
	$post = $_POST;
	foreach( $keys as $k ) {
		if ( isset( $post[ $k ] ) == false ) {
			echo $default;
			return false;
		}

		$post = $post[ $k ];
	}
	echo ( empty( $display ) ? $post : $display );
}

function display_mark( $bool ) {
	echo ( $bool ? '&#x2713;' : '&#x2717' );
}