<?php

	$MailTo = "chandni.patel@transformsolution.net";
	$Subject = "Test";
	$content = "<a href='mailto:transform.pms@gmail.com?Subject=hello'>Test mail</a>";

	//sendmail($MailTo, $Subject, $content);

	function sendmail($MailTo, $Subject, $content)
	{
		/*$Headers = "MIME-Version: 1.0" . "\r\n";
		$Headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
		$Headers .= "From: Transform<transform.pms@gmail.com>";

		if(@mail($MailTo,$Subject,$MailContent,$Headers))
			return true;
		else
			return false;*/

		//echo $content;

		include_once('includes/class.phpmailer.php');

		try {
			$mail = new PHPMailer(true); //New instance, with exceptions enabled

			//$body = file_get_contents('contents.html');
			$body = preg_replace('/\\\\/','', $content); //Strip backslashes

			$mail->IsSMTP();  // telling the class to use SMTP
			$mail->Mailer = "smtp";
			$mail->Host = "ssl://smtp.gmail.com";
			$mail->Port = 465;
			$mail->SMTPAuth = true; // turn on SMTP authentication
			$mail->Username = "transform.pms@gmail.com"; // SMTP username
			$mail->Password = "Transform@123"; // SMTP password

			$mail->FromName     = "Transformsolution";
			$mail->From     = $mail->Username;

			$mail->AddAddress($MailTo);

			$mail->Subject  = $Subject;
			//$mail->Body     = $content;
			//$mail->WordWrap = 50;

			$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!";

			$mail->MsgHTML($body);

			$mail->IsHTML(true); // send as HTML

			//$mail->Send();

			if($mail->Send())
				echo "sent";
			else
				echo "not sent";


		} catch (phpmailerException $e) {
			echo $e->errorMessage();
		}
	}

	/* connect to gmail */
	$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
	$username = 'transform.pms@gmail.com';
	$password = 'Transform@123';

	/* try to connect */
	$inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());

	/* grab emails */
	$date = date("d-M-Y");

	//$emails = imap_search($inbox,"ON '".$date."'");
	$emails = imap_search($inbox,"SINCE ".$date);

	/* if emails are returned, cycle through each... */
	if($emails) {

		/* begin output var */
		$output = '';

		/* put the newest emails on top */
		rsort($emails);

		/* for every email... */
		foreach($emails as $email_number) {

			/* get information specific to this email */
			$overview = imap_fetch_overview($inbox,$email_number,0);
			$message = imap_fetchbody($inbox,$email_number,2);

			/* output the email header information */
			$output.= '<div class="toggler '.($overview[0]->seen ? 'read' : 'unread').'">';
			$output.= '<span class="subject">'.$overview[0]->subject.'</span> ';
			$output.= '<span class="from">'.$overview[0]->from.'</span>';
			$output.= '<span class="date">on '.$overview[0]->date.'</span>';
			$output.= '</div>';

			/* output the email body */
			$output.= '<div class="body">'.$message.'</div>';
		}

		echo $output;
	}

	/* close the connection */
	imap_close($inbox);

?>
