<?php defined( 'VALID_INCLUSION' ) or die();

require_once './configuration.php';
require_once './system/lib/exceptions.inc.php';
require_once './system/lib/classes/class.database.php';

function __( &$object ) {
	echo '<pre>'; print_r( $object ); echo '</pre>';
}

function site_link( $params ) {
	if ( USE_CLEAN_URLS == false ) {
		return SITE_URL . 'index.php' . ( empty( $params ) ? '' : '?' . http_build_query( $array ) );
	}

	$format = '%s' . join( '/', array_fill( 0, count( $params ), '%s' ) );
	$values = array_values( $params );
	array_unshift( $values, SITE_URL );
	return vsprintf( $format, $values );
}

function get_ip() {
	if ( function_exists( 'get_env' ) ) {
		if ( getenv( 'HTTP_CLIENT_IP' ) )
			return getenv( 'HTTP_CLIENT_IP' );
		if ( getenv( 'HTTP_X_FORWARDED_FOR' ) )
			return getenv( 'HTTP_X_FORWARDED_FOR' );
		if ( getenv( 'HTTP_X_FORWARDED' ) )
			return getenv( 'HTTP_X_FORWARDED' );
		if ( getenv( 'HTTP_FORWARDED_FOR' ) )
			return getenv( 'HTTP_FORWARDED_FOR' );
		if ( getenv( 'HTTP_FORWARDED' ) )
			return getenv( 'HTTP_FORWARDED' );
	}
	return $_SERVER['REMOTE_ADDR'];
}

function log_error( $class, $exception ) {
	static $logfile = NULL;
	$logfile = realpath( '.' ) . '/system/logs/error_' . gmdate( 'Ymd' ) . '.log';
	echo $logfile;
	exit;

	$message = $exception;
	if ( is_a( $exception, 'Exception' ) ) {
		$message = $exception->getMessage();
	}

	error_log( "$class: $message\n", 3, $logfile );
}

function post_exists( $key, $type = null, $keys = null ) {
	if ( empty( $_POST ) ) {
		return false;
	}

	if ( empty( $_POST[ $key ] ) ) {
		return false;
	}

	switch( $type ) {
		case 'array':
			if ( is_array( $_POST[ $key ] ) == false ) {
				return false;
			}
			if ( isset( $keys ) ) {
				foreach ( $keys as $k ) {
					if ( empty( $_POST[ $key ][ $k ] ) ) {
						return false;
					}
				}
			}
			return true;
		case 'double':	return is_numeric( $_POST[ $key ] ) && is_double( floatval( $_POST[ $key ] ) );
		case 'integer':	return is_numeric( $_POST[ $key ] );
		case 'string':	return is_string( $_POST[ $key ] );
		default:	return false;
	}
}

function shutdown() {
}

register_shutdown_function( 'shutdown' );