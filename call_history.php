<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('call_history.html','main_container');

	/* Rights management */
	$PageIdentifier = "TicketCallHistory";
	include_once('userrights.php');
	
	/* Set heading */
	$tpl->set_var("mainheading","Manage Call History");

	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Manage Call History</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.ticket.php');
	include_once('includes/class.issue_category.php');
	
	$objTicket = new ticket();
	$objIssueCategory = new issue_category();

	if(isset($_POST["action"]) && trim($_POST["action"]) == "CallHistoryReport")
	{
		$_SESSION["CallHistoryReport"]["issue_category_id"] = trim($_POST["search_issue_category_id"]);
		$_SESSION["CallHistoryReport"]["issue_id"] = trim($_POST["search_issue_id"]);
		$_SESSION["CallHistoryReport"]["priority"] = trim($_POST["search_priority"]);
		$_SESSION["CallHistoryReport"]["ticket_raised_date_from"] = trim($_POST["search_ticket_raised_date_from"]);
		$_SESSION["CallHistoryReport"]["ticket_raised_date_to"] = trim($_POST["search_ticket_raised_date_to"]);
		
		header("Location: call_history.php");
		exit;
	}

	$issue_category_id = $issue_id = $priority = 0;
	$ticket_raised_date_from = $ticket_raised_date_to = Date('Y-m-d');
	
	if(isset($_SESSION["CallHistoryReport"]["issue_category_id"]))
		$issue_category_id = $_SESSION["CallHistoryReport"]["issue_category_id"];

	if(isset($_SESSION["CallHistoryReport"]["issue_id"]))
		$issue_id = $_SESSION["CallHistoryReport"]["issue_id"];

	if(isset($_SESSION["CallHistoryReport"]["priority"]))
		$priority = $_SESSION["CallHistoryReport"]["priority"];

	if(isset($_SESSION["CallHistoryReport"]["ticket_raised_date_from"]))
		$ticket_raised_date_from = $_SESSION["CallHistoryReport"]["ticket_raised_date_from"];

	if(isset($_SESSION["CallHistoryReport"]["ticket_raised_date_to"]))
		$ticket_raised_date_to = $_SESSION["CallHistoryReport"]["ticket_raised_date_to"];

	$tpl->set_var("search_issue_category_id",$issue_category_id);
	$tpl->set_var("search_issue_id",$issue_id);
	$tpl->set_var("search_priority",$priority);
	$tpl->set_var("search_ticket_raised_date_from",$ticket_raised_date_from);
	$tpl->set_var("search_ticket_raised_date_to",$ticket_raised_date_to);

	$arrTickets = $objTicket->fnGetAllTickets($issue_category_id, $issue_id, $priority, $ticket_raised_date_from, $ticket_raised_date_to);

	/* Display list */
	$tpl->set_var("FillTicketsList","");
	if(count($arrTickets) > 0)
	{
		foreach($arrTickets as $curTicket)
		{
			$tpl->SetAllValues($curTicket);
			$tpl->parse("FillTicketsList",true);
		}
	}

	/* Fill issue category */
	$tpl->set_var("FillIssueCategory","");
	$arrIssueCategory = $objIssueCategory->fnGetAllIssueCategory();
	if(count($arrIssueCategory) > 0)
	{
		foreach($arrIssueCategory as $curIssueCategory)
		{
			$tpl->set_var("issuecategory_id",$curIssueCategory["id"]);
			$tpl->set_var("issuecategory_name",$curIssueCategory["issue_category"]);
			
			$tpl->parse("FillIssueCategory",true);
		}
	}

	$tpl->pparse('main',false);

?>
