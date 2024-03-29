<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('admin_shift_movement_compensation.html','main_container');

	$PageIdentifier = "AdminShiftMovementCompensation";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Add Shift Movement Compensation");
	$breadcrumb = '<li><a href="admin_shift_movement_compensation_list.php">Manage Shift Movement Compensation</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add SM Compensation</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.employee.php');
	include_once('includes/class.shift_movement.php');
	include_once('includes/class.roster.php');
	include_once('includes/class.shifts.php');

	$objEmployee = new employee();
	$objShiftMovement = new shift_movement();
	$objRoster = new roster();
	$objShifts = new shifts();
/*
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
			header("Location: admin_shift_movement_list.php?info=errnotallowed");
			exit;
		}

		$shiftmovement_status = $objShiftMovement->fnSaveAdminShiftMovement($_POST);

		if($shiftmovement_status == 1)
		{
			header("Location: admin_shift_movement_list.php?info=success");
			exit;
		}
		else if($shiftmovement_status == 0)
		{
			header("Location: admin_shift_movement_list.php?info=err");
			exit;
		}
		else if($shiftmovement_status == -1)
		{
			header("Location: admin_shift_movement_list.php?info=alreadyexist");
			exit;
		}
	}
*/
	$tpl->set_var("compensation_date",Date('Y-m-d'));
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
	$arrEmployees = $objEmployee->fnGetAllEmployeesDetails(0);
	if(count($arrEmployees) > 0)
	{
		foreach($arrEmployees as $curEmployee)
		{
			$tpl->set_var("employeeid",$curEmployee["id"]);
			$tpl->set_var("employeename",$curEmployee["name"]);

			$tpl->parse("FillEmployeesBlock",true);
		}
	}

	if(isset($_POST["action"]) && trim($_POST["action"]) == "adminShiftMovementCompensation")
	{
		//print_r($_POST); die;
		
		if($objShiftMovement->fnSaveAdminShiftMovementCompensation($_POST))
		{
			header("Location: admin_shift_movement_compensation_list.php?info=success");
			exit;
		}
		else
		{
			header("Location: admin_shift_movement_compensation_list.php?info=err");
			exit;
		}
	}

	$tpl->pparse('main',false);
?>
