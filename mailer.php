<?php
	ini_set('display_errors', 1);
	require_once('PHPMailer/src/Exception.php');
	require_once('PHPMailer/src/PHPMailer.php');
	require_once('PHPMailer/src/SMTP.php');
	//Import the PHPMailer class into the global namespace
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;

	//Create a new PHPMailer instance
	$mail = new PHPMailer;
	//Tell PHPMailer to use SMTP
	$mail->isSMTP();
	//Enable SMTP debugging
	// SMTP::DEBUG_OFF = off (for production use)
	// SMTP::DEBUG_CLIENT = client messages
	// SMTP::DEBUG_SERVER = client and server messages
	$mail->SMTPDebug = SMTP::DEBUG_SERVER;
	//Set the hostname of the mail server
	$mail->Host = 'smtp.gmail.com';
	//Set the SMTP port number - likely to be 25, 465 or 587
	$mail->Port = 587;
	//Whether to use SMTP authentication
	$mail->SMTPAuth = true;
	//Username to use for SMTP authentication
	$mail->Username = 'donotreply@columbus.k12.wi.us';
	//Password to use for SMTP authentication
	$mail->Password = 'F0rMailing$'; //
	//Set who the message is to be sent from
	$mail->setFrom('donotreply@columbus.k12.wi.us', 'Do Not Reply');
	//Set an alternative reply-to address
	$mail->addReplyTo('donotreply@columbus.k12.wi.us', 'Do Not Reply');
	//Set who the message is to be sent to
	$mail->addAddress('kshaw@columbus.k12.wi.us', 'Keegan Shaw');
	//Set the subject line
	$mail->Subject = 'PHPMailer SMTP test';
	//Read an HTML message body from an external file, convert referenced images to embedded,
	//convert HTML into a basic plain-text alternative body
	$mail->msgHTML(file_get_contents('contents.html'), __DIR__);
	//Replace the plain text body with one created manually
	$mail->AltBody = 'This is a plain-text message body';
	//Attach an image file
	//$mail->addAttachment('images/phpmailer_mini.png');
	//send the message, check for errors
	if (!$mail->send()) {
		echo 'Mailer Error: ' . $mail->ErrorInfo;
	} else {
		echo 'Message sent!';
	}
?>