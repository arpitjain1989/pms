<?php 

	include('common.php');
	$tpl = new Template($app_path);

	ini_set("max_execution_time",300);

	$tpl->load_file('template.html','main');
	$tpl->load_file('report_attendance.html','main_container');

	$PageIdentifier = "AttendanceReport";
	include_once('userrights.php');

	$curDate = Date('Y-m-d');
	$curYear = Date('Y');

	$arrYear = array($curYear, $curYear-1);

	$tpl->set_var("mainheading","Attendances Report");
	$breadcrumb = '<li class="active">Attendances Report</li>';
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
		/*$_SESSION["SearchAttendance"]["start_date"] = $_POST["start_date"];
		$_SESSION["SearchAttendance"]["end_date"] = $_POST["end_date"];*/

		$_SESSION["SearchAttendance"]["month"] = $_POST["month"];
		$_SESSION["SearchAttendance"]["year"] = $_POST["year"];
		$_SESSION["SearchAttendance"]["manager"] = $_POST["manager"];
		$_SESSION["SearchAttendance"]["teamleader"] = $_POST["teamleader"];
		$_SESSION["SearchAttendance"]["agents"] = $_POST["agents"];
		$_SESSION["SearchAttendance"]["shiftid"] = $_POST["shiftid"];
		$_SESSION["SearchAttendance"]["issingle"] = false;
		
		header("Location: report_attendance.php");
		exit;
	}

	/*if(!isset($_SESSION["SearchAttendance"]["start_date"]))
		$_SESSION["SearchAttendance"]["start_date"] = $curDate;
	if(!isset($_SESSION["SearchAttendance"]["end_date"]))
		$_SESSION["SearchAttendance"]["end_date"] = $curDate;*/
		
	if(!isset($_SESSION["SearchAttendance"]["month"]))
		$_SESSION["SearchAttendance"]["month"] = Date('m');
	if(!isset($_SESSION["SearchAttendance"]["year"]))
		$_SESSION["SearchAttendance"]["year"] = $curYear;
		
	/*if(isset($_SESSION["SearchAttendance"]["start_date"]))
		$tpl->set_var("start_date", $_SESSION["SearchAttendance"]["start_date"]);
	if(isset($_SESSION["SearchAttendance"]["end_date"]))
		$tpl->set_var("end_date", $_SESSION["SearchAttendance"]["end_date"]);*/

	if(isset($_SESSION["SearchAttendance"]["month"]))
		$tpl->set_var("month", $_SESSION["SearchAttendance"]["month"]);
	if(isset($_SESSION["SearchAttendance"]["year"]))
		$tpl->set_var("year", $_SESSION["SearchAttendance"]["year"]);
	if(isset($_SESSION["SearchAttendance"]["manager"]))
		$tpl->set_var("manager", $_SESSION["SearchAttendance"]["manager"]);
	if(isset($_SESSION["SearchAttendance"]["teamleader"]))
		$tpl->set_var("teamleader", $_SESSION["SearchAttendance"]["teamleader"]);
	if(isset($_SESSION["SearchAttendance"]["agents"]))
		$tpl->set_var("agents", $_SESSION["SearchAttendance"]["agents"]);
	if(isset($_SESSION["SearchAttendance"]["shiftid"]))
		$tpl->set_var("shiftid", $_SESSION["SearchAttendance"]["shiftid"]);

	/*$arrEmployee = $objEmployee->fnGetEmployees($_SESSION["SearchAttendance"]["manager"], $_SESSION["SearchAttendance"]["teamleader"], $_SESSION["SearchAttendance"]["agents"],$_SESSION["SearchAttendance"]["shiftid"],$_SESSION["SearchAttendance"]["start_date"],$_SESSION["SearchAttendance"]["end_date"]);*/
	
	$arrEmployee = $objEmployee->fnGetEmployees($_SESSION["SearchAttendance"]["manager"], $_SESSION["SearchAttendance"]["teamleader"], $_SESSION["SearchAttendance"]["agents"],$_SESSION["SearchAttendance"]["shiftid"],$_SESSION["SearchAttendance"]["month"],$_SESSION["SearchAttendance"]["year"], $_SESSION["SearchAttendance"]["issingle"]);
	
	$arrLeave = $objLeave->fnGetAllLeaveTypes();
	$arrEmpLeave = array();
	$arrEmpLeave["P"] = array("name" => "P", "cnt" => 0);
	$arrEmpLeave["Late"] = array("name" => "PLT", "cnt" => 0);
	$arrEmpLeave["LateApproved"] = array("name" => "PLT Approved", "cnt" => 0);
	$arrEmpLeave["BreakExceed"] = array("name" => "Break exceed", "cnt" => 0);
	$arrEmpLeave["BreakExceedApproved"] = array("name" => "Break exceed Approved", "cnt" => 0);
	
	if(count($arrLeave))
	{
		foreach($arrLeave as $curLeave)
		{
			$arrEmpLeave[$curLeave["id"]] = array("name" => $curLeave["title"], "cnt" => 0);
		}
	}
	
	/*$startdate = $_SESSION["SearchAttendance"]["start_date"];
	$enddate = $_SESSION["SearchAttendance"]["end_date"];*/
	
	$startdate = $_SESSION["SearchAttendance"]["year"]."-".$_SESSION["SearchAttendance"]["month"]."-01";
	$enddate = date("Y-m-t", strtotime($startdate));

	$tpl->set_var("headingdate",date('F Y', strtotime($startdate)));

	$arrDates = array();
	
	while($startdate <= $enddate)
	{
		$arrDates[$startdate] = $startdate;
		
		$tpl->set_var("DisplayDate",date('d D', strtotime($startdate)));
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
	}

	if(count($arrEmployee) > 0)
	{
		$arrLeaveColor = $objLeave->fnGetLeaveColorArray();
		$arrLeaveColor["PLT"] = "#DB9EA6";
		foreach($arrEmployee as $curEmp)
		{
			$tpl->set_var("employeename",$curEmp["name"]);
			
			$curEmpLeaveInfo = $arrEmpLeave;
			
			$tpl->set_var("FillEmployeeAttendanceBlock","");
			
			if(count($arrDates) > 0)
			{
				foreach($arrDates as $curdt)
				{
					$attendanceinfo = $objAttendance->fnGetAttendanceDetails($curdt, $curEmp["id"]);
					//print_r($attendanceinfo);
					$late_time  = "";
					$displayStr = "";
					$br = "";
					if(count($attendanceinfo))
					{
						if(($attendanceinfo["leave_id"] == 0) && $attendanceinfo["in_time"] != "00:00:00")
						{
							$displayStr = "P";
							
							$curEmpLeaveInfo["P"]["cnt"] = $curEmpLeaveInfo["P"]["cnt"] + 1;
						}
						else if($attendanceinfo["leave_id"] != 0)
						{
							$leaveInfo = $objLeave->fnGetLeaveTypeId($attendanceinfo["leave_id"]);
							if(isset($leaveInfo["title"]))
							{
								/*if($leaveInfo["title"] == "SC")
									$displayStr = "P";
								else */
									$displayStr = $leaveInfo["title"];
									$curEmpLeaveInfo[$attendanceinfo["leave_id"]]["cnt"] = $curEmpLeaveInfo[$attendanceinfo["leave_id"]]["cnt"] + 1;

									if($leaveInfo["title"] == "SM")
										$curEmpLeaveInfo["P"]["cnt"] = $curEmpLeaveInfo["P"]["cnt"] + 1;
							}
						}
					}
					
					//$attendanceinfo["isLateAllowed"] == "0"
					if($attendanceinfo["is_late"] == 1)
					{
						$late_time  = "Late: " . $attendanceinfo["late_time"];
						$br = "<br/>";
						if($displayStr == "P")
						{
							$displayStr = "PLT";
							$curEmpLeaveInfo["Late"]["cnt"] = $curEmpLeaveInfo["Late"]["cnt"] + 1;
						}
						else if($displayStr == "SM")
						{
							$displayStr = "SM+PLT";
							$curEmpLeaveInfo[11]["cnt"] = $curEmpLeaveInfo[11]["cnt"] + 1;
							$curEmpLeaveInfo["Late"]["cnt"] = $curEmpLeaveInfo["Late"]["cnt"] + 1;
						}
					}
					
					$fontcolor = "color:#000000";
					if($attendanceinfo["isExceededBreak"] == 1)
					{
						$curEmpLeaveInfo["BreakExceed"]["cnt"] = $curEmpLeaveInfo["BreakExceed"]["cnt"] + 1;
						$fontcolor = "color:#0000CC; font-weight:bold;";
						
						$late_time  .= $br . "Break exceed: " . $attendanceinfo["break_exceed_time"];
						$br = "<br/>";
					}
					
					if($attendanceinfo["ishoursapproved"] == "1")
					{
						$late_time  .= $br . "Approved";
						
						if($attendanceinfo["is_late"] == 1)
						{
							$curEmpLeaveInfo["LateApproved"]["cnt"] = $curEmpLeaveInfo["LateApproved"]["cnt"] + 1;
						}
						
						if($attendanceinfo["isExceededBreak"] == 1)
						{
							$curEmpLeaveInfo["BreakExceedApproved"]["cnt"] = $curEmpLeaveInfo["BreakExceedApproved"]["cnt"] + 1;
						}
					}
					
					$strColor = "";
					if(isset($arrLeaveColor[$displayStr]))
						$strColor = $arrLeaveColor[$displayStr];
					
					$tpl->set_var("fontcolor",$fontcolor);
					$tpl->set_var("strColor",$strColor);
					$tpl->set_var("attendance_displaystr",$displayStr);
					$tpl->set_var("tip_info",$late_time);
					$tpl->parse("FillEmployeeAttendanceBlock",true);
				}
			}
			
			if(count($curEmpLeaveInfo) > 0)
			{
				foreach($curEmpLeaveInfo as $curLeave)
				{
					$tpl->set_var("attendance_displaystr",$curLeave["cnt"]);
					$tpl->set_var("tip_info","");
					$tpl->set_var("fontcolor","");
					$tpl->set_var("strColor","");
					$tpl->parse("FillEmployeeAttendanceBlock",true);
				}
			}
			
			$tpl->parse("FillAttendanceInformation",true);
		}
	}

	$arrGetAllManagers = $objAttendance->fnGetAllManagers();
	$arrGetAllCEO = $objAttendance->fnGetAllCEO();
	$arrGetAllTeamLeader = $objAttendance->fnGetAllTeamLeaders();
	$arrShifts = $objShifts->fnGetAllShifts();
	
	$arrGetAllManagers = array_merge($arrGetAllCEO, $arrGetAllManagers);
	
	$tpl->set_var("FillManagers","");
	foreach($arrGetAllManagers as $managers)
	{
		$tpl->SetAllValues($managers);
		$tpl->parse("FillManagers",true);
	}

	$tpl->set_var("FillTeamLeader","");
	foreach($arrGetAllTeamLeader as $teamleaders)
	{
		$tpl->SetAllValues($teamleaders);
		$tpl->parse("FillTeamLeader",true);
	}
	
	$tpl->set_var("FillShiftInformation","");
	foreach($arrShifts as $curShift)
	{
		$tpl->set_var("shift_id",$curShift["id"]);
		$tpl->set_var("shift_name",$curShift["title"]." [".$curShift["starttime"]." - ".$curShift["endtime"]."]");
		$tpl->parse("FillShiftInformation",true);
	}

	$tpl->set_var("DisplayYearBlock","");
	if(count($arrYear) > 0)
	{
		foreach($arrYear as $curYr)
		{
			$tpl->set_var("curyr",$curYr);
			$tpl->parse("DisplayYearBlock",true);
		}
	}

	$tpl->pparse('main',false);
?>
