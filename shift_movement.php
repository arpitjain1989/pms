<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('shift_movement.html','main_container');

	$PageIdentifier = "ShiftMovement";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Add Shift Movement");
	$breadcrumb = '<li><a href="shift_movement_list.php">Manage Shift Movement</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add Shift Movement</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	$tpl->set_var("curdate",Date('Y-m-d'));
	$tpl->set_var("curtime",Date('H:i'));

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

	$employeeInfo = $objEmployee->fnGetEmployeeDetailById($_SESSION["teamleader"]);

	$arrUncompensatedShiftMovement = $objShiftMovement->fnGetUncompensatedShiftMovementsByUser($_SESSION["id"]);

	//print_r($arrUncompensatedShiftMovement);

	if(count($arrUncompensatedShiftMovement) > 0)
	{
		header("Location: shift_movement_list.php?info=erruncompensated");
		exit;
	}

	if(!$objShiftMovement->fnCheckPending())
	{
		header("Location: shift_movement_list.php?info=pendingerr");
		exit;
	}
	//print_r($employeeInfo);

	$shiftid = $employeeInfo["shiftid"];

	$shift = $objRoster->fnGetRosteredShiftByUserAndDate($_SESSION["id"], Date('Y-m-d'));
	if($shift != "")
	{
		$shiftid = $shift;
	}

	$shiftinfo = $objShifts->fnGetShiftById($shiftid);

	$shiftTime = date('H:i',strtotime('+1 hours', strtotime(Date('Y-m-d')." ".$shiftinfo["starttime"].":00")));

	$tpl->set_var("shiftTime", $shiftTime);

	if(isset($employeeInfo["name"]))
	{
		$tpl->set_var("reportinghead",$employeeInfo["name"]);
	}

	if(isset($_POST["action"]) && trim($_POST["action"]) == "ShiftMovement")
	{
		if($_POST["movement_date"] == Date("Y-m-d"))
		{
			if($shiftTime < date('H:i'))
			{
				header("Location: shift_movement_list.php?info=errnotallowed");
				exit;
			}
		}

		$shiftmovement_status = $objShiftMovement->fnSaveShiftMovement($_POST);

		if($shiftmovement_status == 1)
		{
			header("Location: shift_movement_list.php?info=success");
			exit;
		}
		else if($shiftmovement_status == 0)
		{
			header("Location: shift_movement_list.php?info=err");
			exit;
		}
		else if($shiftmovement_status == -1)
		{
			header("Location: shift_movement_list.php?info=alreadyexist");
			exit;
		}
		else if($shiftmovement_status == -2)
		{
			header("Location: shift_movement_list.php?info=pendingerr");
			exit;
		}
		else if($shiftmovement_status == -3)
		{
			header("Location: shift_movement_list.php?info=admexist");
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

	$tpl->pparse('main',false);
?>
