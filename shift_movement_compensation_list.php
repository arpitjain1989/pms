<?php 

	include('common.php');
	$tpl = new Template($app_path);
	$tpl->load_file('template.html','main');
	$tpl->load_file('shift_movement_compensation_list.html','main_container');

	$PageIdentifier = "ShiftMovementCompensation";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Shift Movements Compensation");
	$breadcrumb = '<li class="active">Manage Shift Movements Compensation</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	$message = "";
	$messageClass = "";
	
	$tpl->set_var("DisplayMessageBlock","");
	
	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'success':
				$messageClass = "alert-success";
				$message = "Shift movement compensation added successfully";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Cannot compensate for shift movement after 7 days.";
				break;
			case 'nopending':
				$messageClass = "alert-error";
				$message = "No shift movements compensations are pending or shift movement compensation request is pending";
				break;
		}
		
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);
			
			$tpl->parse("DisplayMessageBlock",false);	
		}
	}
	
	include_once('includes/class.shift_movement.php');
	
	$objShiftMovement = new shift_movement();
	
	$arrCompensation = $objShiftMovement->fnUserShiftMovementCompensation();
	
	$tpl->set_var("FillShiftMovementsCompensation","");
	if(count($arrCompensation))
	{
		foreach($arrCompensation as $CompensationInfo)
		{
			$tpl->SetAllValues($CompensationInfo);
			$tpl->parse("FillShiftMovementsCompensation",true);
		}
	}

	$tpl->pparse('main',false);
?>
