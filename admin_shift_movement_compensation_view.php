<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('admin_shift_movement_compensation_view.html','main_container');

	$PageIdentifier = "AdminShiftMovement";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Shift Movement Compensation");
	$breadcrumb = '<li><a href="admin_shift_movement_compensation_list.php">Manage Shift Movement Compensation</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View SM Compensation</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.shift_movement.php');

	$objShiftMovement = new shift_movement();
	
	$tpl->set_var("DisplayMovementInformationBlock","");
	$tpl->set_var("DisplayNoMovementBlock","");
	
	if(isset($_REQUEST["id"]))
	{
		$MovementInfo = $objShiftMovement->fnAdminShiftMovementCompensationById($_REQUEST["id"]);
		//echo '<pre>'; print_r($MovementInfo);
		if($MovementInfo)
		{
			$tpl->SetAllValues($MovementInfo);
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
