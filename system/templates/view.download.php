<?php defined( 'VALID_INCLUSION' ) or die(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en-US" xml:lang="en-US" xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Download</title>
<style type="text/css">
#wrapper {
	margin: 0 auto;
	width: 800px;
}
#error {
	background: #ffa6c9;
	border: 1px solid #f00;
	padding: 3px;
	margin-bottom: 10px;
	width: 100%;
}
#error h2 {
	color: #f00;
	font-size: 1.05em;
	margin: 0;
}
label {
	clear: left;
	float: left;
	margin: 5px 0;
	padding-right: 5px;
	width: 150px;
}
label + input {
	margin: 5px 0;
}
</style>
</head>
<body>
<div id="wrapper">
<?php if ( empty( $this->error ) == false ): ?>
<div id="error">
<h2>Error</h2>
<?php echo $this->error; ?>
</div>
<?php endif; ?>
<form method="post" action="">
<label>Download code:</label><input type="text" size="40" name="code" value="<?php display_post( 'code', null, $_GET[ 'code' ] ); ?>" maxlength="32" /><br/>
<label>Email:</label><input type="text" size="40" name="email" value="<?php display_post( 'email' ); ?>" maxlength="50" /><br/>
<?php display_captcha( $this->exception ); ?>
<div id="submit">
<input type="submit" value="Download!" />
</div>
</form>
</div>
</body>
</html>