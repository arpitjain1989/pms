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

	$curYear = Date('Y');

	$arrYear = array($curYear, $curYear-1);

	$month = Date('m');
	$year = Date('Y');
	
	if(isset($_REQUEST['month']) && $_REQUEST['month'] != '')
	{
		$month = $_REQUEST['month'];
	}

	if(isset($_REQUEST['year']) && $_REQUEST['year'] != '')
	{
		$year = $_REQUEST['year'];
	}

	$tpl->set_var("premonth", $month);
	$tpl->set_var("preyear", $year);
	
	$tpl->set_var("DisplayMessageBlock","");
	
	if(isset($_POST['hdnAction']) && $_POST['hdnAction'] =='cancel')
	{
		$changeStatus = $objShiftMovement->fnChangeStatus($_POST['id']);
		
		if($changeStatus)
		{
			header("Location: shift_movement_cancel.php?info=success");
		}
		else
		{
			header("Location: shift_movement_cancel.php?info=err");
		}
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
	
	/*if($_SESSION['usertype'] == 'admin')
	{*/
		$arrEmployee = $objEmployee->fnGetAllemployees(0);
	/*}
	else
	{
		$arrEmployee = $objEmployee->fnGetAllemployees($_SESSION['id']);
	}*/
	
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
	$arrMovements = $objShiftMovement->fnGetAllShiftMovementToCancel($ids,$month,$year);
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

	$tpl->set_var("DisplayYearBlock","");
	if(count($arrYear) > 0)
	{
		foreach($arrYear as $curYr)
		{
			$tpl->set_var("curyr",$curYr);
			$tpl->parse("DisplayYearBlock",true);
		}
	}
	
	$tpl->pparse('main',false);
?>
