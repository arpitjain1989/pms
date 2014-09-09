<?php 

	include('common.php');
	$tpl = new Template($app_path);

	ini_set("max_execution_time",300);

	$tpl->load_file('print_attendance_report_summary.html','main');

	$curDate = Date('Y-m-d');
	$curYear = Date('Y');

	$arrYear = array($curYear, $curYear-1);

	include_once('includes/class.attendance.php');
	$objAttendance = new attendance();

	include_once('includes/class.leave.php');
	$objLeave = new leave();

	include_once('includes/class.employee.php');
	$objEmployee = new employee();
	
	include_once('includes/class.shifts.php');
	$objShifts = new shifts();
	
	if(!isset($_SESSION["SearchAttendance"]["month"]))
		$_SESSION["SearchAttendance"]["month"] = Date('m');
	if(!isset($_SESSION["SearchAttendance"]["year"]))
		$_SESSION["SearchAttendance"]["year"] = $curYear;

	/*if(isset($_SESSION["SearchAttendance"]["month"]))
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
		$tpl->set_var("shiftid", $_SESSION["SearchAttendance"]["shiftid"]);*/

	$arrEmployee = $objEmployee->fnGetEmployees($_SESSION["SearchAttendance"]["reporting_head"], $_SESSION["SearchAttendance"]["team_member"],$_SESSION["SearchAttendance"]["shiftid"],$_SESSION["SearchAttendance"]["month"],$_SESSION["SearchAttendance"]["year"], $_SESSION["SearchAttendance"]["issingle"]);
	
	$arrLeave = $objLeave->fnGetAllLeaveTypes();
	$arrEmpLeave = array();
	$arrEmpLeave["P"] = array("name" => "P", "cnt" => 0);
	$arrEmpLeave["Late"] = array("name" => "PLT", "cnt" => 0);
	$arrEmpLeave["BreakExceed"] = array("name" => "Break exceed", "cnt" => 0);
	$arrEmpLeave["BreakExceedApproved"] = array("name" => "Break exceed Approved", "cnt" => 0);
	
	if(count($arrLeave))
	{
		foreach($arrLeave as $curLeave)
		{
			if($curLeave["title"] != "SC")
			{
				$arrEmpLeave[$curLeave["id"]] = array("name" => $curLeave["title"], "cnt" => 0);
			}
		}
	}
	
	$startdate = $_SESSION["SearchAttendance"]["year"]."-".$_SESSION["SearchAttendance"]["month"]."-01";
	$enddate = date("Y-m-t", strtotime($startdate));

	$tpl->set_var("headingdate",date('F Y', strtotime($startdate)));

	$arrDates = array();
	
	while($startdate <= $enddate)
	{
		$arrDates[$startdate] = $startdate;
		
		/*$tpl->set_var("DisplayDate",date('d D', strtotime($startdate)));
		$tpl->parse("FillSearchDateBlock",true);*/
		
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
					
					if(isset($attendanceinfo["is_late"]) && $attendanceinfo["is_late"] == 1)
					{
						$late_time  = "Late: " . $attendanceinfo["late_time"];
						
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
					
					if(isset($attendanceinfo["isExceededBreak"]) && $attendanceinfo["isExceededBreak"] == 1)
						$curEmpLeaveInfo["BreakExceed"]["cnt"] = $curEmpLeaveInfo["BreakExceed"]["cnt"] + 1;
					
					if(isset($attendanceinfo["ishoursapproved"]) && $attendanceinfo["ishoursapproved"] == "1")
					{
						if(isset($attendanceinfo["isExceededBreak"]) && $attendanceinfo["isExceededBreak"] == 1)
						{
							$curEmpLeaveInfo["BreakExceedApproved"]["cnt"] = $curEmpLeaveInfo["BreakExceedApproved"]["cnt"] + 1;
						}
					}
					
					/*$strColor = "";
					if(isset($arrLeaveColor[$displayStr]))
						$strColor = $arrLeaveColor[$displayStr];*/
					
					/*$tpl->set_var("strColor",$strColor);
					$tpl->set_var("attendance_displaystr",$displayStr);
					$tpl->set_var("tip_info",$late_time);
					$tpl->parse("FillEmployeeAttendanceBlock",true);*/
				}
			}
			
			if(count($curEmpLeaveInfo) > 0)
			{
				foreach($curEmpLeaveInfo as $curLeave)
				{
					$tpl->set_var("attendance_displaystr",$curLeave["cnt"]);
					$tpl->parse("FillEmployeeAttendanceBlock",true);
				}
			}
			
			$tpl->parse("FillAttendanceInformation",true);
		}
	}

	$tpl->pparse('main',false);
?>
