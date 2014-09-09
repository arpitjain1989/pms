<?php 
	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('leave_type.html','main_container');

	$PageIdentifier = "LeaveType";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Leave Type");
	$breadcrumb = '<li class="active">Manage Leave Type</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.leave.php');
	
	$objLeaveType = new leave();
	$arrLeaveTypes = $objLeaveType->fnGetAllLeaveTypes();
	
	$message = "";
	$messageClass = "";
	$tpl->set_var("DisplayMessageBlock","");
	if(isset($_REQUEST['info']))
	{
		switch($_REQUEST["info"])
		{
			case 'succ':
				$messageClass = "alert-success";
				$message = "Leave Types inserted successfully.";
				break;
			case 'update':
				$messageClass = "alert-success";
				$message = "Leave Types updated successfully.";
				break;
			case 'delete':
				$messageClass = "alert-success";
				$message = "Leave Types deleted successfully.";
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
		$deleteLeaveType = $objLeaveType->fnDeleteLeaveType($_POST);
		if($deleteLeaveType)
		{
			header("Location: leave_type.php?info=delete");
		}
		else
		{
			
		}
	}
	
	$tpl->set_var("FillLeaveTypeValues","");
	foreach($arrLeaveTypes as $arrLeaveTypesvalue)
	{
		$tpl->SetAllValues($arrLeaveTypesvalue);
		$tpl->parse("FillLeaveTypeValues",true);
	}

	$tpl->pparse('main',false);
?>
