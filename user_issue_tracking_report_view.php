<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('user_issue_tracking_report_view.html','main_container');

	/* Rights management */
	$PageIdentifier = "UserWiseIssueTrackingReport";
	include_once('userrights.php');
	
	/* Set heading */
	$tpl->set_var("mainheading","Tickets - User wise view");

	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Tickets - User wise view</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.ticket.php');
	include_once('includes/class.employee.php');

	$objTicket = new ticket();
	$objEmployee = new employee();

	/* Display list */
	$tpl->set_var("FillUserWiseIssueInformation","");
	$tpl->set_var("DisplayInformationBlock","");

	$arrIssueInformation = array();

	$EmployeeName = "";

	if(isset($_REQUEST["id"]) && isset($_REQUEST["start_date"]) && isset($_REQUEST["end_date"]))
	{
		$arrIssueInformation = $objTicket->fnGetUserWiseTicketInformation($_REQUEST["id"], $_REQUEST["start_date"], $_REQUEST["end_date"]);
		
		$EmployeeName = $objEmployee->fnGetEmployeeNameById($_REQUEST["id"]);
		
		$tpl->set_var("username",$EmployeeName);
		$tpl->set_var("date_from",$_REQUEST["start_date"]);
		$tpl->set_var("date_to",$_REQUEST["end_date"]);
	}

	if(count($arrIssueInformation) > 0)
	{
		/* Export to excel */
		if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "export") 
		{
			$filename = "UserWiseIssueTracking-".Date('Y-m-d_H_i').".xls";

			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");;
			header("Content-Disposition: attachment;filename=".$filename);
			header("Content-Transfer-Encoding: binary ");
			
			xlsBOF();

			xlsWriteLabel(0,0,"User Name: ");
			xlsWriteLabel(0,1,$EmployeeName);
			xlsWriteLabel(1,0,"Date: ");
			xlsWriteLabel(1,1,$_REQUEST["start_date"]." - " . $_REQUEST["end_date"]);

			xlsWriteLabel(3,0,"Ticket Id");
			xlsWriteLabel(3,1,"Issue Category");
			xlsWriteLabel(3,2,"Issue");
			xlsWriteLabel(3,3,"Location");
			xlsWriteLabel(3,4,"Priority");
			xlsWriteLabel(3,5,"Estimated resolution time");
			xlsWriteLabel(3,6,"Ticket raised date");
			xlsWriteLabel(3,7,"Resolution Status");

			$xlsRow = 4;

			if(is_array($arrIssueInformation) && count($arrIssueInformation) > 0)
			{
				foreach($arrIssueInformation as $curIssueInformation)
				{
					xlsWriteLabel($xlsRow,0,$curIssueInformation["ticket_id"]);
					xlsWriteLabel($xlsRow,1,$curIssueInformation["issue_category"]);
					xlsWriteLabel($xlsRow,2,$curIssueInformation["issue"]);
					xlsWriteLabel($xlsRow,3,$curIssueInformation["location_name"]);
					xlsWriteLabel($xlsRow,4,$curIssueInformation["priority"]);
					xlsWriteLabel($xlsRow,5,$curIssueInformation["resolution_time"]);
					xlsWriteLabel($xlsRow,6,$curIssueInformation["ticket_date"]);
					xlsWriteLabel($xlsRow,7,$curIssueInformation["resolution_status_text"]);

					$xlsRow++;
				}
			}
			else
			{
				xlsWriteLabel($xlsRow,1,"No Records");
			}

			xlsEOF();

			exit;
		}
		
		foreach($arrIssueInformation as $curIssueInformation)
		{
			$tpl->SetAllValues($curIssueInformation);
			$tpl->parse("FillUserWiseIssueInformation",true);
		}
		$tpl->parse("DisplayInformationBlock",true);
	}
	
	$tpl->pparse('main',false);

?>
