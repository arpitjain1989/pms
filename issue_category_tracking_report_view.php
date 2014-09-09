<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('issue_category_tracking_report_view.html','main_container');

	/* Rights management */
	$PageIdentifier = "IssueCategoryWiseIssueTrackingReport";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","Tickets - Category wise view");

	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Tickets - Category wise view</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.ticket.php');
	include_once('includes/class.issue_category.php');

	$objTicket = new ticket();
	$objIssueCategory = new issue_category();

	/* Display list */
	$tpl->set_var("FillIssueCategoryWiseIssueInformation","");
	$tpl->set_var("DisplayInformationBlock","");

	$arrIssueInformation = array();

	if(isset($_REQUEST["id"]) && isset($_REQUEST["start_date"]) && isset($_REQUEST["end_date"]))
	{
		$arrIssueInformation = $objTicket->fnGetIssueCategoryWiseTicketInformation($_REQUEST["id"], $_REQUEST["start_date"], $_REQUEST["end_date"]);

		$IssueCategory = $objIssueCategory->fnGetIssueCategoryNameById($_REQUEST["id"]);

		$tpl->set_var("issue_category",$IssueCategory);
		$tpl->set_var("date_from",$_REQUEST["start_date"]);
		$tpl->set_var("date_to",$_REQUEST["end_date"]);
	}

	if(count($arrIssueInformation) > 0)
	{
		foreach($arrIssueInformation as $curIssueInformation)
		{
			$tpl->SetAllValues($curIssueInformation);
			$tpl->parse("FillIssueCategoryWiseIssueInformation",true);
		}
		$tpl->parse("DisplayInformationBlock",true);
	}

	$tpl->pparse('main',false);

?>
