<?php 

	include('common.php');
	$tpl = new Template($app_path);
	$tpl->load_file('template.html','main');
	$tpl->load_file('shift_movement_list.html','main_container');

	$PageIdentifier = "ShiftMovement";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Shift Movements");
	$breadcrumb = '<li class="active">Manage Shift Movements</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	if(isset($_SESSION["usertype"]) && trim($_SESSION["usertype"]) == "admin")
	{
		/* If logged in as admin, do not show add leave form option */
		$tpl->set_var("ShiftMovementAddBlock","");
	}
	
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
				$message = "No shift movement pending for this month. Cannot add the shift movement";
				break;
			case 'errnotallowed':
				$messageClass = "alert-error";
				$message = "Shift movement cannot be added for such a short notice. Please apply well in advance";
				break;
			case 'alreadyexist':
				$messageClass = "alert-error";
				$message = "Shift movement cannot be added. Leave / shift movement already exists for this date.";
				break;
			case 'admexist':
				$messageClass = "alert-error";
				$message = "Shift movement cannot be added. Approved LWP added by admin.";
				break;
			case 'erruncompensated':
				$messageClass = "alert-error";
				$message = "Previous shift movement are not compensated. Please compensate the previous shift movements to add another shift movement.";
				break;
			case 'pendingerr':
				$messageClass = "alert-error";
				$message = "You have a shift movement pending. Cannot add another shift movement.";
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
	$arrMovements = $objShiftMovement->fnUserShiftMovement($_SESSION["id"]);
	
	$tpl->set_var("FillShiftMovements","");
	if(count($arrMovements))
	{
		foreach($arrMovements as $MovementInfo)
		{
			$clsbold = "";
			if($MovementInfo['isemergency'] == '1')
			{
				$clsbold = "bold";
			}
			$tpl->set_var("clsbold",$clsbold);
			
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
