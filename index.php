<?php define( 'VALID_INCLUSION', true );

try {
	require_once './system/bootstrap.inc.php';
	require_once './system/lib/controller.inc.php';

	$controller = new Controller();
	$controller->process();
} catch ( Exception $e ) {
	exit( 'An unknown exception occured' );
}