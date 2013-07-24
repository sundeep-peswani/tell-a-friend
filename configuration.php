<?php if ( defined( 'VALID_INCLUSION' ) == false ) exit;

/*
 * Server-related settings
 *
 * Site URL: the base url for the site, requires a trailing slash
 * Use Clean URLs: use clean URLs (ie, /admin/view/1 instead of index.php?view=admin&user=1)
 */
define( 'SITE_URL', 'http://www.example.com/' ); # requires trailing slash
define( 'USE_CLEAN_URLS', true );

/*
 * Database-related settings
 *
 * Host: the host of the MySQLi-compliant server
 * User: the username to connect to the server
 * Password: the password for the user
 * Database: the name of the database on the server
 * Prefix: the prefix used for every table, default: 'taf_'
 */
define( 'DB_HOST', 'tell-a-friend.db' );
define( 'DB_USER', 'taf' );
define( 'DB_PASSWORD', '' );
define( 'DB_DATABASE', 'taf' );
define( 'DB_PREFIX', 'taf_' );

/*
 * Administrative security settings
 *
 * User: the username with which to log in
 * Password: the password for the user
 */
define( 'ADMIN_USER', 'admin' );
define( 'ADMIN_PASSWORD', '' );

/*
 * Captcha settings
 * 	Uses ReCaptcha (www.recaptcha.net)
 *
 * Enable: whether or not to use captchas
 * Public Key: the public key required for a domain using ReCaptcha (see: www.recaptcha.net)
 * Private Key: the private key required for a domain using ReCaptcha (see: www.recaptcha.net)
 */
define( 'ENABLE_CAPTCHA', true );
define( 'RECAPTCHA_PUBLIC_KEY', '' );
define( 'RECAPTCHA_PRIVATE_KEY', '' );

/*
 * Personal preferences
 *
 * Number of friends: the number of friends of which users need to enter details every time
 * Max. number of downloads: the maximum number of downloads which are permitted after satisfying the validation requirement
 * Max. download window: the period of time after first accessing the download that the download should remain alive
 */
define( 'NUMBER_OF_FRIENDS', 2 );
define( 'MAX_NUM_DOWNLOADS', 2 ); # negative = disabled
define( 'MAX_DOWNLOAD_WINDOW', 60 * 60 * 48 ); # in seconds, negative = disabled 

/*
 * Mail-related settings
 * 	Uses PHPMailer (phpmailer.codeworxtech.com)
 *
 * From (email): the email address from which emails come
 * From (name): the name from which emails come
 * System: the type of mailer to use (mail, smtp or sendmail)
 * SMTP host: the hosts to check for sending SMTP mail
 * SMTP user: the username with which to authenticate at the SMTP server
 * SMTP password: the password with which to authenticate at the SMTP server
 */
define( 'MAIL_FROM_EMAIL', 'admin@localhost' );
define( 'MAIL_FROM_NAME', '' );
define( 'MAIL_SYSTEM', 'mail' );
define( 'MAIL_SMTP_HOST', '' );
define( 'MAIL_SMTP_USER', '' );
define( 'MAIL_SMTP_PASSWORD', '' );
define( 'MAIL_SENDMAIL_PATH', '' );
define( 'MAIL_WORDWRAP', 80 );
