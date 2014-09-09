<?php

	include('common.php');

	include_once('includes/class.requisition.php');
	
	$objRequisition = new requisition();
	
	$date = Date('Y-m-d');
	
	$arrUserRequisition = array();
	
	$arrRequisition = $objRequisition->fnGetRequisitionExpiredByDate($date);
	
	if(count($arrRequisition) > 0)
	{
		/* Send mail to it support for all  */
	
		$MailTo = "itsupport@transformsolution.net";
		
		$Subject = "Requisition Expiry";
		
		$content = "Dear IT Support team,<br><br>";
		$content .= "The below stated requisition are getting expired today.<br/><br/>";

		$content .= "<table cellspacting='3' cellpadding='1' bgcolor='#E6E6E6'>
						<tr bgcolor='#FFFFFF'>
							<th>User Name</th>
							<th>Requisition For</th>
							<th>Project Name</th>
						</tr>";

		foreach($arrRequisition as $curRequisition)
		{
			$arrUserRequisition[$curRequisition["email"]]["user_name"] = $curRequisition["user_name"];
			$arrUserRequisition[$curRequisition["email"]]["reqinfo"][] = array("title"=>$curRequisition["requisition_for_title"], "project"=>$curRequisition["project_name"]);
			
			$content .= "<tr bgcolor='#FFFFFF'>
							<td style='white-space:nowrap;'>".$curRequisition["user_name"]."</td>
							<td style='white-space:nowrap;'>".$curRequisition["requisition_for_title"]."</td>
							<td style='white-space:nowrap;'>".$curRequisition["project_name"]."</td>
						</tr>";
		}

		$content .= "</table>";
		$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;

		sendmail($MailTo, $Subject, $content);
		
		foreach($arrUserRequisition as $curRequisitionEmail => $curRequisitionFor)
		{
			$MailTo = $curRequisitionEmail;

			$Subject = "Requisition Expiry";
			
			$content = "Dear ".$curRequisitionFor["user_name"].",<br><br>";
			$content .= "The below stated requisition are getting expired today.<br/><br/>";

			$content .= "<table cellspacting='3' cellpadding='1' bgcolor='#E6E6E6' width='50%'>
							<tr bgcolor='#FFFFFF'>
								<th>Requisition For</th>
								<th>Project Name</th>
							</tr>";
			foreach($curRequisitionFor["reqinfo"] as $curReqinfo)
			{
				$content .= "<tr bgcolor='#FFFFFF'>
							<td>".$curReqinfo["title"]."</td>
							<td>".$curReqinfo["project"]."</td>
						</tr>";
			}
			
			$content .= "</table>";
			$content .= "<br><br>Regards,<br>".SITEADMINISTRATOR;
			
			sendmail($MailTo, $Subject, $content);
		}
	}

?>

