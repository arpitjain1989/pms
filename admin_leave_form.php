<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('admin_leave_form.html','main_container');

	$PageIdentifier = "AdminLeaveForm";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Leave Form");
	$breadcrumb = '<li class="active">Manage Admin Leave Form</li>';
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
			case 'succ':
				$messageClass = "alert-success";
				$message = "Leave Form inserted successfully.";
				break;
			case 'exist':
				$messageClass = "alert-error";
				$message = "Leave For the date already exists.";
				break;
			case 'admexist':
				$messageClass = "alert-error";
				$message = "Leave cannot be added. Approved LWP added by admin.";
				break;
			case 'earlyerr':
				$messageClass = "alert-error";
				$message = "Cannot add leave so much in advance.";
				break;
			case 'aerr':
				$messageClass = "alert-error";
				$message = "Error while adding leave, attendance already added.";
				break;
			case 'shift':
				$messageClass = "alert-error";
				$message = "Error while adding leave, shift movement added.";
				break;
		}
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	$arrAdminLeaveForm = $objLeave->fnGetAllAdminLeave();

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
