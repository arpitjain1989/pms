<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('emergency_shift_movement_view.html','main_container');

	$PageIdentifier = "EmergencyShiftMovement";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Emergency Shift Movement");
	$breadcrumb = '<li><a href="emergency_shift_movement_list.php">Manage Emergency Shift Movement</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Emergency Shift Movement</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.shift_movement.php');

	$objShiftMovement = new shift_movement();
	
	$tpl->set_var("DisplayMovementInformationBlock","");
	$tpl->set_var("DisplayNoMovementBlock","");
	$tpl->set_var("DisplayDelegateTLBlock","");
	$tpl->set_var("DisplayDelegateManagerBlock","");
	
	if(isset($_REQUEST["id"]))
	{
		$MovementInfo = $objShiftMovement->fnUserEmergencyShiftMovementById($_REQUEST["id"]);
		
		if($MovementInfo)
		{
			$tpl->SetAllValues($MovementInfo);

			if($MovementInfo["delegatedtl_id"] != 0)
				$tpl->parse("DisplayDelegateTLBlock",false);

			if($MovementInfo["delegatedmanager_id"] != 0)
				$tpl->parse("DisplayDelegateManagerBlock",false);

			$tpl->parse("DisplayMovementInformationBlock",false);
		}
		else
		{
			$tpl->parse("DisplayNoMovementBlock",false);
		}
	}
	else
	{
		$tpl->parse("DisplayNoMovementBlock",false);
	}
	
	$tpl->pparse('main',false);
?>
