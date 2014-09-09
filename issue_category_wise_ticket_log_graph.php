<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('issue_category_wise_ticket_log_graph.html','main_container');

	/* Rights management */
	$PageIdentifier = "IssueCategoryWiseTicketGraph";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","Tickets - Issue Category wise graph");

	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Tickets - Issue Category wise graph</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.ticket.php');

	$objTicket = new ticket();

	if(isset($_POST["action"]) && trim($_POST["action"]) == "IssueCategoryWiseTicket")
	{
		$_SESSION["IssueCategoryWiseTracking"]["ticket_date_from"] = $_POST["search_ticket_date_from"];
		$_SESSION["IssueCategoryWiseTracking"]["ticket_date_to"] = $_POST["search_ticket_date_to"];
		
		header("Location: issue_category_wise_ticket_log_graph.php");
		exit;
	}

	$ticket_date_from = $ticket_date_to = Date('Y-m-d');
	
	if(isset($_SESSION["IssueCategoryWiseTracking"]["ticket_date_from"]) && trim($_SESSION["IssueCategoryWiseTracking"]["ticket_date_from"]) != '')
		$ticket_date_from = $_SESSION["IssueCategoryWiseTracking"]["ticket_date_from"];

	if(isset($_SESSION["IssueCategoryWiseTracking"]["ticket_date_to"]) && trim($_SESSION["IssueCategoryWiseTracking"]["ticket_date_to"]) != '')
		$ticket_date_to = $_SESSION["IssueCategoryWiseTracking"]["ticket_date_to"];
	
	$tpl->set_var("search_ticket_date_from",$ticket_date_from);
	$tpl->set_var("search_ticket_date_to",$ticket_date_to);

	$tpl->set_var("DisplayGraphContainer","");
	$tpl->set_var("DisplayGraphScript","");
	$tpl->set_var("DisplayNoGraphContainer","");

	$arrIssueInformation = $objTicket->fnGetIssueCategoryTicketInformation($ticket_date_from, $ticket_date_to);

	if(count($arrIssueInformation) > 0)
	{
		
		$keys = $total_values = $pending_values = $resolved_intime_values = $comma = "";
		foreach($arrIssueInformation as $IssueKey => $arrIssueDetail)
		{
			$keys .= $comma . $IssueKey;
			$total_values .= $comma . $arrIssueDetail["total"];
			$pending_values .= $comma . $arrIssueDetail["pending"];
			$resolved_intime_values .= $comma . $arrIssueDetail["resolved_intime"];
			
			$comma = ",";
		}
		
		$tpl->set_var("graph_labels", $keys);
		$tpl->set_var("total_values", $total_values);
		$tpl->set_var("pending_values", $pending_values);
		$tpl->set_var("resolved_intime_values", $resolved_intime_values);
		
		$tpl->parse("DisplayGraphContainer",false);
		$tpl->parse("DisplayGraphScript",false);
	}
	else
	{
		$tpl->parse("DisplayNoGraphContainer",false);
	}

	$tpl->pparse('main',false);

?>
