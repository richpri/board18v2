<?php

require_once( 'class.phpmailer.php');
require_once('configMail.php');

// the sendEmail() function uses SMTP to send plain text emails.
// Use configMail.php to specify the server and server access 
// information. You should use the SMTP server provided by your
// ISP or your hosting service for these Emails.
function sendEmail($email, $subject, $body) {
  $mailObj = new PHPMailer;

  $mailObj->isSMTP();             // Set mailer to use SMTP.
  $mailObj->Host = MAIL_HOST;     // Specify server.
  $mailObj->Port = MAIL_PORT;     // Specify port. Use 587 for STARTTLS.
  $mailObj->SMTPAuth = true;      // Enable SMTP authentication
  $mailObj->Username = MAIL_USER; // SMTP username.
  $mailObj->Password = MAIL_PASS; // SMTP password.

  $mailObj->From = 'noreply@board18.org';
  $mailObj->FromName = 'BOARD18';
  $mailObj->addAddress($email);   // Add a recipient

  $mailObj->WordWrap = 60;        // Set word wrap to 50 characters.
  $mailObj->isHTML(false);        // Set email format to plaintext.

  $mailObj->Subject = $subject;
  $mailObj->Body = $body;
  $mailObj->Debugoutput = "error_log";
  if ($mailObj->send()) {
    echo 'success';
  } else {
    error_log('Mailer Error: ' . $mailObj->ErrorInfo);
    echo 'fail';
  }
}
?>