<?php

	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('admin_shift_movement_report.html','main_container');
	
	$PageIdentifier = "AdminLeavesReport";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Admin Shift Movement Report");
	$breadcrumb = '<li class="active">Admin Shift Movement Report</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once("includes/class.shift_movement.php");
	
	$objShiftMovement = new shift_movement();

	$curDate = Date("Y-m-d");

	//$curYear = Date('Y');
	//$curMonth = Date('m');

	/* Get current year and previous year */
	//$arrYear = array($curYear, $curYear-1);

	/* Search late comings */
	if(isset($_POST["action"]) && trim($_POST["action"]) == "shiftMovementSearch")
	{
		$_SESSION["AdminShiftMovementReport"]["search_from_date"] = $_POST["search_from_date"];
		$_SESSION["AdminShiftMovementReport"]["search_to_date"] = $_POST["search_to_date"];
		$_SESSION["AdminShiftMovementReport"]["movement_compensation"] = $_POST["movement_compensation"];
		
		header("Location: admin_shift_movement_report.php");
		exit;
	}
	
	if(!isset($_SESSION["AdminShiftMovementReport"]["search_from_date"]))
		$_SESSION["AdminShiftMovementReport"]["search_from_date"] = $curDate;

	if(!isset($_SESSION["AdminShiftMovementReport"]["search_to_date"]))
		$_SESSION["AdminShiftMovementReport"]["search_to_date"] = $curDate;
		
	if(!isset($_SESSION["AdminShiftMovementReport"]["movement_compensation"]))
		$_SESSION["AdminShiftMovementReport"]["movement_compensation"] = '1';
	
	$search_to_date = $_SESSION["AdminShiftMovementReport"]["search_to_date"];
	$search_from_date = $_SESSION["AdminShiftMovementReport"]["search_from_date"];
	$movement_compensation = $_SESSION["AdminShiftMovementReport"]["movement_compensation"];
	
	$tpl->set_var("search_to_date",$search_to_date);
	$tpl->set_var("search_from_date",$search_from_date);
	$tpl->set_var("movement_compensation",$movement_compensation);

	/*
	$shiftMovementFromAttendance = $objShiftMovement->fnGetAllAdminShiftMovementsEmployee($year, $month);

	//echo '<pre>'; print_r($shiftMovementFromAttendance); die;
	$tpl->set_var("DisplayShiftMovement","");
	$tpl->set_var("NoDisplayShiftMovement","");
	
	$tpl->set_var("FillMovementInformation","");

	

	if(count($shiftMovementFromAttendance) > 0)
	{
		$tpl->set_var("count_sm",count($shiftMovementFromAttendance));
		foreach($shiftMovementFromAttendance as $shiftMovementAttendance)
		{
			//echo '<pre>'; echo 'key:'.$key; print_r($shiftMovementAttendance);
			if(isset($shiftMovementAttendance["e_name"]) && $shiftMovementAttendance["e_name"]!= '')
			{
				$tpl->set_var("emp_name",$shiftMovementAttendance["e_name"]);
			}
			if(isset($shiftMovementAttendance["m_date"]))
			{
				$tpl->set_var("mov_date",$shiftMovementAttendance["m_date"]);
			}
			if(isset($shiftMovementAttendance["com_date"]))
			{
				$tpl->set_var("comp_date",$shiftMovementAttendance["com_date"]);
			}
			if(isset($shiftMovementAttendance["name"]))
			{
				$tpl->set_var("employeename",$shiftMovementAttendance["name"]);
			}

			if(isset($shiftMovementAttendance['appr_tl']) && $shiftMovementAttendance['appr_tl'] == '1')
			{
				$tpl->set_var("status_tl","Approved");
			}
			else if(isset($shiftMovementAttendance['appr_tl']) && $shiftMovementAttendance['appr_tl'] == '2')
			{
				$tpl->set_var("status_tl","Un-approved");
			}
			else
			{
				$tpl->set_var("status_tl","pending");
			}
			
			$tpl->parse("FillMovementInformation",true);
		}
	}
	else
	{
		$tpl->parse("NoDisplayShiftMovement",true);
		$tpl->set_var("count_sm",'0');
	}
*/
	/* Fill year dropdown */
	/*$tpl->set_var("DisplayYearBlock","");
	if(count($arrYear) > 0)
	{
		foreach($arrYear as $yr)
		{
			$tpl->set_var("curyr", $yr);
			
			$tpl->parse("DisplayYearBlock",true);
		}
	}*/
	
	$tpl->pparse('main',false);

?>
