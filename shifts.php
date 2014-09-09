<?php 

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('shifts.html','main_container');

	$PageIdentifier = "Shift";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Shift");
	$breadcrumb = '<li class="active">Manage Shift</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.shifts.php');
	
	$objShift = new shifts();
	$arrShifts = $objShift->fnGetAllShifts();
	
	$message = "";
	$messageClass = "";
	$tpl->set_var("DisplayMessageBlock","");
	
	if(isset($_REQUEST['info']))
	{
		switch($_REQUEST["info"])
		{
			case 'succ':
				$messageClass = "alert-success";
				$message = "Shift inserted successfully.";
				break;
			case 'update':
				$messageClass = "alert-success";
				$message = "Shift updated successfully.";
				break;
			case 'delete':
				$messageClass = "alert-success";
				$message = "Shift deleted successfully.";
				break;
		}
		
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);
			
			$tpl->parse("DisplayMessageBlock",false);	
		}
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] = 'delete')
	{
		$delteshifts = $objShift->fnDeleteShift($_POST);
		if($delteshifts)
		{
			header("Location: shifts.php?info=delete");
		}
		else
		{
			
		}
	}
	
	$tpl->set_var("FillShiftValues","");
	foreach($arrShifts as $arrShiftvalue)
	{
		$tpl->SetAllValues($arrShiftvalue);
		$tpl->parse("FillShiftValues",true);
	}

	$tpl->pparse('main',false);
?>
