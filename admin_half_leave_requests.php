<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('admin_half_leave_requests.html','main_container');

	$PageIdentifier = "AdminHalfLeaveRequest";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Admin Half Leave Request");
	$breadcrumb = '<li class="active">Manage Admin Half Leave Request</li>';
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
				$message = "Half day Leave not found. Error updating leave information.";
				break;
			case 'succa':
				$messageClass = "alert-success";
				$message = "Half day Leave Approved successfully.";
				break;
			case 'succu':
				$messageClass = "alert-success";
				$message = "Half day Leave Unapproved successfully.";
				break;
		}
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	$arrAdminHalfLeaveForm = $objLeave->fnGetAllAdminHalfLeaveRequests();

	$tpl->set_var("FillAdminHalfLeaveFormValues","");
	if(count($arrAdminHalfLeaveForm) > 0)
	{
		$arrLeaveFor = array("0"=>"", "1"=>"First Half", "2"=>"Second Half");

		foreach($arrAdminHalfLeaveForm as $curAdminHalfLeaveForm)
		{
			$tpl->SetAllValues($curAdminHalfLeaveForm);
			$tpl->set_var("halfdayform_text", $arrLeaveFor[$curAdminHalfLeaveForm["halfdayfor"]]);
			$tpl->parse("FillAdminHalfLeaveFormValues",true);
		}
	}

	$tpl->pparse('main',false);

?>
