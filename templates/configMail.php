<?php
/*
 * configMail.php is included at the start of sendEmail.php which
 * sends SMTP emails via PHPMailer.  Modify MAIL_HOST, MAIL_PORT,
 * MAIL_USER and MAIL_PASS to the values appropriate for your Email server.
 * MAIL_TLS should be set to 'tls' if SMTP server requires TLS.
 * MAIL_SENDER defines the text that will appear in the "From" field
 * of the Email. All replies will be directed here.
 */
  define('MAIL_HOST', 'your.mail.server.com');
  define('MAIL_PORT', '587');  // STARTTLS
  define('MAIL_USER', 'name@your.domain.com');
  define('MAIL_PASS', 'password');
  define('MAIL_SENDER', 'admin@your.domain.com');
  define('MAIL_TLS', '');