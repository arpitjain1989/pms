<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('emergency_shift_movement.html','main_container');

	$PageIdentifier = "EmergencyShiftMovement";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Add Emergency Shift Movement");
	$breadcrumb = '<li><a href="emergency_shift_movement_list.php">Manage Emergency Shift Movement</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add Emergency Shift Movement</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.employee.php');
	include_once('includes/class.shift_movement.php');
	include_once('includes/class.roster.php');
	include_once('includes/class.shifts.php');

	$objEmployee = new employee();
	$objShiftMovement = new shift_movement();
	$objRoster = new roster();
	$objShifts = new shifts();

	$curtime = strtotime("+5 minutes");

	$tpl->set_var("currenthour",date('h', $curtime));
	$tpl->set_var("currentminute",date('i', $curtime));
	$tpl->set_var("currentampm",date('a', $curtime));

	$employeeInfo = $objEmployee->fnGetEmployeeDetailById($_SESSION["id"]);
	if(isset($employeeInfo["name"]))
	{
		$tpl->set_var("reportinghead",$employeeInfo["name"]);
	}

	$tpl->set_var("movement_date",Date('Y-m-d'));
	$tpl->set_var("previous_date",Date('Y-m-d', strtotime('-1 day')));

	$shiftid = $employeeInfo["shiftid"];

	$shift = $objRoster->fnGetRosteredShiftByUserAndDate($_SESSION["id"], Date('Y-m-d'));
	if($shift != "")
	{
		$shiftid = $shift;
	}

	$shiftinfo = $objShifts->fnGetShiftById($shiftid);
	
	if($shiftinfo["endtime"] < $shiftinfo["starttime"])
		$dt = date('Y-m-d',strtotime('+1 day', strtotime(Date('Y-m-d'))));
	else
		$dt = date('Y-m-d');

	$shiftTime = date('Y-m-d H:i',strtotime('+2 hours', strtotime($dt." ".$shiftinfo["endtime"].":00")));

	if(isset($_POST["action"]) && trim($_POST["action"]) == "ShiftMovement")
	{
		if($shiftTime < date('Y-m-d H:i'))
		{
			header("Location: emergency_shift_movement_list.php?info=errnotallowed");
			exit;
		}

		$shiftmovement_status = $objShiftMovement->fnSaveEmergencyShiftMovement($_POST);

		if($shiftmovement_status == 1)
		{
			header("Location: emergency_shift_movement_list.php?info=success");
			exit;
		}
		else if($shiftmovement_status == 0)
		{
			header("Location: emergency_shift_movement_list.php?info=err");
			exit;
		}
		else if($shiftmovement_status == -1)
		{
			header("Location: emergency_shift_movement_list.php?info=alreadyexist");
			exit;
		}
		else if($shiftmovement_status == -2)
		{
			header("Location: emergency_shift_movement_list.php?info=admexist");
			exit;
		}
	}

	/* Display hours */
	$tpl->set_var("FillHoursBlock","");
	for($i = 1; $i<13; $i++)
	{
		$tpl->set_var("hours",str_pad($i,2,'0',STR_PAD_LEFT));
		$tpl->parse("FillHoursBlock",true);
	}

	/* Display hours */
	$tpl->set_var("FillMinutesBlock","");
	for($i = 0; $i<60; $i++)
	{
		$tpl->set_var("minutes",str_pad($i,2,'0',STR_PAD_LEFT));
		$tpl->parse("FillMinutesBlock",true);
	}

	$tpl->set_var("FillEmployeesBlock","");
	//$arrEmployees = $objEmployee->fnGetEmployeesByReportingHead($_SESSION["id"]);
	$arrEmployees = $objEmployee->fnGetAllEmployeesDetails($_SESSION["id"]);
	
	$arrtemp = array();

	/* Get Delegated Manager id */
	$arrDelegatedManagerId = $objEmployee->fnGetDelegateManagerId($_SESSION['id']);

	if(count($arrDelegatedManagerId) > 0 )
	{
		foreach($arrDelegatedManagerId as $delegatesManagerIds)
		{
			$arrtemp = $objEmployee->fnGetAllEmployeesDetails($delegatesManagerIds);
			$arrEmployees = $arrEmployees + $arrtemp;
		}
	}

	/* Get delegated teamleader id */
	$arrDelegatedTeamLeaderId = $objEmployee->fnGetDelegateEmployeeId($_SESSION['id']);	
	
	if(count($arrDelegatedTeamLeaderId) > 0 )
	{
		foreach($arrDelegatedTeamLeaderId as $delegatesIds)
		{
			$arrtemp = $objEmployee->fnGetAllEmployeesDetails($delegatesIds);
			$arrEmployees = $arrEmployees + $arrtemp;
		}
	}
	
	/*if($_SESSION["designation"] == "6")
	{
		// Get Delegated Manager id 
		$arrDelegatedManagerId = $objEmployee->fnGetDelegateManagerId($_SESSION['id']);
		
		if(count($arrDelegatedManagerId) > 0 )
		{
			foreach($arrDelegatedManagerId as $delegatesManagerIds)
			{
				$arrtemp = $objEmployee->fnGetAllEmployeesDetails($delegatesManagerIds);
				$arrEmployees = $arrEmployees + $arrtemp;
			}
		}
	}
	else if ($_SESSION["designation"] == "7" || $_SESSION["designation"] == "13")
	{
		// Get delegated teamleader id 
		$arrDelegatedTeamLeaderId = $objEmployee->fnGetDelegateEmployeeId($_SESSION['id']);	
		
		if(count($arrDelegatedTeamLeaderId) > 0 )
		{
			foreach($arrDelegatedTeamLeaderId as $delegatesIds)
			{
				$arrtemp = $objEmployee->fnGetAllEmployeesDetails($delegatesIds);
				$arrEmployees = $arrEmployees + $arrtemp;
			}
		}
	}*/
	
	if(count($arrEmployees) > 0)
	{
		foreach($arrEmployees as $curEmployee)
		{
			$tpl->set_var("employeeid",$curEmployee["id"]);
			$tpl->set_var("employeename",$curEmployee["name"]);

			$tpl->parse("FillEmployeesBlock",true);
		}
	}

	$tpl->pparse('main',false);
?>
