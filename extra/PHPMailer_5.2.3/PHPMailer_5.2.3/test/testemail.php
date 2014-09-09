<?php
/**
* Simple example script using PHPMailer with exceptions enabled
* @package phpmailer
* @version $Id$
*/

require '../class.phpmailer.php';

try {
		/*$Subject = 'test';
		$MailTo = 'chandni.patel@transformsolution.net';
		$body = 'This is a testing mail.';*/
		$mail = new PHPMailer(true); //New instance, with exceptions enabled

		//$body = file_get_contents('contents.html');
		//$body = preg_replace('/\\\\/','', $content); //Strip backslashes

		$mail->IsSMTP();  // telling the class to use SMTP
		$mail->Mailer = "smtp";
		$mail->Host = "ssl://smtp.gmail.com";
		$mail->Port = 465;
		$mail->SMTPAuth = true; // turn on SMTP authentication
		$mail->Username = "transform.pms@gmail.com"; // SMTP username
		$mail->Password = "Transform@123"; // SMTP password 

		$mail->From     = "transform.pms@gmail.com";
		$to = $MailTo;

		$mail->AddAddress($to);

		$mail->Subject  = $Subject;
		//$mail->Body     = $content;
		//$mail->WordWrap = 50;  

		$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!";

		$mail->MsgHTML($body);

		$mail->IsHTML(true); // send as HTML

		$mail->Send();
		
		/*if($mail->Send())
			echo "sent";
		else
			echo "not sent";*/
		
		
	} catch (phpmailerException $e) {
		echo $e->errorMessage();
	}
?>