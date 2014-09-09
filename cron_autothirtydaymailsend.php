<?php
	/* This file is using to send emails to reporting heads of employee on the employees one month completion */
	include('common.php');
	
	include_once('includes/class.employee.php');
	
	$objEmployee = new employee();

	$date = strtotime(Date('Y-m-d'));

	$date_one_month_before = date("Y-m-d", strtotime("-1 month", $date));
	
	/* Get all employee thats one month complete on current date */
	$getAllEmployees = $objEmployee->fnGetOneMonthComplete($date_one_month_before);

	/* sending mail to reporting heads and hr */
	foreach($getAllEmployees as $employee)
	{
		//echo '<pre>'; print_r($employee);
		/* Get All Reporting heads for the employee */
		$getAllTeamLeads = $objEmployee->fnGetReportingHeads($employee['emp_id']);
		//echo '<pre>'; print_r($getAllTeamLeads);
		$Subject = '30 Day Completion';
		/* Send mail to both reporting heads */
		foreach($getAllTeamLeads as $leads)
		{
			$content = "Dear ".$leads['name'].", <br /><br />";
			$content .= "This mail is to inform you that ".$employee['emp_name']." is complete one month today in our organization.";
			$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
			//echo '<br>'.$content;
			//sendmail($leads['email'],$Subject,$content);
		}
		
		$content = "Dear HR, <br /><br />";
		$content .= "This mail is to inform you that ".$employee['emp_name']." is complete one month today in our organization.";
		$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
		//echo '<br>'.$content;
		//sendmail('hr@transformsolution.net',$Subject,$content);
	}		
?>
