<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('ticket_log_graph.html','main_container');

	/* Rights management */
	$PageIdentifier = "DateWiseTicketGraph";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","Datewise ticket log graph");

	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Datewise ticket log graph</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.ticket.php');

	$objTicket = new ticket();

	if(isset($_POST["action"]) && trim($_POST["action"]) == "DatewiseTicket")
	{
		$_SESSION["DatewiseTracking"]["ticket_date_from"] = $_POST["search_ticket_date_from"];
		$_SESSION["DatewiseTracking"]["ticket_date_to"] = $_POST["search_ticket_date_to"];
		
		header("Location: ticket_log_graph.php");
		exit;
	}

	$ticket_date_from = $ticket_date_to = Date('Y-m-d');
	
	if(isset($_SESSION["DatewiseTracking"]["ticket_date_from"]) && trim($_SESSION["DatewiseTracking"]["ticket_date_from"]) != '')
		$ticket_date_from = $_SESSION["DatewiseTracking"]["ticket_date_from"];

	if(isset($_SESSION["DatewiseTracking"]["ticket_date_to"]) && trim($_SESSION["DatewiseTracking"]["ticket_date_to"]) != '')
		$ticket_date_to = $_SESSION["DatewiseTracking"]["ticket_date_to"];
	
	$tpl->set_var("search_ticket_date_from",$ticket_date_from);
	$tpl->set_var("search_ticket_date_to",$ticket_date_to);

	$tpl->set_var("DisplayGraphContainer","");
	$tpl->set_var("DisplayGraphScript","");
	$tpl->set_var("DisplayNoGraphContainer","");

	$arrIssueInformation = $objTicket->fnGetDatewiseTicketInformation($ticket_date_from, $ticket_date_to);
//print_r($arrIssueInformation);
	if(count($arrIssueInformation) > 0)
	{
		$keys =  implode(",",array_keys($arrIssueInformation));
		$values =  implode(",",$arrIssueInformation);
		
		$tpl->set_var("graph_labels", $keys);
		$tpl->set_var("graph_values", $values);
		
		$tpl->parse("DisplayGraphContainer",false);
		$tpl->parse("DisplayGraphScript",false);
	}
	else
	{
		$tpl->parse("DisplayNoGraphContainer",false);
	}

	$tpl->pparse('main',false);

?>
