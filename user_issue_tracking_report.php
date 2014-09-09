<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('user_issue_tracking_report.html','main_container');

	/* Rights management */
	$PageIdentifier = "UserWiseIssueTrackingReport";
	include_once('userrights.php');
	
	/* Set heading */
	$tpl->set_var("mainheading","Tickets - User wise");

	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Tickets - User wise</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.ticket.php');

	$objTicket = new ticket();

	if(isset($_POST["action"]) && trim($_POST["action"]) == "UserIssueTracking")
	{
		$_SESSION["UserIssueTracking"]["ticket_date_from"] = $_POST["search_ticket_date_from"];
		$_SESSION["UserIssueTracking"]["ticket_date_to"] = $_POST["search_ticket_date_to"];
		
		header("Location: user_issue_tracking_report.php");
		exit;
	}
	
	$ticket_date_from = $ticket_date_to = Date('Y-m-d');
	
	if(isset($_SESSION["UserIssueTracking"]["ticket_date_from"]) && trim($_SESSION["UserIssueTracking"]["ticket_date_from"]) != '')
		$ticket_date_from = $_SESSION["UserIssueTracking"]["ticket_date_from"];

	if(isset($_SESSION["UserIssueTracking"]["ticket_date_to"]) && trim($_SESSION["UserIssueTracking"]["ticket_date_to"]) != '')
		$ticket_date_to = $_SESSION["UserIssueTracking"]["ticket_date_to"];
	
	$tpl->set_var("search_ticket_date_from",$ticket_date_from);
	$tpl->set_var("search_ticket_date_to",$ticket_date_to);
	
	/* Display list */
	$tpl->set_var("FillUserWiseIssueInformation","");
	$arrIssueInformation = $objTicket->fnGetUserWiseIssueTracking($ticket_date_from, $ticket_date_to);

	if(count($arrIssueInformation) > 0)
	{
		foreach($arrIssueInformation as $curIssueInformation)
		{
			$tpl->SetAllValues($curIssueInformation);
			$tpl->parse("FillUserWiseIssueInformation",true);
		}
	}

	$tpl->pparse('main',false);

?>
