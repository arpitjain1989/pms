<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('ticket.html','main_container');

	/* Rights management */
	$PageIdentifier = "Ticket";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","Ticket");
	
	/* Set breadcrumb */
	$breadcrumb = '<li><a href="issue_category_list.php">Manage Ticket</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Ticket</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.issue.php');
	include_once('includes/class.inventory_location.php');
	include_once('includes/class.ticket.php');

	$objIssue = new issue();
	$objInventoryLocation = new inventory_location();
	$objTicket = new ticket();

	/* save ticket */
	if(isset($_POST["action"]) && trim($_POST["action"]) == "SaveTicket")
	{
		$ticketInfo = $_POST;
		
		if(isset($_FILES["ticket_reference_image"]["name"]) && trim($_FILES["ticket_reference_image"]["name"]) != "")
		{
			$ticketInfo["ticket_reference_image"] = $_FILES["ticket_reference_image"];
		}
		
		if($objTicket->fnSaveTicket($ticketInfo))
		{
			header("Location: ticket_list.php?info=success");
			exit;
		}
		else
		{
			header("Location: ticket_list.php?info=err");
			exit;
		}
	}

	/* Fill issue category as per issue access */
	$tpl->set_var("FillIssueCategoryBlock","");
	$arrIssueCategory = $objIssue->fnGetIssueCategoryAccessDetailByDesignation($_SESSION["designation"]);
	if(count($arrIssueCategory) > 0)
	{
		foreach($arrIssueCategory as $curIssueCategory)
		{
			$tpl->set_var("issue_category_id",$curIssueCategory["issue_categgory_id"]);
			$tpl->set_var("issue_category_name",$curIssueCategory["issue_category"]);
			
			$tpl->parse("FillIssueCategoryBlock",true);
		}
	}

	/* Fill location */
	$tpl->set_var("FillLocationBlock","");
	//$arrLocation = $objInventoryLocation->fnGetAllInventoryLocation();
	$arrLocation = $objInventoryLocation->fnGetAllVisibleLocations();
	if(count($arrLocation) > 0)
	{
		foreach($arrLocation as $curLocation)
		{
			$tpl->set_var("location_id",$curLocation["id"]);
			$tpl->set_var("location_name",$curLocation["location_name"]);

			$tpl->parse("FillLocationBlock",true);
		}
	}

	$tpl->pparse('main',false);

?>
