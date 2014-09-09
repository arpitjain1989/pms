<?php
	include('common.php');
	
	$tpl = new Template($app_path);
	
	$tpl->load_file('template.html','main');
	$tpl->load_file('shift_movement_cancel.html','main_container');
	
	$PageIdentifier = "ShiftMovementCancellation";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Cancel Shift Movements Request");
	$breadcrumb = '<li class="active">Cancel Shift Movements Request</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.employee.php');
	include_once('includes/class.shift_movement.php');
	
	$objEmployee = new employee();
	$objShiftMovement = new shift_movement();
	
	$tpl->set_var("DisplayMessageBlock","");
	
	if(isset($_POST['hdnAction']) && $_POST['hdnAction'] =='cancel')
	{
	//echo $_POST['id'];
		$changeStatus = $objShiftMovement->fnChangeStatus($_POST['id']);
		//echo $changeStatus;die;
		if($changeStatus)
		{
			header("Location: shift_movement_cancel.php?info=success");
		}
		else
		{
			header("Location: shift_movement_cancel.php?info=err");
		}
		//die;
	}
	
	if(isset($_REQUEST["info"]))
	{
		if($_REQUEST["info"] == 'success')
		{
			$messageClass = "alert-success";
			$message = "Shift movement cancel successfully";
		}
		else
		{
			$messageClass = "alert-error";
			$message = "Shift movement not cancel successfully";
		}
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);
			
			$tpl->parse("DisplayMessageBlock",false);	
		}
	}
	
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
	
	$ids = "0";
	if(count($arrEmployee) > 0)
	{
		$ids = implode(',',$arrEmployee);
	}

	//$arrMovements = $objShiftMovement->fnGetAllShiftMovementRequest($ids);
	$arrMovements = $objShiftMovement->fnGetAllShiftMovementToCancel($ids);

	$tpl->set_var("FillShiftMovements","");
	if(count($arrMovements) > 0)
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
			$tpl->setAllValues($MovementInfo);
			$tpl->parse("FillShiftMovements",true);
		}
	}
	
	$tpl->pparse('main',false);
?>
