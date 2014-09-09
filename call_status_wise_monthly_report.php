<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('call_status_wise_monthly_report.html','main_container');

	/* Rights management */
	$PageIdentifier = "CallStatusWiseMonthlyReport";
	include_once('userrights.php');
	
	/* Set heading */
	$tpl->set_var("mainheading","Call status wise monthly report");

	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Call status wise monthly report</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.ticket.php');

	$objTicket = new ticket();

	/* Get current year and previous year */
	$curYear = Date('Y');
	$curMonth = Date('m');
	$arrYear = array($curYear, $curYear-1);
	
	$tpl->set_var("FillYearDropdown","");
	foreach($arrYear as $cYear)
	{
		$tpl->set_var("year_value",$cYear);
		$tpl->parse("FillYearDropdown",true);
	}

	if(isset($_POST["action"]) && trim($_POST["action"]) == "CallStatusWiseMonthlyReport")
	{
		$_SESSION["CallStatusWiseMonthlyReport"]["month"] = $_POST["search_month"];
		$_SESSION["CallStatusWiseMonthlyReport"]["year"] = $_POST["search_year"];
		
		header("Location: call_status_wise_monthly_report.php");
		exit;
	}

	$search_month = $curMonth;
	$search_year = $curYear;

	if(isset($_SESSION["CallStatusWiseMonthlyReport"]["month"]) && trim($_SESSION["CallStatusWiseMonthlyReport"]["month"]) != '')
		$search_month = $_SESSION["CallStatusWiseMonthlyReport"]["month"];

	if(isset($_SESSION["CallStatusWiseMonthlyReport"]["year"]) && trim($_SESSION["CallStatusWiseMonthlyReport"]["year"]) != '')
		$search_year = $_SESSION["CallStatusWiseMonthlyReport"]["year"];

	$tpl->set_var("search_month",$search_month);
	$tpl->set_var("search_year",$search_year);
	
	/* Display list */
	$tpl->set_var("FillCallStatusWiseTicketInformation","");
	$arrIssueInformation = $objTicket->fnGetCallStatusWiseIssueTracking($search_month, $search_year);

	if(count($arrIssueInformation) > 0)
	{
		foreach($arrIssueInformation as $curIssueInformation)
		{
			$tpl->SetAllValues($curIssueInformation);
			$tpl->parse("FillCallStatusWiseTicketInformation",true);
		}
	}

	$tpl->pparse('main',false);

?>
