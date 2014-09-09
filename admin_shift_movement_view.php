<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('admin_shift_movement_view.html','main_container');

	$PageIdentifier = "AdminShiftMovement";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Shift Movement");
	$breadcrumb = '<li><a href="admin_shift_movement_list.php">Manage Shift Movement</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Shift Movement</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.shift_movement.php');

	$objShiftMovement = new shift_movement();
	
	$tpl->set_var("DisplayMovementInformationBlock","");
	$tpl->set_var("DisplayNoMovementBlock","");
	
	if(isset($_REQUEST["id"]))
	{
		$MovementInfo = $objShiftMovement->fnAdminShiftMovementById($_REQUEST["id"]);

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
