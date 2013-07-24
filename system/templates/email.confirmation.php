<?php defined( 'VALID_INCLUSION' ) or die(); ?>
<html>
<head>
<title>{subject}</title>
</head>
<body>
Hi {friend.name},
<br/>
<br/>
You have been invited by your friend, {sender.name}. <?php if ( strlen( $message ) ): ?>Here's what he had to say:
<br/>
{personal.message}
<br/>
<?php endif; ?>
Please click the following link: <a href="{confirmation.fullLink}" title="Confirm">{confirmation.fullLink}</a> or go to: <a href="{confirmation.link}" title="Confirm">{confirmation.link}</a> and type in "{confirmation.code}" where requested.
<br/>
<br/>
Thanks,
Nige-Ryan Prince
</body>
</html>