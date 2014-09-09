<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('admin_leave_requests.html','main_container');

	$PageIdentifier = "AdminLeaveRequest";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Admin Leave Request");
	$breadcrumb = '<li class="active">Manage Admin Leave Request</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.leave.php');

	$objLeave = new leave();

	$message = "";
	$messageClass = "";

	$tpl->set_var("DisplayMessageBlock","");

	if(isset($_REQUEST['info']))
	{
		switch($_REQUEST["info"])
		{
			case 'err':
				$messageClass = "alert-error";
				$message = "Leave not found. Error updating leave information.";
				break;
			case 'succa':
				$messageClass = "alert-success";
				$message = "Leave Approved successfully.";
				break;
			case 'succu':
				$messageClass = "alert-success";
				$message = "Leave Unapproved successfully.";
				break;
		}
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	$arrAdminLeaveForm = $objLeave->fnGetAllAdminLeaveRequests();

	$tpl->set_var("FillAdminLeaveFormValues","");
	if(count($arrAdminLeaveForm) > 0)
	{
		foreach($arrAdminLeaveForm as $curAdminLeaveForm)
		{
			$tpl->SetAllValues($curAdminLeaveForm);
			$tpl->parse("FillAdminLeaveFormValues",true);
		}
	}

	$tpl->pparse('main',false);

?>
