<?php defined( 'VALID_INCLUSION' ) or die(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en-US" xml:lang="en-US" xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $user->title; ?></title>
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
#profile {
	clear: both;
}
.field {
	clear: left;
	float: left;
	margin: 5px 0;
	padding-right: 5px;
	width: 150px;
}
.value {
	float: left;
	margin: 5px 0;
}
#friends {
	margin-top: 30px;
	width: 100%;
}
#friends th {
	text-align: left;
	width: 150px;
}
.spacer {
	clear: both;
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
<div id="profile">
<h2>Your profile:</h2>
<span class="field">Name:</span><span class="value"><?php echo $user->name; ?></span>
<span class="field">Email:</span><span class="value"><?php echo $user->email; ?></span>
<span class="field">Zip:</span><span class="value"><?php echo $user->zip; ?></span>
<div class="spacer"></div>
</div>
<?php if ( empty( $user->friends ) == false ): ?>
<div id="friends">
<h2>Your friends:</h2>
<table>
<thead>
<tr>
<th>Name</th>
<th>Email</th>
<th>Confirmed</th>
<th>&nbsp;</th>
</tr>
</thead>
<tbody>
<?php foreach( $user->friends as $friend ): ?>
<tr>
<td><?php echo $friend->name; ?></td>
<td><?php echo $friend->email; ?></td>
<td><?php if ( $friend->confirmation->confirmed ): ?>Yes<?php else: ?>No<? endif; ?></td>
<td>
<?php if ( $friend->confirmation->confirmed == false ): ?>
<form method="post">
<input type="hidden" name="friend" value="<?php echo $friend->id; ?>" />
<input type="submit" value="Resend Email" />
</form>
<?php else: ?>
&nbsp;
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</div>
</body>
</html>