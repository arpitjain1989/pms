<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('ticket_resolution_report.html','main_container');

	/* Rights management */
	$PageIdentifier = "TicketResolutionReport";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","Manage Ticket Resolution Report");

	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Manage Ticket Resolution Report</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once("includes/class.ticket.php");
	
	$objTicket = new ticket();

	if(isset($_POST["action"]) && trim($_POST["action"]) == "TicketResolutionReportSearch")
	{
		$_SESSION["TicketResolutionReport"]["date_from"] = $_POST["search_ticket_date_from"];
		$_SESSION["TicketResolutionReport"]["date_to"] = $_POST["search_ticket_date_to"];
		
		header("Location: ticket_resolution_report.php");
	}

	$curDate = Date('Y-m-d');
	if(!isset($_SESSION["TicketResolutionReport"]["date_from"]))
		$_SESSION["TicketResolutionReport"]["date_from"] = $curDate;
	if(!isset($_SESSION["TicketResolutionReport"]["date_to"]))
		$_SESSION["TicketResolutionReport"]["date_to"] = $curDate;

	$tpl->set_var("search_ticket_date_from",$_SESSION["TicketResolutionReport"]["date_from"]);
	$tpl->set_var("search_ticket_date_to",$_SESSION["TicketResolutionReport"]["date_to"]);

	$arrTicket = $objTicket->fnTicketResolutionWiseReport($_SESSION["TicketResolutionReport"]["date_from"], $_SESSION["TicketResolutionReport"]["date_to"]);
	$tpl->set_var("FillTicketResolutionReportList","");
	
	/* Export to excel */
	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "export") 
	{
		$filename = "Ticket_Resolution_Report-".Date('Y-m-d_H_i').".xls";

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");;
		header("Content-Disposition: attachment;filename=".$filename);
		header("Content-Transfer-Encoding: binary ");
		
		xlsBOF();

		xlsWriteLabel(0,0,"Ticket Id");
		xlsWriteLabel(0,1,"User");
		xlsWriteLabel(0,2,"Issue Category");
		xlsWriteLabel(0,3,"Issue");
		xlsWriteLabel(0,4,"Ticket Date");
		xlsWriteLabel(0,5,"Ticket Count Start");
		xlsWriteLabel(0,6,"Ticket Count End");
		xlsWriteLabel(0,7,"Time Difference (d hh:mm)");
		xlsWriteLabel(0,8,"Resolved By");

		$xlsRow = 1;

		if(is_array($arrTicket) && count($arrTicket) > 0)
		{
			foreach($arrTicket as $curTicket)
			{
				xlsWriteLabel($xlsRow,0,$curTicket["ticket_id"]);
				xlsWriteLabel($xlsRow,1,$curTicket["user_name"]);
				xlsWriteLabel($xlsRow,2,$curTicket["issue_category_title"]);
				xlsWriteLabel($xlsRow,3,$curTicket["issue_title"]);
				xlsWriteLabel($xlsRow,4,$curTicket["ticket_date"]." ".$curTicket["ticket_time"]);
				xlsWriteLabel($xlsRow,5,$curTicket["ticket_resolution_start"]);
				xlsWriteLabel($xlsRow,6,$curTicket["ticket_resolution_end"]);
				xlsWriteLabel($xlsRow,7,$curTicket["time_difference"]);
				xlsWriteLabel($xlsRow,8,$curTicket["resolved_by_user"]);

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
	
	if(count($arrTicket) > 0)
	{
		foreach($arrTicket as $curTicket)
		{
			$tpl->SetAllValues($curTicket);

			$highlight_text = "";
			if($curTicket["is_before_time"] == 1)
				$highlight_text = "style='text-decoration: underline;font-weight: bold;color: green;'";

			$tpl->set_var("highlight_text",$highlight_text);

			$tpl->parse("FillTicketResolutionReportList",true);
		}
	}

	$tpl->pparse('main',false);
	
?>
