<?php

	include('common.php');
	
	$tpl = new Template($app_path);
	
	$tpl->load_file('template.html','main');
	$tpl->load_file('daily_headcount_report.html','main_container');

	$PageIdentifier = "DailyHeadCountReport";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Daily head count report");
	$breadcrumb = '<li class="active">Daily head count report</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once("includes/class.employee.php");
	$objEmployee = new employee();

	$curDate = Date('Y-m-d');
	$curYear = Date('Y');

	$arrYear = array($curYear-1, $curYear, $curYear+1);

	/* Search daily headcount */
	if(isset($_POST["action"]) && trim($_POST["action"]) == "DailyReportingHeadCountSearch")
	{
		$_SESSION["DailyHeadCountReport"]["month"] = $_POST["month"];
		$_SESSION["DailyHeadCountReport"]["year"] = $_POST["year"];
		$_SESSION["DailyHeadCountReport"]["reporting_head"] = $_POST["reporting_head"];

		header("Location: daily_headcount_report.php");
		exit;
	}

	/* Check is session already set, if not set it to defaults */
	if(!isset($_SESSION["DailyHeadCountReport"]["month"]) || (isset($_SESSION["DailyHeadCountReport"]["month"]) && $_SESSION["DailyHeadCountReport"]["month"] == ""))
		$_SESSION["DailyHeadCountReport"]["month"] = Date('m');

	if(!isset($_SESSION["DailyHeadCountReport"]["year"]) || (isset($_SESSION["DailyHeadCountReport"]["year"]) && $_SESSION["DailyHeadCountReport"]["year"] == ""))
		$_SESSION["DailyHeadCountReport"]["year"] = $curYear;

	if(!isset($_SESSION["DailyHeadCountReport"]["reporting_head"]))
		$_SESSION["DailyHeadCountReport"]["reporting_head"] = "";

	$tpl->set_var("month", $_SESSION["DailyHeadCountReport"]["month"]);
	$tpl->set_var("year", $_SESSION["DailyHeadCountReport"]["year"]);
	$tpl->set_var("reporting_head", $_SESSION["DailyHeadCountReport"]["reporting_head"]);
	$tpl->set_var("month_text",Date('F',strtotime($_SESSION["DailyHeadCountReport"]["year"]."-".$_SESSION["DailyHeadCountReport"]["month"]."-01")));

	/* Fetch Daily Head Count for Month and Year, if year and month not greater then current month and yr*/
	if(Date('Y-m') >= $_SESSION["DailyHeadCountReport"]["year"]."-".$_SESSION["DailyHeadCountReport"]["month"])
	{
		/* Fetch daily head counts */
		$arrDailyHeadCounts = $objEmployee->fnGetDailyHeadCount($_SESSION["DailyHeadCountReport"]["month"], $_SESSION["DailyHeadCountReport"]["year"], $_SESSION["DailyHeadCountReport"]["reporting_head"],true);
	}

	/* Display Grid */
	$arrHeads = array();

	if($_SESSION["DailyHeadCountReport"]["reporting_head"] != "" && $_SESSION["DailyHeadCountReport"]["reporting_head"] != "0")
		$arrHeads[] = array("reportinghead_id" => $_SESSION["DailyHeadCountReport"]["reporting_head"], "reportinghead_name" => $objEmployee->fnGetEmployeeNameById($_SESSION["DailyHeadCountReport"]["reporting_head"]));
	else
	{
		$arrHeads = $objEmployee->fnGetAllReportingHeadsDetails($_SESSION["DailyHeadCountReport"]["year"], $_SESSION["DailyHeadCountReport"]["month"]);
	}

	$tpl->set_var("DisplayDateHeaderBlock", "");
	$tpl->set_var("DisplayDailyHeadCountForUsersBlock", "");

	$arrHeadCounts = array();
	$isSingle = false;

	$highestHead = $objEmployee->fnGetHighestReportingHead();

	if(count($arrHeads) > 0 && count($arrDailyHeadCounts) > 0)
	{
		if(count($arrHeads) == 1)
			$isSingle = true;
		
		/* Parse Heading Dates */
		$firstDate = $_SESSION["DailyHeadCountReport"]["year"].'-'.$_SESSION["DailyHeadCountReport"]["month"].'-01';
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

		foreach($arrHeads as $curReportingHead)
		{
			$tpl->set_var("reporting_head_name", $curReportingHead["reportinghead_name"]);
			$tpl->set_var("reporting_head_id", $curReportingHead["reportinghead_id"]);
			
			$tpl->set_var("DisplayDateHeadCountDetailsBlock1", "");
			$tpl->set_var("DisplayDateHeadCountDetailsBlock2", "");
			$tpl->set_var("DisplayDateHeadCountDetailsBlock3", "");
			$tpl->set_var("DisplayDateHeadCountDetailsBlock4", "");
			
			$calculationFirstDate = $firstDate;
			while($calculationFirstDate <= $lastDate)
			{
				$tpl->set_var("display_date", $calculationFirstDate);
				
				if(isset($arrDailyHeadCounts[$curReportingHead["reportinghead_id"]][$calculationFirstDate]["OpeaningHeadCount"]))
					$tpl->set_var("opeaning_head_count",$arrDailyHeadCounts[$curReportingHead["reportinghead_id"]][$calculationFirstDate]["OpeaningHeadCount"]);
				else
					$tpl->set_var("opeaning_head_count",0);

				if(isset($arrDailyHeadCounts[$curReportingHead["reportinghead_id"]][$calculationFirstDate]["JoinersCount"]))
					$tpl->set_var("joiners_count",$arrDailyHeadCounts[$curReportingHead["reportinghead_id"]][$calculationFirstDate]["JoinersCount"]);
				else
					$tpl->set_var("joiners_count",0);

				if(isset($arrDailyHeadCounts[$curReportingHead["reportinghead_id"]][$calculationFirstDate]["LeaversCount"]))
					$tpl->set_var("leavers_count",$arrDailyHeadCounts[$curReportingHead["reportinghead_id"]][$calculationFirstDate]["LeaversCount"]);
				else
					$tpl->set_var("leavers_count",0);

				if(isset($arrDailyHeadCounts[$curReportingHead["reportinghead_id"]][$calculationFirstDate]["ClosingHeadCount"]))
				{
					$tpl->set_var("closing_head_count",$arrDailyHeadCounts[$curReportingHead["reportinghead_id"]][$calculationFirstDate]["ClosingHeadCount"]);
					
					if($isSingle)
					{
						/* If reporting head is selected */
						$arrHeadCounts[] = $arrDailyHeadCounts[$curReportingHead["reportinghead_id"]][$calculationFirstDate]["ClosingHeadCount"];
					}
					else
					{
						/* If no reporting head is selected, consider the highest reporting head */
						if(in_array($curReportingHead["reportinghead_id"],$highestHead))
						{
							$arrHeadCounts[] = $arrDailyHeadCounts[$curReportingHead["reportinghead_id"]][$calculationFirstDate]["ClosingHeadCount"];
						}
					}
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
	
	/* Fill Reporting head dropdown */
	$tpl->set_var("DisplayReportingHeadBlock","");
	$arrReportingHead = $objEmployee->fnGetAllReportingHeadsDetails($_SESSION["DailyHeadCountReport"]["year"], $_SESSION["DailyHeadCountReport"]["month"]);
	if(count($arrReportingHead) > 0)
	{
		foreach($arrReportingHead as $curReportingHead)
		{
			$tpl->set_var("reportinghead_id", $curReportingHead["reportinghead_id"]);
			$tpl->set_var("reportinghead_name", $curReportingHead["reportinghead_name"]);

			$tpl->parse("DisplayReportingHeadBlock",true);
		}
	}

	$tpl->pparse("main",false);

?>
