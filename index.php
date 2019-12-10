<?php
ini_set('display_errors', 1);
/*
 * @author: Keegan Shaw
 * This program takes a look at a text file <>
 * then opens the csv files from campus (or other sourcse)
 * to generate the email and then send it to the needed users
 *
 * Original intent was to email Special Ed Secretary to update
 * 6 year old students on their birthdays for state reporting.
 * Wanted to make this function generic so we could expand on demadn and not
 * need furhter programming.
 *
 * This file gets kicked off by a cron job set up on the server. Can also be run
 * on demand by opening this page in a webbrowser
 */

define("CONFIG_FILE", "emails.txt");

//using phpmailer to send emails from the windows server and give me access to gmail as a sender
require_once('PHPMailer/src/Exception.php');
require_once('PHPMailer/src/PHPMailer.php');
require_once('PHPMailer/src/SMTP.php');
//Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

function getEmailRequests(){
  /*
   * This function goes out and looks at the text file emails.text
   * ignores the header, then returns an array of key => value pairs that
   * correspond to the rows of the email. Key [e]
   *
   * Return type array
   */
   $returnArray = array();

   $handle = fopen(CONFIG_FILE, "r");

   //get the header row
   $lineData = fgetcsv($handle, 1024, ",");
   //we arent doing anything with it since we dont care about it...

   while ( ($lineData = fgetcsv($handle, 1024, ",")) != FALSE ){
     if ( count($lineData) != 3 ){
       //@TODO: Change this to be an email before the die()
       die(CONFIG_FILE . " format is not correct. Please correct before continuing");
     }

     $fileName = $lineData[0];
     $subject = $lineData[1];
     $emails = $lineData[2];
     //echo $fileName;
     $returnArray[$fileName] = $subject . "," . $emails;
   }
   return $returnArray;
}

function grabCSV($csvFileName){
  /*
   * This funciton takes in a csv file name and generates html from the lines
   * of it.
   *
   * Return type string
   */

   $returnString = "<html><body><table border='1'>";

   $handle = fopen($csvFileName, "r");

   //if the file doesnt exist
   if ( $handle == FALSE ){
     //@TODO: Generate an email here and die
     die($csvFileName . " does not exist or cannot be opened.");
   }

   while ( ($lineData = fgetcsv($handle, 2048, ",")) != FALSE ){
     $returnString .= "<tr>";
     //get the number of columns in the data
     $numColumns = count( $lineData );

     for( $i = 0; $i < $numColumns; $i++){
       $returnString .= "<td>" . $lineData[$i] . "</td>";
     }
     $returnString .= "</tr>";

   }
   $returnString .= "</table></body></html>";

   return $returnString;
}

function processSubjectEmails($subEmails){
  /*
  * Really the file tells the user to put ; between people
  * We just need to replace all the ; with ,
  *
  * Return type Array
  */

  //first strip off the subject from the inout string. It will be seperated by
  //a ,
  $subEmailsA = explode(",", $subEmails);
  $subject = $subEmailsA[0];
  $emailList = $subEmailsA[1];

  //$replaceChar = array(";");
  //$replaceWith = array(",");
  $emails = explode(";", $emailList);

  $returnArray[0] = $subject;
  $returnArray[1] = $emails;

  return $returnArray;
}

$testArray = getEmailRequests();
//print_r($testArray);
foreach ( $testArray as $file => $subEmails ){
  $body = grabCSV("csv/" . $file);

  //echo $body;

  $subEmailArray = processSubjectEmails($subEmails);

  $subject = $subEmailArray[0];
  $emails = $subEmailArray[1];
  //echo $emails;
  //echo $subject;

  //generate emails
  $headers = "MIME-Version: 1.0" . "\r\n" .
      'From: DoNotReply@columbus.k12.wi.us' . "\r\n" .
      "Content-type:text/html;charset=UTF-8" . "\r\n" .
      'Reply-To: DoNotReply@columbus.k12.wi.us' . "\r\n" .
      'X-Mailer: PHP/' . phpversion();
  
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
	foreach ( $emails as $email ){
		echo $email; 
		$mail->addAddress($email);
	}
	
	//Set the subject line
	$mail->Subject = $subject;
	//Read an HTML message body from an external file, convert referenced images to embedded,
	//convert HTML into a basic plain-text alternative body
	$mail->msgHTML($body);
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
  //mail($emails, $subject, $body, $headers);
}





?>
