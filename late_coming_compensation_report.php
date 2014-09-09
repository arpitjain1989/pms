<?php

	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('late_coming_compensation_report.html','main_container');
	
	$PageIdentifier = "LateComingCompensationReport";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Break Exceed Approved Report");
	$breadcrumb = '<li class="active">Break Exceed Approved Report</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once("includes/class.attendance.php");
	include_once("includes/class.employee.php");
	
	$objAttendance = new attendance();
	$objEmployee	= new employee();

	$curYear = Date('Y');
	$curMonth = Date('m');

	/* Get current year and previous year */
	$arrYear = array($curYear, $curYear-1);

	/* Search break exceed */
	if(isset($_POST["action"]) && trim($_POST["action"]) == "BreakExceedApprovedSearch")
	{
		$_SESSION["latecoming"]["month"] = $_POST["month"];
		$_SESSION["latecoming"]["year"] = $_POST["year"];
		
		header("Location: late_coming_compensation_report.php");
		exit;
	}
	
	if(!isset($_SESSION["latecoming"]["month"]))
		$_SESSION["latecoming"]["month"] = $curMonth;

	if(!isset($_SESSION["latecoming"]["year"]))
		$_SESSION["latecoming"]["year"] = $curYear;
	
	 $year = $_SESSION["latecoming"]["year"];
	 $month = $_SESSION["latecoming"]["month"];
		
	$tpl->set_var("year",$year);
	$tpl->set_var("month",$month);
	
	$breakExceedInformation = $objAttendance->fnGetLateComingCompensationByYearAndMonth($year, $month);
	
	$tpl->set_var("FillBreakExceedApprovedInformation","");
	if(count($breakExceedInformation) > 0)
	{
		foreach($breakExceedInformation as $key=>$curUserInformation)
		{
			$diff=strtotime($curUserInformation['compensation_totime'])-strtotime($curUserInformation['compensation_fromtime']);
			$tpl->set_var("diff",gmdate("H:i:s", $diff));
			$name = $objEmployee->fnGetEmployeeNameById($curUserInformation['userid']);
			$tpl->set_var("name",$name);
			$datetime = strtotime($curUserInformation['date']);
			$mysqldate = date("d/m/y ", $datetime);
			$tpl->set_var("datetime",$mysqldate);
			
			$tpl->SetAllValues($curUserInformation);
			$tpl->parse("FillBreakExceedApprovedInformation",true);
		}
	}
	
	/*$breakExceedInformation = $objAttendance->fnGetBreakExceedApprovedByYearAndMonth($year, $month);

	$tpl->set_var("DisplayBreakExceed","");
	$tpl->set_var("NoDisplayBreakExceed","");
	
	$tpl->set_var("FillBreakExceedInformation","");

	if(count($breakExceedInformation) > 0)
	{
		foreach($breakExceedInformation as $curUserInformation)
		{
			$tpl->set_var("employeename",$curUserInformation["name"]);
			$totalExceed = count($curUserInformation["exceedinfo"]);
			
			$deduction = ($totalExceed - 3) * 0.25;
			
			$tpl->set_var("deduction",$deduction);
			
			$tpl->set_var("FillBreakExceedDetailsInformation","");
			
			if($totalExceed > 0)
			{
				$i = 1;
				foreach($curUserInformation["exceedinfo"] as $curInfo)
				{
					$tpl->set_var("srno", $i++);
					$tpl->set_var("breakexceeddate", $curInfo["exceeddate"]);
					$tpl->set_var("totalbreaktime", $curInfo["totalbreak"]);
					$tpl->set_var("totalexceedtime", $curInfo["exceedtime"]);
					$tpl->set_var("official_total_working_hours", $curInfo["official_total_working_hours"]);
					
					$tpl->parse("FillBreakExceedDetailsInformation",true);
				}
			}
			
			$tpl->parse("FillBreakExceedInformation",true);
		}
		$tpl->parse("DisplayBreakExceed",true);
	}
	else
	{
		$tpl->parse("NoDisplayBreakExceed",true);
	}*/

	/* Fill year dropdown */
	$tpl->set_var("DisplayYearBlock","");
	if(count($arrYear) > 0)
	{
		foreach($arrYear as $yr)
		{
			$tpl->set_var("curyr", $yr);
			
			$tpl->parse("DisplayYearBlock",true);
		}
	}
	
	$tpl->pparse('main',false);

?>
