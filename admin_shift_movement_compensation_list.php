<?php 

	include('common.php');
	$tpl = new Template($app_path);
	$tpl->load_file('template.html','main');
	$tpl->load_file('admin_shift_movement_compensation_list.html','main_container');

	$PageIdentifier = "AdminShiftMovementCompensation";
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
				$message = "Shift movement added successfully";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "No Shift movement pending for this month. Cannot add the shift movement";
				break;
			case 'errnotallowed':
				$messageClass = "alert-error";
				$message = "Shift movement cannot be added 2 hours after the shift. Please apply well in advance";
				break;
			case 'alreadyexist':
				$messageClass = "alert-error";
				$message = "Shift movement cannot be added. Leave / shift movement already exists for this date.";
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
	$arrMovements = $objShiftMovement->fnAdminShiftMovementCompensation();
	//echo '<pre>'; print_r($arrMovements); die;
	$tpl->set_var("FillShiftMovements","");
	if(count($arrMovements))
	{
		foreach($arrMovements as $MovementInfo)
		{
			if($MovementInfo['isCancel'] == '1')
			{
				$trclass = "red";
				$tpl->set_var("trclass",$trclass);
			}
			else if($MovementInfo['isCancel'] == '0')
			{
				$tpl->set_var("trclass","");
			}
			$tpl->SetAllValues($MovementInfo);
			
			$tpl->parse("FillShiftMovements",true);
		}
	}

	$tpl->pparse('main',false);
?>