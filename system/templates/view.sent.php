<?php defined( 'VALID_INCLUSION' ) or die(); ?>
<?php $dashboard_link = site_link( array( 'view' => 'dashboard', 'code' => $sender->dashboard_code ) ); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en-US" xml:lang="en-US" xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Thanks!</title>
<style type="text/css">
#wrapper {
	margin: 0 auto;
	width: 800px;
}
</style>
</head>
<body>
<div id="wrapper">
<p>Thanks for inviting your friends!</p>
<p>You can keep track of your friends via your own dashboard: <a href="<?php echo $dashboard_link; ?>" title="Your dashboard"><?php echo $dashboard_link; ?></a></p>
<p>Please save it for future reference.</p>
</div>
</body>
</html>