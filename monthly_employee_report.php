<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('monthly_employee_report.html','main_container');

	$PageIdentifier = "MonthlyEmployeeStatusReport";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Monthly Employee Report");
	$breadcrumb = '<li class="active">Monthly Employee Report</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once("includes/class.employee.php");
	
	$objEmployee = new employee();

	$tpl->set_var("FillJoineeReport","");
	$tpl->set_var("FillLeaversReport","");
	
	if(isset($_POST["action"]) && trim($_POST["action"]) == "SearchMonthlyEmployeeStatus")
	{
		if(isset($_POST["search_by"]) && trim($_POST["search_by"]) != "")
		{
			$curDate = Date("Y-m-")."01";
			$endDate = Date("Y-m-t", strtotime("-1 month", strtotime($curDate)));
			
			switch($_POST["search_by"])
			{
				case "1":
					/* Get date for last month */
					$startDate = Date("Y-m-", strtotime("-1 month", strtotime($curDate)))."01";
					break;
				case "2":
					/* Get date for last 3 month */
					$startDate = Date("Y-m-", strtotime("-3 month", strtotime($curDate)))."01";
					break;
				case "3":
					/* Get date for last 6 month */
					$startDate = Date("Y-m-", strtotime("-6 month", strtotime($curDate)))."01";
					break;
				case "4":
					/* Get date for last 9 month */
					$startDate = Date("Y-m-", strtotime("-9 month", strtotime($curDate)))."01";
					break;
				case "5":
					/* Get date for last 1 year */
					$startDate = Date("Y-m-", strtotime("-1 year", strtotime($curDate)))."01";
					break;
			}
			
			$_SESSION["MonthlyEmployeeReport"]["search_by"] = $_POST["search_by"];
			$_SESSION["MonthlyEmployeeReport"]["search_from_date"] = $startDate;
			$_SESSION["MonthlyEmployeeReport"]["search_to_date"] = $endDate;
		}
		else
		{
			$_SESSION["MonthlyEmployeeReport"]["search_from_date"] = $_POST["search_from_date"];
			$_SESSION["MonthlyEmployeeReport"]["search_to_date"] = $_POST["search_to_date"];
			$_SESSION["MonthlyEmployeeReport"]["search_by"] = "";
		}
		
		header("Location: monthly_employee_report.php");
		exit;
	}
	
	/* If start date not set then set it default to current month first date */
	if(!isset($_SESSION["MonthlyEmployeeReport"]["search_from_date"]) || (isset($_SESSION["MonthlyEmployeeReport"]["search_from_date"]) && $_SESSION["MonthlyEmployeeReport"]["search_from_date"] == ""))
	{
		$curDate = Date("Y-m-")."01";
		$_SESSION["MonthlyEmployeeReport"]["search_from_date"] = Date("Y-m-", strtotime("-1 month", strtotime($curDate)))."01";
	}
	
	/* If end date not set then set it default to current month last date */
	if(!isset($_SESSION["MonthlyEmployeeReport"]["search_to_date"]) || (isset($_SESSION["MonthlyEmployeeReport"]["search_to_date"]) && $_SESSION["MonthlyEmployeeReport"]["search_to_date"] == ""))
	{
		$_SESSION["MonthlyEmployeeReport"]["search_to_date"] = Date("Y-m-t", strtotime("-1 month", strtotime($_SESSION["MonthlyEmployeeReport"]["search_from_date"])));
	}
	
	/* If search as per month is not set then set it by default to null */
	if(!isset($_SESSION["MonthlyEmployeeReport"]["search_by"]))
		$_SESSION["MonthlyEmployeeReport"]["search_by"] = "";

	$tpl->set_var("search_from_date", $_SESSION["MonthlyEmployeeReport"]["search_from_date"]);
	$tpl->set_var("search_to_date", $_SESSION["MonthlyEmployeeReport"]["search_to_date"]);
	$tpl->set_var("search_by", $_SESSION["MonthlyEmployeeReport"]["search_by"]);
	
	/* Fill information for joinees */
	$arrJoinees = $objEmployee->fnGetJoineesByDateRange($_SESSION["MonthlyEmployeeReport"]["search_from_date"], $_SESSION["MonthlyEmployeeReport"]["search_to_date"]);
	if(count($arrJoinees) > 0)
	{
		foreach($arrJoinees as $curJoinee)
		{
			$tpl->set_var("joinee_employee_code", $curJoinee["employee_code"]);
			$tpl->set_var("joinee_name", $curJoinee["name"]);
			$tpl->set_var("joinee_reporting_head", $curJoinee["head_name"]);
			$tpl->set_var("joinee_date_of_joining", $curJoinee["date_of_joining"]);
			
			$tpl->parse("FillJoineeReport",true);
		}
	}
	
	/* Fill information for leavers */
	$arrLeavers = $objEmployee->fnGetLeaversByDateRange($_SESSION["MonthlyEmployeeReport"]["search_from_date"], $_SESSION["MonthlyEmployeeReport"]["search_to_date"]);
	if(count($arrLeavers) > 0)
	{
		foreach($arrLeavers as $curLeavers)
		{
			$tpl->set_var("leavers_employee_code", $curLeavers["employee_code"]);
			$tpl->set_var("leavers_name", $curLeavers["name"]);
			$tpl->set_var("leavers_reporting_head", $curLeavers["head_name"]);
			$tpl->set_var("leavers_date_of_joining", $curLeavers["date_of_joining"]);
			$tpl->set_var("leavers_relieving_date_by_manager", $curLeavers["relieving_date_by_manager"]);
			
			$tpl->parse("FillLeaversReport",true);
		}
	}

	$tpl->pparse('main',false);

?>
