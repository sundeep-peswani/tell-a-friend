<?php defined( 'VALID_INCLUSION' ) or die(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en-US" xml:lang="en-US" xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Administration</title>
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
.panel {
	clear: both;
}
.panel p {
	float: left;
}
.panel p + p {
	float: right;
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
table {
	border: 0;
	border-collapse: collapse;
	border-spacing: 0;
}
thead th {
	border-bottom: 1px solid #000;
}
th {
	text-align: left;
	width: 200px;
}
</style>
</head>
<body>
<div id="wrapper">
<h1><a href="<?php echo $admin->link(); ?>" title="Home">Administration</a></h1>
<?php if ( empty( $this->error ) == false ): ?>
<div id="error">
<h2>Error</h2>
<?php echo $this->error; ?>
</div>
<?php endif; ?>
<div class="panel">
<form method="post">
<p><span>Search:</span> <input type="text" name="email" value="<?php display_post( 'email' ); ?>" size="30" /><input type="submit" value="Search" /></p>
<p><span>Total emails sent:</span> <?php echo $admin->totalEmails; ?></p>
</form>
</div>
<?php if ( empty( $admin->user ) == false ): # Display view for user ?>
<div class="panel" id="profile">
<h2>Profile</h2>
<span class="field">Name:</span><span class="value"><?php echo $admin->user->name; ?></span>
<span class="field">Email:</span>
<span class="value">
<a href="mailto:<?php echo $admin->user->email; ?>" "Email <?php echo $admin->user->name; ?>"><?php echo $admin->user->email; ?></a>
</span>
<span class="field">Zip:</span><span class="value"><?php $zip = $admin->user->zip; echo empty( $zip ) ? '-' : $admin->user->zip; ?></span>
<span class="field">Confirmed:</span>
<span class="value"><?php display_mark( is_a( $admin->user, 'Sender' ) || ( isset( $admin->user->confirmation ) && $admin->user->confirmation->confirmed ) ); ?></span>
<span class="field">Subscribed:</span>
<span class="value"><?php display_mark( !( is_a( $admin->user, 'Friend' ) || $admin->user->subscribed == false ) ); ?></span>
<?php if ( is_a( $admin->user, 'Friend' ) ): ?>
<span class="field">Invited by:</span>
<span class="value">
<a href="<?php echo $admin->link( $admin->user->sender->id ); ?>" title="View <?php echo $admin->user->sender->name ?>">
<?php echo $admin->user->sender->name; ?> (<?php echo $admin->user->sender->email; ?>)
</a>
</span>
<?php endif; ?>
</div>
<?php if ( is_a( $admin->user, 'Sender' ) ): ?>
<div class="panel" id="friends">
<h2>Friends</h2>
<?php if ( empty( $admin->user->friends ) ): ?>
<p>-</p>
<?php else: ?>
<table>
<thead>
<tr>
<th>Name</th>
<th>Email</th>
<th>&nbsp;</th>
</tr>
</thead>
<tbody>
<?php foreach( $admin->user->friends as $user ): ?>
<tr>
<td><?php echo $user->name; ?></td>
<td><a href="mailto:<?php echo $user->email; ?>" title="Email <?php echo $user->name; ?>"><?php echo $user->email; ?></a></td>
<td><a href="<?php echo $admin->link( $user->id ); ?>" title="View <?php echo $user->name; ?>">View</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</div>
<?php endif; ?>
<?php elseif ( empty( $admin->users ) == false ): # Display all users ?>
<div class="panel">
<table>
<thead>
<tr>
<th>Name</th>
<th>Email</th>
<th>Confirmed</th>
<th>Subscribed</th>
<th>Type</th>
<th>&nbsp;</th>
</tr>
</thead>
<tbody>
<?php foreach( $admin->users as $user ): ?>
<tr>
<td><?php echo $user->name; ?></td>
<td><a href="mailto:<?php echo $user->email; ?>" title="Email <?php echo $user->name; ?>"><?php echo $user->email; ?></a></td>
<td><?php display_mark( $user->confirmed ); ?></td>
<td><?php display_mark( $user->subscribed ); ?></td>
<td><?php echo ( $user->type == Admininstration::SENDER ? 'Inviter' : 'Invited' ); ?></td>
<td><a href="<?php echo $user->link; ?>" title="View <?php echo $user->name; ?>">View</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>
</body>
</html>