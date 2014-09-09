<?php 

	include('common.php');
	$tpl = new Template($app_path);

	ini_set("max_execution_time",0);

	$tpl->load_file('template.html','main');
	$tpl->load_file('report_attendance.html','main_container');

	$PageIdentifier = "TeamAttendanceReport";
	include_once('userrights.php');

	$curDate = Date('Y-m-d');
	$curYear = Date('Y');

	$arrYear = array($curYear+1, $curYear, $curYear-1);

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
		$_SESSION["SearchAttendance"]["reporting_head"] = $_POST["reporting_head"];
		$_SESSION["SearchAttendance"]["team_member"] = $_POST["team_member"];
		/*$_SESSION["SearchAttendance"]["agents"] = $_POST["agents"];*/
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
	
	/* set default as a single manager to load the report faster */
	if(!isset($_SESSION["SearchAttendance"]["reporting_head"]))
		$_SESSION["SearchAttendance"]["reporting_head"] = 2;

	if(isset($_SESSION["usertype"]) && trim($_SESSION["usertype"]) == "employee")
	{
		$_SESSION["SearchAttendance"]["reporting_head"] = $_SESSION["id"];
	}
		
	/*if(isset($_SESSION["SearchAttendance"]["start_date"]))
		$tpl->set_var("start_date", $_SESSION["SearchAttendance"]["start_date"]);
	if(isset($_SESSION["SearchAttendance"]["end_date"]))
		$tpl->set_var("end_date", $_SESSION["SearchAttendance"]["end_date"]);*/

	if(isset($_SESSION["SearchAttendance"]["month"]))
		$tpl->set_var("month", $_SESSION["SearchAttendance"]["month"]);
	else
		$_SESSION["SearchAttendance"]["month"] = 0;

	if(isset($_SESSION["SearchAttendance"]["year"]))
		$tpl->set_var("year", $_SESSION["SearchAttendance"]["year"]);
	else
		$_SESSION["SearchAttendance"]["year"] = 0;
		
	if(isset($_SESSION["SearchAttendance"]["reporting_head"]))
		$tpl->set_var("reporting_head", $_SESSION["SearchAttendance"]["reporting_head"]);
	else
		$_SESSION["SearchAttendance"]["reporting_head"] = 0;

	if(isset($_SESSION["SearchAttendance"]["team_member"]))
		$tpl->set_var("team_member", $_SESSION["SearchAttendance"]["team_member"]);
	else
	{
		$_SESSION["SearchAttendance"]["team_member"] = $objEmployee->fnGetFirstTeamMember($_SESSION["SearchAttendance"]["reporting_head"], $_SESSION["SearchAttendance"]["year"], $_SESSION["SearchAttendance"]["month"]);
		$tpl->set_var("team_member", $_SESSION["SearchAttendance"]["team_member"]);
		//$_SESSION["SearchAttendance"]["team_member"] = 0;
	}
		
	/*if(isset($_SESSION["SearchAttendance"]["agents"]))
		$tpl->set_var("agents", $_SESSION["SearchAttendance"]["agents"]);
	else
		$_SESSION["SearchAttendance"]["agents"] = 0;*/
		
	if(isset($_SESSION["SearchAttendance"]["shiftid"]))
		$tpl->set_var("shiftid", $_SESSION["SearchAttendance"]["shiftid"]);
	else
		$_SESSION["SearchAttendance"]["shiftid"] = 0;

	if(!isset($_SESSION["SearchAttendance"]["issingle"]))
		$_SESSION["SearchAttendance"]["issingle"] = false;
		
	/*$arrEmployee = $objEmployee->fnGetEmployees($_SESSION["SearchAttendance"]["manager"], $_SESSION["SearchAttendance"]["team_member"], $_SESSION["SearchAttendance"]["agents"],$_SESSION["SearchAttendance"]["shiftid"],$_SESSION["SearchAttendance"]["start_date"],$_SESSION["SearchAttendance"]["end_date"]);*/
	
	$arrEmployee = $objEmployee->fnGetEmployees($_SESSION["SearchAttendance"]["reporting_head"], $_SESSION["SearchAttendance"]["team_member"],$_SESSION["SearchAttendance"]["shiftid"],$_SESSION["SearchAttendance"]["month"],$_SESSION["SearchAttendance"]["year"], $_SESSION["SearchAttendance"]["issingle"]);
	
	$arrLeave = $objLeave->fnGetAllLeaveTypes();
	$arrEmpLeave = array();
	$arrEmpLeave["P"] = array("name" => "P", "cnt" => 0);
	$arrEmpLeave["Late"] = array("name" => "PLT", "cnt" => 0);
	//$arrEmpLeave["LateApproved"] = array("name" => "PLT Approved", "cnt" => 0);
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
	$i = 0;	
	while($startdate <= $enddate)
	{
		//echo $startdate; die;
		$currentDay = date('D',strtotime($startdate));
		$i++;
		if($currentDay == 'Mon')
		{
			$tpl->set_var("DisplayDate","Employee Name");
			$tpl->parse("FillSearchDateBlock",true);
		}
			
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
			//echo '<pre>'; print_r($curEmp);
			$tpl->set_var("employeename",$curEmp["name"]);
			
			$curEmpLeaveInfo = $arrEmpLeave;
			
			$tpl->set_var("FillEmployeeAttendanceBlock","");
			//echo count($arrDates);
			if(count($arrDates) > 0)
			{
				$i = 0;
				foreach($arrDates as $curdt)
				{
					//print_r($curdt);
					$currentDay = date('D',strtotime($curdt));
					$i++;
					if($currentDay == 'Mon')
					{
						$tpl->set_var("attendance_displaystr",$curEmp["name"]);
						$tpl->set_var("strColor","");
						$tpl->set_var("tip_info","");
						$tpl->set_var("fontcolor","");
						$tpl->parse("FillEmployeeAttendanceBlock",true);
					}
					$attendanceinfo = $objAttendance->fnGetAttendanceDetails($curdt, $curEmp["id"]);
					
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
						else if(isset($attendanceinfo["leave_id"]) && $attendanceinfo["leave_id"] != 0)
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
							
							if($attendanceinfo["leave_id"] == '17')
							{
								/* Fetch reason for special leave */
								$arrSpecialLeaveInfo = $objLeave->fnGetSpecialLeaveByUserAndDate($curEmp["id"], $curdt);
								if(isset($arrSpecialLeaveInfo["reason"]))
								{
									$late_time  .= "Reason for SPL: " . $arrSpecialLeaveInfo["reason"];
									$br = "<br/>";
								}
							}
						}
					}
					
					//$attendanceinfo["isLateAllowed"] == "0"
					if(isset($attendanceinfo["is_late"]) && $attendanceinfo["is_late"] == 1)
					{
						$late_time  .= $br . "Late: " . $attendanceinfo["late_time"];
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
					if(isset($attendanceinfo["isExceededBreak"]) && $attendanceinfo["isExceededBreak"] == 1)
					{
						$curEmpLeaveInfo["BreakExceed"]["cnt"] = $curEmpLeaveInfo["BreakExceed"]["cnt"] + 1;
						$fontcolor = "color:#0000CC; font-weight:bold;";
						
						$late_time  .= $br . "Break exceed: " . $attendanceinfo["break_exceed_time"];
						$br = "<br/>";
					}
					
					if(isset($attendanceinfo["ishoursapproved"]) && $attendanceinfo["ishoursapproved"] == "1")
					{
						$late_time  .= $br . "Approved";
						
						/*if(isset($attendanceinfo["is_late"]) && $attendanceinfo["is_late"] == 1)
						{
							$curEmpLeaveInfo["LateApproved"]["cnt"] = $curEmpLeaveInfo["LateApproved"]["cnt"] + 1;
						}*/
						
						if(isset($attendanceinfo["isExceededBreak"]) && $attendanceinfo["isExceededBreak"] == 1)
						{
							$curEmpLeaveInfo["BreakExceedApproved"]["cnt"] = $curEmpLeaveInfo["BreakExceedApproved"]["cnt"] + 1;
						}
					}
					
					$strColor = "";
					if(isset($arrLeaveColor[$displayStr]))
						$strColor = $arrLeaveColor[$displayStr];
										
					if(isset($attendanceinfo["attendance_remarks"]) && trim($attendanceinfo["attendance_remarks"]) != "")
						$late_time .= $br . "Remarks : ".nl2br($attendanceinfo["attendance_remarks"]);
					if($displayStr == "A" || $displayStr =="HA" )
					{
					
					$late_time .= $br . "Remarks : ".nl2br($attendanceinfo["text"]);
					}
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

	/*$arrGetAllManagers = $objAttendance->fnGetAllManagers();
	$arrGetAllCEO = $objAttendance->fnGetAllCEO();
	$arrGetAllTeamLeader = $objAttendance->fnGetAllTeamLeaders();
	
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
	}*/
	
	$tpl->set_var("DisplayReportingHeadHiddenBlock","");
	$tpl->set_var("DisplayReportingHeadBlock","");

	if(isset($_SESSION["usertype"]) && trim($_SESSION["usertype"]) == "employee")
	{
		$tpl->parse("DisplayReportingHeadHiddenBlock",false);
	}
	else
	{
		$arrReportingHead = $objAttendance->fnGetEmployees(Date('Y-m-d'));
		$tpl->set_var("FillReportingHeads","");
		foreach($arrReportingHead as $curReportingHead)
		{
			$tpl->set_var("reporting_head_id",$curReportingHead["employee_id"]);
			$tpl->set_var("reporting_head_name",$curReportingHead["employee_name"]);

			$tpl->parse("FillReportingHeads",true);
		}
		$tpl->parse("DisplayReportingHeadBlock",false);
	}
	
	$arrShifts = $objShifts->fnGetAllShifts();
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
