<?php
	include('common.php');

	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('shift_movement_cancel_view.html','main_container');

	$PageIdentifier = "ShiftMovementCancellation";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Shift Movement Request");
	$breadcrumb = '<li><a href="shift_movement_cancel.php">Manage Shift Movement Request</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Shift Movement Request</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.shift_movement.php');
	include_once('includes/class.employee.php');

	$objShiftMovement = new shift_movement();
	$objEmployee = new employee();

	if(isset($_POST["hShiftMovementRequest"]) && trim($_POST["hShiftMovementRequest"]) == "ShiftMovementRequest")
	{
		$status = $objShiftMovement->fnUpdateShiftMovement($_POST);

		if($status == 0)
		{
			header("location: shift_movement_request.php?info=err");
		}
		else if($status == -1)
		{
			header("location: shift_movement_request.php?info=errtime");
		}
		else
		{
			header("location: shift_movement_request.php?info=success&s=".$status);
		}
		exit;
	}

	$tpl->set_var("DisplayMovementInformationBlock","");
	$tpl->set_var("DisplayNoMovementBlock","");

	$tpl->set_var("TLStatusViewBlock","");
	$tpl->set_var("ManagersStatusViewBlock","");
	$tpl->set_var("DelegatedTLStatusViewBlock","");
	$tpl->set_var("DelegatedManagersStatusViewBlock","");
	$tpl->set_var("CancelButtonBlock","");

	if(isset($_REQUEST["id"]))
	{

		if($_SESSION['usertype'] == 'admin')
		{
			$arrEmployee = $objEmployee->fnGetAllemployees(0);
		}
		else
		{
			$arrEmployee = $objEmployee->fnGetAllemployees($_SESSION['id']);
		}

		$arrEmployee[] = "";

		if(count($arrEmployee) > 0)
		{
			$arrEmployee = array_filter($arrEmployee,'strlen');
		}

		$ids = "";
		if(count($arrEmployee) > 0)
		{
			$ids = implode(',',$arrEmployee);
		}

		$MovementInfo = $objShiftMovement->fnShiftMovementById($_REQUEST["id"], $ids);

		if($MovementInfo)
		{
			$tpl->SetAllValues($MovementInfo);
			
			if($MovementInfo["reportinghead1"] != "" && $MovementInfo["reportinghead1"] != "0")
			{
				$tpl->parse("TLStatusViewBlock",false);
			}

			if($MovementInfo["reportinghead2"] != "" && $MovementInfo["reportinghead2"] != "0")
			{
				$tpl->parse("ManagersStatusViewBlock",false);
			}

			if($MovementInfo["delegatedtl_id"] != "" && $MovementInfo["delegatedtl_id"] != "0")
			{
				$tpl->parse("DelegatedTLStatusViewBlock",false);
			}

			if($MovementInfo["delegatedmanager_id"] != "" && $MovementInfo["delegatedmanager_id"] != "0")
			{
				$tpl->parse("DelegatedManagersStatusViewBlock",false);
			}

			if($MovementInfo['isCancel'] == 0)
			{
				$tpl->parse("CancelButtonBlock",true);
			}
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
