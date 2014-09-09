<?php

	include('common.php');

	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('department_wise_daily_headcount_report.html','main_container');

	$PageIdentifier = "DepartmentWiseHeadCountReport";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Department wise daily head count report");
	$breadcrumb = '<li class="active">Department wise daily head count report</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once("includes/class.employee.php");
	include_once("includes/class.departments.php");

	$objEmployee = new employee();
	$objDepartment = new departments();

	$curDate = Date('Y-m-d');
	$curYear = Date('Y');

	$arrYear = array($curYear-1, $curYear, $curYear+1);

	/* Search daily headcount */
	if(isset($_POST["action"]) && trim($_POST["action"]) == "DepartmentWiseDailyReportingHeadCountSearch")
	{
		$_SESSION["DepartmentWiseDailyHeadCountReport"]["month"] = $_POST["month"];
		$_SESSION["DepartmentWiseDailyHeadCountReport"]["year"] = $_POST["year"];
		$_SESSION["DepartmentWiseDailyHeadCountReport"]["department"] = $_POST["department"];

		header("Location: department_wise_daily_headcount_report.php");
		exit;
	}

	/* Check is session already set, if not set it to defaults */
	if(!isset($_SESSION["DepartmentWiseDailyHeadCountReport"]["month"]) || (isset($_SESSION["DepartmentWiseDailyHeadCountReport"]["month"]) && $_SESSION["DepartmentWiseDailyHeadCountReport"]["month"] == ""))
		$_SESSION["DepartmentWiseDailyHeadCountReport"]["month"] = Date('m');

	if(!isset($_SESSION["DepartmentWiseDailyHeadCountReport"]["year"]) || (isset($_SESSION["DepartmentWiseDailyHeadCountReport"]["year"]) && $_SESSION["DepartmentWiseDailyHeadCountReport"]["year"] == ""))
		$_SESSION["DepartmentWiseDailyHeadCountReport"]["year"] = $curYear;
		
	if(!isset($_SESSION["DepartmentWiseDailyHeadCountReport"]["department"]) || (isset($_SESSION["DepartmentWiseDailyHeadCountReport"]["department"]) && $_SESSION["DepartmentWiseDailyHeadCountReport"]["department"] == ''))
		$_SESSION["DepartmentWiseDailyHeadCountReport"]["department"] = '';

	$tpl->set_var("month", $_SESSION["DepartmentWiseDailyHeadCountReport"]["month"]);
	$tpl->set_var("year", $_SESSION["DepartmentWiseDailyHeadCountReport"]["year"]);
	$tpl->set_var("department", $_SESSION["DepartmentWiseDailyHeadCountReport"]["department"]);
	$tpl->set_var("month_text",Date('F',strtotime($_SESSION["DepartmentWiseDailyHeadCountReport"]["year"]."-".$_SESSION["DepartmentWiseDailyHeadCountReport"]["month"]."-01")));

	/* Fetch all the departments */
	$arrDepartments = $objDepartment->fnGetAllDepartments();
	$arrSearchDepartments = array();
	if(isset($_SESSION["DepartmentWiseDailyHeadCountReport"]["department"]) && $_SESSION["DepartmentWiseDailyHeadCountReport"]["department"] != '')
		$arrSearchDepartments[] = array("id"=>$_SESSION["DepartmentWiseDailyHeadCountReport"]["department"], 'title'=> $objDepartment->fnGetDepartmentNameById($_SESSION["DepartmentWiseDailyHeadCountReport"]["department"]));
	else
		$arrSearchDepartments = $arrDepartments;

	/* Fetch Daily Head Count for Month and Year, if year and month not greater then current month and yr */
	$arrDailyHeadCounts = array();
	if(Date('Y-m') >= $_SESSION["DepartmentWiseDailyHeadCountReport"]["year"]."-".$_SESSION["DepartmentWiseDailyHeadCountReport"]["month"])
	{
		/* Fetch daily head counts */
		$arrDailyHeadCounts = $objEmployee->fnGetDepartmentWiseDailyHeadCount($_SESSION["DepartmentWiseDailyHeadCountReport"]["month"], $_SESSION["DepartmentWiseDailyHeadCountReport"]["year"], $arrSearchDepartments);
	}

	/* Display Grid */
	$tpl->set_var("DisplayDateHeaderBlock", "");
	$tpl->set_var("DisplayDailyHeadCountForUsersBlock", "");

	$arrHeadCounts = array();
	$isSingle = false;

	if(count($arrSearchDepartments) > 0 && count($arrDailyHeadCounts) > 0)
	{
		/* Parse Heading Dates */
		$firstDate = $_SESSION["DepartmentWiseDailyHeadCountReport"]["year"].'-'.$_SESSION["DepartmentWiseDailyHeadCountReport"]["month"].'-01';
		$lastDate = date ("Y-m-t",strtotime($firstDate));

		$curDate = Date('Y-m-d');

		if($lastDate > $curDate)
			$lastDate = $curDate;

		$calculationFirstDate = $firstDate;
		while($calculationFirstDate <= $lastDate)
		{
			$tpl->set_var("display_date",  date ("d",strtotime($calculationFirstDate)));
			$tpl->parse("DisplayDateHeaderBlock", true);

			$calculationFirstDate = date ("Y-m-d", strtotime("+1 day", strtotime($calculationFirstDate)));
		}

		foreach($arrSearchDepartments as $curDepartment)
		{
			$tpl->set_var("department_title", $curDepartment["title"]);
			$tpl->set_var("department_id", $curDepartment["id"]);

			$tpl->set_var("DisplayDateHeadCountDetailsBlock1", "");
			$tpl->set_var("DisplayDateHeadCountDetailsBlock2", "");
			$tpl->set_var("DisplayDateHeadCountDetailsBlock3", "");
			$tpl->set_var("DisplayDateHeadCountDetailsBlock4", "");

			$calculationFirstDate = $firstDate;
			while($calculationFirstDate <= $lastDate)
			{
				$tpl->set_var("display_date", $calculationFirstDate);

				if(isset($arrDailyHeadCounts[$curDepartment["id"]][$calculationFirstDate]["OpeaningHeadCount"]))
					$tpl->set_var("opeaning_head_count",$arrDailyHeadCounts[$curDepartment["id"]][$calculationFirstDate]["OpeaningHeadCount"]);
				else
					$tpl->set_var("opeaning_head_count",0);

				if(isset($arrDailyHeadCounts[$curDepartment["id"]][$calculationFirstDate]["JoinersCount"]))
					$tpl->set_var("joiners_count",$arrDailyHeadCounts[$curDepartment["id"]][$calculationFirstDate]["JoinersCount"]);
				else
					$tpl->set_var("joiners_count",0);

				if(isset($arrDailyHeadCounts[$curDepartment["id"]][$calculationFirstDate]["LeaversCount"]))
					$tpl->set_var("leavers_count",$arrDailyHeadCounts[$curDepartment["id"]][$calculationFirstDate]["LeaversCount"]);
				else
					$tpl->set_var("leavers_count",0);

				if(isset($arrDailyHeadCounts[$curDepartment["id"]][$calculationFirstDate]["ClosingHeadCount"]))
				{
					$tpl->set_var("closing_head_count",$arrDailyHeadCounts[$curDepartment["id"]][$calculationFirstDate]["ClosingHeadCount"]);
					
					$arrHeadCounts[] = $arrDailyHeadCounts[$curDepartment["id"]][$calculationFirstDate]["ClosingHeadCount"];
				}
				else
				{
					$tpl->set_var("closing_head_count",0);
				}

				$tpl->parse("DisplayDateHeadCountDetailsBlock1", true);
				$tpl->parse("DisplayDateHeadCountDetailsBlock2", true);
				$tpl->parse("DisplayDateHeadCountDetailsBlock3", true);
				$tpl->parse("DisplayDateHeadCountDetailsBlock4", true);

				$calculationFirstDate = date ("Y-m-d", strtotime("+1 day", strtotime($calculationFirstDate)));
			}

			$tpl->parse("DisplayDailyHeadCountForUsersBlock", true);
		}
	}

	$arrHeadCounts = array_filter($arrHeadCounts);
	if(count($arrHeadCounts) > 0)
	{
		$tpl->set_var("average_head_count",sprintf('%.2f', array_sum($arrHeadCounts)/count($arrHeadCounts)));
	}
	else
	{
		$tpl->set_var("average_head_count",0);
	}

	/* Fill year dropdown */
	$tpl->set_var("DisplayYearBlock","");
	foreach($arrYear as $curYr)
	{
		$tpl->set_var("curyr", $curYr);
		$tpl->parse("DisplayYearBlock",true);
	}

	/* Fill department */
	$tpl->set_var("FillDepartmentBlock", "");
	if(count($arrDepartments) > 0)
	{
		foreach($arrDepartments as $curDepartment)
		{
			$tpl->set_var("department_id", $curDepartment["id"]);
			$tpl->set_var("department_title", $curDepartment["title"]);

			$tpl->parse("FillDepartmentBlock", true);
		}
	}

	$tpl->pparse("main",false);

?>
