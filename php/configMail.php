<?php
/*
 * configMail.php is included at the start of sendEmail.php which
 * sends SMTP emails via PHPMailer.  Modify MAIL_HOST, MAIL_PORT,
 * MAIL_USER and MAIL_PASS to the values appropriate for your Email server.
 * MAIL_TLS should be set to 'Yes' if SMTP server requires TLS. See Bug 90.
 * MAIL_SENDER defines the text that will appear in the "From" field
 * of the Email. All replies will be directed here.
 */
  define('MAIL_HOST', 'mail.board18.org');
  define('MAIL_PORT', '587');  // STARTTLS
  define('MAIL_USER', 'rich02@board18.org');
  define('MAIL_PASS', 'BD18dream');
  define('MAIL_SENDER', 'admin@board18.org');
  define('MAIL_TLS', 'No');
?>
