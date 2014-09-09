<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('call_history_view.html','main_container');

	/* Rights management */
	$PageIdentifier = "TicketCallHistory";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","Pending Tickets");
	
	/* Set breadcrumb */
	$breadcrumb = '<li><a href="call_history.php">Call History</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Call History View</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.ticket.php');
	
	$objTicket = new ticket();
	
	/* Fetch ticket by ID */
	$tpl->set_var("DisplayTicketInformationBlock","");
	$tpl->set_var("DisplayNoTicketInformationBlock","");
	$tpl->set_var("FillStatusHistoryBlock","");
	
	if(isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		$tpl->set_var("ticket_id",$_REQUEST["id"]);
		$ticketInformation = $objTicket->fnGetTicketHistoryById(trim($_REQUEST["id"]));
		
		if(count($ticketInformation) > 0)
		{
			/* Fill dropdown status */
			$status = array("0"=>"In queue", "1"=>"Being attended", "2"=>"Resolved", "3"=>"Work in progress", "4"=>"Under observation");

			$tpl->SetAllValues($ticketInformation);
			
			if(isset($ticketInformation["call_status_history"]) && count($ticketInformation["call_status_history"]))
			{
				foreach($ticketInformation["call_status_history"] as $curCallStatus)
				{
					$curCallStatus["resolution_status_text"] = $status[$curCallStatus["resolution_status"]];

					$tpl->SetAllValues($curCallStatus);
					$tpl->parse("FillStatusHistoryBlock",true);
				}
			}
			
			$tpl->parse("DisplayTicketInformationBlock",false);
		}
		else
		{
			$tpl->parse("DisplayNoTicketInformationBlock",false);
		}
	}
	else
	{
		$tpl->parse("DisplayNoTicketInformationBlock",false);
	}

	$tpl->pparse('main',false);

?>
