<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('issue_category_tracking_report.html','main_container');

	/* Rights management */
	$PageIdentifier = "IssueCategoryWiseIssueTrackingReport";
	include_once('userrights.php');
	
	/* Set heading */
	$tpl->set_var("mainheading","Tickets - Category wise");

	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Tickets - Category wise</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.ticket.php');

	$objTicket = new ticket();

	if(isset($_POST["action"]) && trim($_POST["action"]) == "IssueCategoryWiseIssueTracking")
	{
		$_SESSION["IssueCatgoryWiseIssueTracking"]["ticket_date_from"] = $_POST["search_ticket_date_from"];
		$_SESSION["IssueCatgoryWiseIssueTracking"]["ticket_date_to"] = $_POST["search_ticket_date_to"];
		
		header("Location: issue_category_tracking_report.php");
		exit;
	}
	
	$ticket_date_from = $ticket_date_to = Date('Y-m-d');
	
	if(isset($_SESSION["IssueCatgoryWiseIssueTracking"]["ticket_date_from"]) && trim($_SESSION["IssueCatgoryWiseIssueTracking"]["ticket_date_from"]) != '')
		$ticket_date_from = $_SESSION["IssueCatgoryWiseIssueTracking"]["ticket_date_from"];

	if(isset($_SESSION["IssueCatgoryWiseIssueTracking"]["ticket_date_to"]) && trim($_SESSION["IssueCatgoryWiseIssueTracking"]["ticket_date_to"]) != '')
		$ticket_date_to = $_SESSION["IssueCatgoryWiseIssueTracking"]["ticket_date_to"];
	
	$tpl->set_var("search_ticket_date_from",$ticket_date_from);
	$tpl->set_var("search_ticket_date_to",$ticket_date_to);
	
	/* Display list */
	$tpl->set_var("FillIssueCategoryWiseIssueInformation","");
	$arrIssueInformation = $objTicket->fnGetIssueCategoryWiseIssueTracking($ticket_date_from, $ticket_date_to);

	if(count($arrIssueInformation) > 0)
	{
		foreach($arrIssueInformation as $curIssueInformation)
		{
			$tpl->SetAllValues($curIssueInformation);
			$tpl->parse("FillIssueCategoryWiseIssueInformation",true);
		}
	}

	$tpl->pparse('main',false);

?>
