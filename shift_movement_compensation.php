<?php 

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('shift_movement_compensation.html','main_container');

	$PageIdentifier = "ShiftMovementCompensation";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Add Shift Movement Compensation");
	$breadcrumb = '<li><a href="shift_movement_compensation_list.php">Manage Compensation</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add   Compensation</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.shift_movement.php');
	
	$objShiftMovement = new shift_movement();
	
	if(isset($_POST["action"]) && trim($_POST["action"]) == "ShiftMovementCompensation")
	{
		//print_r($_POST);
		
		if($objShiftMovement->fnSaveShiftMovementCompensation($_POST))
		{
			header("Location: shift_movement_compensation_list.php?info=success");
			exit;
		}
		else
		{
			header("Location: shift_movement_compensation_list.php?info=err");
			exit;
		}
	}
	
	$tpl->set_var("compensation_date",Date('Y-m-d'));
	$tpl->set_var("previous_date",Date('Y-m-d', strtotime('-1 day')));
	$tpl->set_var("userid",$_SESSION["id"]);

	$ShiftMovements = $objShiftMovement->fnGetPendingCompensationMovementByUser($_SESSION["id"]);

	$tpl->set_var("FillShiftMovementDate","");
	if(count($ShiftMovements) > 0)
	{
		foreach($ShiftMovements as $currentShift)
		{
			$tpl->set_var("shift_movement_id",$currentShift["id"]);
			$tpl->set_var("shift_movement_date",$currentShift["movement_date"]);
			
			$tpl->parse("FillShiftMovementDate",true);
		}
	}
	else
	{
		header("Location: shift_movement_compensation_list.php?info=nopending");
		exit;
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
