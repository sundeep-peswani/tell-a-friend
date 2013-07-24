<?php defined( 'VALID_INCLUSION' ) or die(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en-US" xml:lang="en-US" xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Email this to your friends</title>
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
.left-column {
	float: left;
	width: 50%;
}
.right-column {
	clear: right;
	float: left;
	width: 50%;
}
#sender label {
	clear: left;
	float: left;
	margin: 5px 0;
	padding-right: 5px;
	width: 50px;
}
.friend span {
	text-align: right;
}
.friend span, #friends label {
	float: left;
	margin: 5px 0;
}
#sender label + input, #friends label + input {
	float: left;
	font-size: 8px;
	margin: 5px 10px;
	width: 110px;
}
.friend span {
	clear: left;
	padding-right: 5px;
	width: 15px;
}
#submit {
	clear: both;
	float: right;
}
#submit input {
	background: 0;
	border: 1px solid #000;
	margin: 20px 0 0;
}
</style>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.2.6/jquery.min.js"></script>
<script type="text/javascript">
checker = {
	url: '<?php echo site_link( array( 'view' => 'check' ) ); ?>',
	field: null,
	reset: function( field ) { $( field ).css( { border: '1px solid #000' } ); },
	success: function( field ) { $( field ).css( { border: '1px solid #3a3' } ); },
	failure: function( field ) { $( field ).css( { border: '1px solid #f00' } ); }
};

$( document ).ready( function() {
	$( '.friend input.email' ).each( function() {
		$( this ).change( function() {
			if ( $( this ).val().length == 0 ) {
				return checker.reset( this );
			}

			checker.field = this;
		    	$.post(
		    		checker.url,
		    		{ 'email': $( this ).val() },
		    		function( data, textStatus ) {
		    			if ( textStatus == 'success' ) {
						if ( data.valid ) {
							return checker.success( checker.field );
						}
						return checker.failure( checker.field );
		    			}

		    			checker.reset( checker.field );
		    		},
		    		'json'
		    	);
		});
	});
});
</script>
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
<div class="left-column">
<fieldset id="sender">
<legend>Your details</legend>
<label>Name:</label><input type="text" size="30" name="sender[name]" value="<?php display_post( 'sender.name' ); ?>" maxlength="50" />
<label>Email:</label><input type="text" size="30" name="sender[email]" value="<?php display_post( 'sender.email' ); ?>" maxlength="50" />
<label>Zip:</label><input type="text" size="30" name="sender[zip]" value="<?php display_post( 'sender.zip' ); ?>" maxlength="5" />
<label>Subscribe:</label><input type="checkbox" name="sender[subscribe]"<?php display_post( 'sender.subscribe', ' checked="checked"' ); ?> />
</fieldset>
<?php display_captcha( $this->exception ); ?>
</div>
<div class="right-column">
<fieldset id="friends">
<legend>Your friends:</legend>
<?php for( $ii = 0; $ii < NUMBER_OF_FRIENDS; $ii++ ): ?>
<div class="friend">
<span><?php echo $ii + 1; ?>.</span>
<label class="name">Name:</label><input type="text" size="15" name="friends[name][]" class="name" value="<?php display_post( "friends.name.$ii" ); ?>" maxlength="50" />
<label class="email">Email:</label><input type="text" size="15" name="friends[email][]" class="email" value="<?php display_post( "friends.email.$ii" ); ?>" maxlength="50" />
</div>
<?php endfor; ?>
</fieldset>
<fieldset id="message">
<legend>Your Personal Message:</legend>
<textarea name="sender[message]" rows="10" cols="50"><?php display_post( 'sender.message' ); ?></textarea>
</fieldset>
</div>
<div id="submit">
<input type="submit" value="Send!" />
</div>
</form>
</div>
</body>
</html>
