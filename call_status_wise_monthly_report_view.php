<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('call_status_wise_monthly_report_view.html','main_container');

	/* Rights management */
	$PageIdentifier = "CallStatusWiseMonthlyReport";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","Call status wise issue tracking report");

	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Call status wise issue tracking report</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.ticket.php');
	include_once('includes/class.issue_category.php');

	$objTicket = new ticket();
	$objIssueCategory = new issue_category();

	/* Display list */
	$tpl->set_var("FillCallStatusWiseInformation","");
	$tpl->set_var("DisplayInformationBlock","");

	$arrIssueInformation = array();

	if(isset($_REQUEST["status"]) && isset($_REQUEST["month"]) && isset($_REQUEST["year"]))
	{
		$arrIssueInformation = $objTicket->fnGetCallStatusWiseTicketInformation($_REQUEST["status"], $_REQUEST["month"], $_REQUEST["year"]);

		$IssueCategory = $objIssueCategory->fnGetIssueCategoryNameById($_REQUEST["id"]);

		$status = array("0"=>"In queue", "1"=>"Being attended", "2"=>"Resolved", "3"=>"Work in progress", "4"=>"Under observation");

		$arrMonth = array("01" =>"January", "02" =>"Feburary", "03"=>"March", "04"=>"April", "05"=>"May", "06"=>"June", "07"=>"July", "08"=>"August", "09"=>"September", "10"=>"October", "11"=>"November", "12"=>"December");

		$tpl->set_var("issue_status",$status[$_REQUEST["status"]]);
		$tpl->set_var("month",$arrMonth[$_REQUEST["month"]]);
		$tpl->set_var("year",$_REQUEST["year"]);
	}

	if(count($arrIssueInformation) > 0)
	{
		
		foreach($arrIssueInformation as $curIssueInformation)
		{
			$tpl->SetAllValues($curIssueInformation);
			$tpl->parse("FillCallStatusWiseInformation",true);
		}
		$tpl->parse("DisplayInformationBlock",true);
	}

	$tpl->pparse('main',false);

?>
