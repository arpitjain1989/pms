<?php
	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('insufficient_workhours.html','main_container');

	$PageIdentifier = "WorkhoursCalendar";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Insufficient Work Hours");
	$breadcrumb = '<li class="active">Manage Insufficient Work Hours</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.attendance.php');
	$objAttendance = new attendance();

	include_once('includes/class.employee.php');
	$objEmployee = new employee();
	
	$arrInsufficientWorkHours = $objAttendance->fnGetInsufficientWorkHours($_REQUEST["date"]);

	$tpl->set_var("date",$_REQUEST["date"]);

	//print_r($_REQUEST);die;

	if(isset($_REQUEST["search"]) && trim($_REQUEST["search"]) == "Attendance" && isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "" && isset($_REQUEST["date"]) && trim($_REQUEST["date"]) != "")
	{
		$arrEmployee = $objEmployee->fnGetEmployeeDetailById($_REQUEST["id"]);
		
		$_SESSION["SearchAttendance"]["issingle"] = true;
		
		if(count($arrEmployee))
		{
			$arrDt = explode("-",$_REQUEST["date"]);
			if(count($arrDt) == 3)
			{
				$_SESSION["SearchAttendance"]["year"] = $arrDt[0];
				$_SESSION["SearchAttendance"]["month"] = $arrDt[1];
			}

			$_SESSION["SearchAttendance"]["reporting_head"] = $objEmployee->fnGetReportingHeadId($arrEmployee["id"]);
			$_SESSION["SearchAttendance"]["team_member"] = $arrEmployee["id"];

			/*if($arrEmployee["designation"] == "6")
			{
				$_SESSION["SearchAttendance"]["manager"] = $arrEmployee["id"];
			}
			else if($arrEmployee["designation"] == "7" || $arrEmployee["designation"] == "13")
			{
				$_SESSION["SearchAttendance"]["teamleader"] = $arrEmployee["id"];
				$_SESSION["SearchAttendance"]["manager"] = $objEmployee->fnGetReportingHeadId($arrEmployee["id"]);
			}
			else
			{
				$_SESSION["SearchAttendance"]["agents"] = $arrEmployee["id"];

				$teamLeaderId = $objEmployee->fnGetReportingHeadId($arrEmployee["id"]);
				$arrTeamLeader = $objEmployee->fnGetEmployeeDetailById($teamLeaderId);

				$_SESSION["SearchAttendance"]["teamleader"] = $teamLeaderId;
				$_SESSION["SearchAttendance"]["manager"] = $objEmployee->fnGetReportingHeadId($arrTeamLeader["id"]);
			}*/
		}
		header("Location: report_attendance.php");
		exit;
	}

	if(isset($_POST["action"]) && trim($_POST["action"]) == "SaveWorkHours")
	{
		foreach($_POST["attendanceid"] as $curattendance)
		{
			$arrAttendance["id"] = $curattendance;
			if(isset($_POST["chkapprove_".$curattendance]))
				$arrAttendance["ishoursapproved"] = 1;
			else
				$arrAttendance["ishoursapproved"] = 0;
			
			/*$arrAttendance["additionaltime"] = $_POST["additionalminutes_".$curattendance];*/
			
			$objAttendance->fnUpdateAttendances($arrAttendance);
		}
		
		header("Location: workhours_calendar.php?info=succ");
		exit;
	}

	$tpl->set_var("DisplayInsufficientWorkHours","");
	$tpl->set_var("DisplayNoInsufficientWorkHours","");
	$tpl->set_var("DisplayWorkHours","");
	
	if(count($arrInsufficientWorkHours) > 0)
	{
		foreach($arrInsufficientWorkHours as $curworkhours)
		{
			$tpl->set_var("employeename",$curworkhours["name"]);
			$tpl->set_var("workhours",$curworkhours["workhours"]);
			$tpl->set_var("attendanceid",$curworkhours["attendanceid"]);
			$tpl->set_var("employeeid",$curworkhours["employeeid"]);
			$strchecked = "";
			if($curworkhours["ishoursapproved"] == "1")
				$strchecked = "checked='checked'";

			$tpl->set_var("strchecked",$strchecked);
			//$tpl->set_var("additionaltime",$curworkhours["additionaltime"]);

			/*$reason = "";
			$br = "";
			if($curworkhours["is_late"] == "1")
			{
				$reason .= "Late comming: ".$curworkhours["late_time"];
				$br = "<br/>";
			}
			if($curworkhours["isExceededBreak"] == "1")
			{
				$reason .= $br . "Break exceed: ".$curworkhours["break_exceed_time"];
			}*/

			$tpl->set_var("LateTime",$curworkhours["late_time"]);

			$tpl->set_var("BreakExceed",$curworkhours["break_exceed_time"]);

			//$tpl->set_var("reason",$reason);
			
			$tpl->parse("DisplayWorkHours",true);
		}
		$tpl->parse("DisplayInsufficientWorkHours",false);
	}
	else
	{
		$tpl->parse("DisplayNoInsufficientWorkHours",false);
	}
	
	$tpl->pparse('main',false);
?>
