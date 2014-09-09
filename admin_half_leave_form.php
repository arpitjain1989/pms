<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('admin_half_leave_form.html','main_container');

	$PageIdentifier = "AdminHalfLeaveForm";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Admin Half Leave Form");
	$breadcrumb = '<li class="active">Manage Admin Half Leave Form</li>';
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
				$message = "Leave Form inserted successfully.Please update Attendance ";
				break;
			case 'admexist':
				$messageClass = "alert-error";
				$message = "Half day leave cannot be added. Approved LWP added by admin.";
				break;
			case 'exist':
				$messageClass = "alert-error";
				$message = "Leave For the date already exists.";
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
			case 'leave':
				$messageClass = "alert-error";
				$message = "Error while adding leave, Short Fall of Work Hours for Half Day Present. Please Update attandance.";
				break;
		}
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	$arrAdminHalfLeaveForm = $objLeave->fnGetAllAdminHalfLeave();

	$tpl->set_var("FillAdminLeaveFormValues","");
	if(count($arrAdminHalfLeaveForm) > 0)
	{
		foreach($arrAdminHalfLeaveForm as $curAdminHalfLeaveForm)
		{
			$tpl->SetAllValues($curAdminHalfLeaveForm);
			
			$halfdayform_text = "";
			switch($curAdminHalfLeaveForm["halfdayfor"])
			{
				case '1':
					$halfdayform_text = "First Half";
					break;
				case '2':
					$halfdayform_text = "Second Half";
					break;
			}

			$tpl->set_var("halfdayform_text", $halfdayform_text);
			$tpl->parse("FillAdminLeaveFormValues",true);
		}
	}

	$tpl->pparse('main',false);

?>
