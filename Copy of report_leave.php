<?php 
	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('report_leave.html','main_container');

	$PageIdentifier = "LeaveReport";
	include_once('userrights.php');

	$curDate = Date('Y-m-d');
	$first_day_this_month = date('Y-m-01');
	$last_day_this_month = date('Y-m-t');
	$tpl->set_var("mainheading","Leave Report");
	$breadcrumb = '<li class="active">Leave Report</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.attendance.php');
	$objAttendance = new attendance();

	include_once('includes/class.leave.php');
	$objLeave = new leave();

	include_once('includes/class.employee.php');
	$objEmployee = new employee();
	
	include_once('includes/class.shifts.php');
	$objShifts = new shifts();

	if(isset($_POST["action"]) && trim($_POST["action"]) == "AttendanceSearch")
	{
		/*$_SESSION["SearchLeave"]["start_date"] = $first_day_this_month;
		$_SESSION["SearchLeave"]["end_date"] = $last_day_this_month;*/
		/*$_SESSION["SearchLeave"]["month"] = Date('m');
		$_SESSION["SearchLeave"]["year"] = Date('Y');*/
		$_SESSION["SearchLeave"]["reporting_head"] = $_POST["reporting_head"];
		$_SESSION["SearchLeave"]["team_member"] = $_POST["team_member"];
		/*$_SESSION["SearchLeave"]["agents"] = $_POST["agents"];*/
		$_SESSION["SearchLeave"]["shiftid"] = $_POST["shiftid"];
		
		header("Location: report_leave.php");
		exit;
	}

	/*if(!isset($_SESSION["SearchLeave"]["start_date"]))
		$_SESSION["SearchLeave"]["start_date"] = $first_day_this_month;

	if(!isset($_SESSION["SearchLeave"]["end_date"]))
		$_SESSION["SearchLeave"]["end_date"] = $last_day_this_month;*/
		
	/*if(!isset($_SESSION["SearchLeave"]["month"]))
		$_SESSION["SearchLeave"]["month"] = Date('m');
	if(!isset($_SESSION["SearchLeave"]["year"]))
		$_SESSION["SearchLeave"]["year"] = Date('Y');*/

	//$_SESSION["SearchLeave"]["month"] = "09";
	//$_SESSION["SearchLeave"]["year"] = "2013";
	$_SESSION["SearchLeave"]["month"] = date('m');
	$_SESSION["SearchLeave"]["year"] = date('Y');
	
	if(!isset($_SESSION["SearchLeave"]["reporting_head"]))
		$_SESSION["SearchLeave"]["reporting_head"] = 0;
	if(!isset($_SESSION["SearchLeave"]["team_member"]))
		$_SESSION["SearchLeave"]["team_member"] = 0;
	if(!isset($_SESSION["SearchLeave"]["shiftid"]))
		$_SESSION["SearchLeave"]["shiftid"] = 0;

	//$curDate = Date('2013-09-30');
	$curDate = date('Y-m-t');

	$first_day_this_month = date('Y-m-01');
	$last_day_this_month = date('Y-m-t');
	
	$tpl->set_var("year",$_SESSION["SearchLeave"]["year"]);
	$tpl->set_var("month",$_SESSION["SearchLeave"]["month"]);

	//echo '<br>first_day_this_month:'.$first_day_this_month = '2013-09-01';
	//echo '<br>last_day_this_month:'.$last_day_this_month = '2013-09-30';

	/*if(isset($_SESSION["SearchLeave"]["start_date"]))
		$tpl->set_var("start_date", $_SESSION["SearchLeave"]["start_date"]);
	if(isset($_SESSION["SearchLeave"]["end_date"]))
		$tpl->set_var("end_date", $_SESSION["SearchLeave"]["end_date"]);*/
	if(isset($_SESSION["SearchLeave"]["month"]))
		$tpl->set_var("month", $_SESSION["SearchLeave"]["month"]);
	if(isset($_SESSION["SearchLeave"]["year"]))
		$tpl->set_var("year", $_SESSION["SearchLeave"]["year"]);
	if(isset($_SESSION["SearchLeave"]["reporting_head"]))
		$tpl->set_var("reporting_head", $_SESSION["SearchLeave"]["reporting_head"]);
	if(isset($_SESSION["SearchLeave"]["team_member"]))
		$tpl->set_var("team_member", $_SESSION["SearchLeave"]["team_member"]);
	/*if(isset($_SESSION["SearchLeave"]["agents"]))
		$tpl->set_var("agents", $_SESSION["SearchLeave"]["agents"]);*/
	if(isset($_SESSION["SearchLeave"]["shiftid"]))
		$tpl->set_var("shiftid", $_SESSION["SearchLeave"]["shiftid"]);

	/*$arrEmployee = $objEmployee->fnGetEmployees($_SESSION["SearchLeave"]["manager"], $_SESSION["SearchLeave"]["teamleader"], $_SESSION["SearchLeave"]["agents"],$_SESSION["SearchLeave"]["shiftid"],$_SESSION["SearchLeave"]["start_date"],$_SESSION["SearchLeave"]["end_date"]);*/
	$arrEmployee = $objEmployee->fnGetEmployees($_SESSION["SearchLeave"]["reporting_head"], $_SESSION["SearchLeave"]["team_member"],$_SESSION["SearchLeave"]["shiftid"],$_SESSION["SearchLeave"]["month"],$_SESSION["SearchLeave"]["year"]);
	
	$arrLeave = $objLeave->fnGetAllLeaveTypes();
	$arrEmpLeave = array();
	$arrEmpLeave["P"] = array("name" => "P", "cnt" => 0);
	$arrEmpLeave["Late"] = array("name" => "PLT", "cnt" => 0);
	$arrEmpLeave["BreakExceed"] = array("name" => "Break exceed", "cnt" => 0);
	if(count($arrLeave))
	{
		foreach($arrLeave as $curLeave)
		{
			$arrEmpLeave[$curLeave["id"]] = array("name" => $curLeave["title"], "cnt" => 0);
		}
	}
	
	//$startdate = $_SESSION["SearchLeave"]["start_date"];
	//$enddate = $_SESSION["SearchLeave"]["end_date"];
	
	$arrDates = array();
	
	/*while($startdate <= $enddate)
	{
		$arrDates[$startdate] = $startdate;
		
		$tpl->set_var("DisplayDate",$startdate);
		$tpl->parse("FillSearchDateBlock",true);
		
		$startdate = date('Y-m-d', strtotime('+1 day', strtotime($startdate)));
	}

	if(count($arrEmpLeave) > 0)
	{
		foreach($arrEmpLeave as $curLeave)
		{
			$tpl->set_var("DisplayDate",$curLeave["name"]);
			$tpl->parse("FillSearchDateBlock",true);
		}
	}*/

	if(count($arrEmployee) > 0)
	{
		foreach($arrEmployee as $curEmp)
		{
			//echo '<pre>';print_r($curEmp);
			$tpl->set_var("employeename",$curEmp["name"]);
			$tpl->set_var("employeeid",$curEmp["id"]);
			
			//$curEmpLeaveInfo = $arrEmpLeave;
			//echo '<pre>';print_r($curEmp);
			//$tpl->set_var("FillEmployeeAttendanceBlock","");
			$openingLeaveBalance = $curEmp['opening_leave_balance'];
			
			$arrGetAllTotalLeaveForLeaveReport = $objAttendance->fnGetAllLeaveForLeaveReport($curEmp['id'],$first_day_this_month,$last_day_this_month);
			
			$earnedLeaved = $objLeave->fnGetEarnedLeaves($curEmp['id'], $_SESSION["SearchLeave"]["month"], $_SESSION["SearchLeave"]["year"]);

			$tot_leaves = $openingLeaveBalance + $earnedLeaved;
			
			//if($tot_leaves > $arrGetAllTotalLeaveForLeaveReport)
			if($arrGetAllTotalLeaveForLeaveReport > 0)
			{
				$closing_Balance = ($tot_leaves - $arrGetAllTotalLeaveForLeaveReport);
			}
			else
			{
				$closing_Balance = $tot_leaves;
			}
			
			//echo "<br/>--".$curEmp['id'] ."----openingLeaveBalance: ".$openingLeaveBalance ."----earnedLeaved: ".$earnedLeaved."----arrGetAllTotalLeaveForLeaveReport: ".$arrGetAllTotalLeaveForLeaveReport."----closing_Balance: ".$closing_Balance;
			
			//echo $arrGetAllTotalLeaveForLeaveReport;
			$tpl->set_var("OpeningLeaveBalance",$openingLeaveBalance);
			$tpl->set_var("userLeave",$arrGetAllTotalLeaveForLeaveReport);
			$tpl->set_var("EarnedLeave",$earnedLeaved);
			$tpl->set_var("ClosingLeaveBalance",$closing_Balance);
			
			$tpl->parse("FillAttendanceInformation",true);
		}
	}

	//$arrGetAllReportingHead = $objAttendance->fnGetAllManagers();
	$arrGetAllReportingHead = $objAttendance->fnGetEmployees(Date('Y-m-d'));
	/*$arrGetAllTeamLeader = $objAttendance->fnGetAllTeamLeaders();*/
	$arrShifts = $objShifts->fnGetAllShifts();
	
	$tpl->set_var("FillReportingHead","");
	foreach($arrGetAllReportingHead as $curReportingHead)
	{
		$tpl->set_var("reporting_head_id",$curReportingHead["employee_id"]);
		$tpl->set_var("reporting_head_name",$curReportingHead["employee_name"]);

		$tpl->parse("FillReportingHead",true);
	}

	/*$tpl->set_var("FillTeamLeader","");
	foreach($arrGetAllTeamLeader as $teamleaders)
	{
		$tpl->SetAllValues($teamleaders);
		$tpl->parse("FillTeamLeader",true);
	}*/
	
	$tpl->set_var("FillShiftInformation","");
	foreach($arrShifts as $curShift)
	{
		$tpl->set_var("shift_id",$curShift["id"]);
		$tpl->set_var("shift_name",$curShift["title"]." [".$curShift["starttime"]." - ".$curShift["endtime"]."]");
		$tpl->parse("FillShiftInformation",true);
	}

	$tpl->pparse('main',false);
?>
