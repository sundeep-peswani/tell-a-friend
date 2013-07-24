<?php defined( 'VALID_INCLUSION' ) or die(); ?>
<html>
<head>
<title>{subject}</title>
</head>
<body>
Hi {sender.name},
<br/>
<br/>
10 of your friends have successfully followed their links. Yay!
<br/>
You can now download something at <a href="{download.fullLink}" title="Download something">{download.fullLink}</a> (or go to <a href="{download.link}" title="Download link">{download.link}</a> and type in "{download.code}" where requested).
The download lasts for 2 days after first access. Go nuts!
<br/>
<br/>
Thanks,
Phil T. Pirate
</body>
</html>