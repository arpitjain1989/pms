<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('location_issue_tracking_report_view.html','main_container');

	/* Rights management */
	$PageIdentifier = "LocationWiseIssueTrackingReport";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","Tickets - Location wise view");

	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Tickets - Location wise view</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.ticket.php');
	include_once('includes/class.inventory_location.php');

	$objTicket = new ticket();
	$objInventoryLocation = new inventory_location();

	/* Display list */
	$tpl->set_var("FillLocationWiseIssueInformation","");
	$tpl->set_var("DisplayInformationBlock","");

	$arrIssueInformation = array();

	if(isset($_REQUEST["id"]) && isset($_REQUEST["start_date"]) && isset($_REQUEST["end_date"]))
	{
		$arrIssueInformation = $objTicket->fnGetLocationWiseTicketInformation($_REQUEST["id"], $_REQUEST["start_date"], $_REQUEST["end_date"]);

		$InventoryLocationName = $objInventoryLocation->fnGetLocationNameById($_REQUEST["id"]);

		$tpl->set_var("inventory_location",$InventoryLocationName);
		$tpl->set_var("date_from",$_REQUEST["start_date"]);
		$tpl->set_var("date_to",$_REQUEST["end_date"]);
	}

	if(count($arrIssueInformation) > 0)
	{
		foreach($arrIssueInformation as $curIssueInformation)
		{
			$tpl->SetAllValues($curIssueInformation);
			$tpl->parse("FillLocationWiseIssueInformation",true);
		}
		$tpl->parse("DisplayInformationBlock",true);
	}

	$tpl->pparse('main',false);

?>
