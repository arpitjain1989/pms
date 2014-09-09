<?php
	include('common.php');

	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('admin_half_leave_request_view.html','main_container');

	$PageIdentifier = "AdminHalfLeaveRequest";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Half Leave Request");
	$breadcrumb = '<li><a href="admin_leave_requests.php">Manage Admin Half Leave Requests</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Half Leave Request</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.leave.php');
	include_once('includes/class.employee.php');

	$objLeave = new leave();
	$objEmployee = new employee();

	if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "AdminHalfLeaveRequest")
	{
		$status = $objLeave->fnApproveAdminHalfLeaveRequest($_POST);
		if($status == 0)
		{
			header("Location: admin_half_leave_requests.php?info=err");
			exit;
		}
		else if($status == 1)
		{
			header("Location: admin_half_leave_requests.php?info=succa");
			exit;
		}
		else if($status == 2)
		{
			header("Location: admin_half_leave_requests.php?info=succu");
			exit;
		}
	}

	$arrHalfLeaveInfo = $objLeave->fnGetAdminHalfLeaveRequestById($_REQUEST['id']);
	if(count($arrHalfLeaveInfo) > 0)
	{
		$tpl->SetAllValues($arrHalfLeaveInfo);
		$arrLeaveFor = array("0"=>"", "1"=>"First Half", "2"=>"Second Half");
		$tpl->set_var("halfdayform_text", $arrLeaveFor[$arrHalfLeaveInfo["halfdayfor"]]);
	}

	$tpl->pparse('main',false);
?>
