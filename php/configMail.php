<?php
/*
 * configMail.php is included at the start of sendEmail.php which
 * sends SMTP emails via PHPMailer.  Modify MAIL_HOST, MAIL_PORT,
 * MAIL_USER and MAIL_PASS to the values appropriate for your Email server.
 */
	define('MAIL_HOST', 'mail.gandalf.ws');
  define('MAIL_PORT', '587');  // STARTTLS
  define('MAIL_USER', 'rich@gandalf.ws');
  define('MAIL_PASS', 'BD18dream');
?>