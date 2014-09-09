<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('location_issue_tracking_report.html','main_container');

	/* Rights management */
	$PageIdentifier = "LocationWiseIssueTrackingReport";
	include_once('userrights.php');
	
	/* Set heading */
	$tpl->set_var("mainheading","Tickets - Location wise");

	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Tickets - Location wise</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.ticket.php');

	$objTicket = new ticket();

	if(isset($_POST["action"]) && trim($_POST["action"]) == "LocationWiseIssueTracking")
	{
		$_SESSION["LocationWiseIssueTracking"]["ticket_date_from"] = $_POST["search_ticket_date_from"];
		$_SESSION["LocationWiseIssueTracking"]["ticket_date_to"] = $_POST["search_ticket_date_to"];
		
		header("Location: location_issue_tracking_report.php");
		exit;
	}
	
	$ticket_date_from = $ticket_date_to = Date('Y-m-d');
	
	if(isset($_SESSION["LocationWiseIssueTracking"]["ticket_date_from"]) && trim($_SESSION["LocationWiseIssueTracking"]["ticket_date_from"]) != '')
		$ticket_date_from = $_SESSION["LocationWiseIssueTracking"]["ticket_date_from"];

	if(isset($_SESSION["LocationWiseIssueTracking"]["ticket_date_to"]) && trim($_SESSION["LocationWiseIssueTracking"]["ticket_date_to"]) != '')
		$ticket_date_to = $_SESSION["LocationWiseIssueTracking"]["ticket_date_to"];
	
	$tpl->set_var("search_ticket_date_from",$ticket_date_from);
	$tpl->set_var("search_ticket_date_to",$ticket_date_to);
	
	/* Display list */
	$tpl->set_var("FillLocationWiseIssueInformation","");
	$arrIssueInformation = $objTicket->fnGetLocationWiseIssueTracking($ticket_date_from, $ticket_date_to);

	if(count($arrIssueInformation) > 0)
	{
		foreach($arrIssueInformation as $curIssueInformation)
		{
			$tpl->SetAllValues($curIssueInformation);
			$tpl->parse("FillLocationWiseIssueInformation",true);
		}
	}

	$tpl->pparse('main',false);

?>
