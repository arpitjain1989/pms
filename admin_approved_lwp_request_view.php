<?php
	include('common.php');

	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('admin_approved_lwp_request_view.html','main_container');

	$PageIdentifier = "AdminApprovedLWPRequest";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Admin Approved LWP Request");
	$breadcrumb = '<li><a href="admin_leave_requests.php">Manage Admin Approved LWP Request</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Admin Approved LWP Request</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.leave.php');
	include_once('includes/class.employee.php');

	$objLeave = new leave();
	$objEmployee = new employee();

	if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "AdminApprovedLwpRequest")
	{
		$status = $objLeave->fnApproveAdminLwpRequest($_POST);
		if($status == 0)
		{
			header("Location: admin_approved_lwp_requests.php?info=err");
			exit;
		}
		else if($status == 1)
		{
			header("Location: admin_approved_lwp_requests.php?info=succa");
			exit;
		}
		else if($status == 2)
		{
			header("Location: admin_approved_lwp_requests.php?info=succu");
			exit;
		}
	}

	$arrLeaveInfo = $objLeave->fnGetAdminApprovedLwpRequestById($_REQUEST['id']);
	if(count($arrLeaveInfo) > 0)
	{
		if(!empty($arrLeaveInfo['lwp_date_to']))
			{
			$dt = new DateTime($arrLeaveInfo['lwp_date_to']);
			$dateTo = $dt->format('d-m-Y');
			$tpl->set_var("date_to",$dateTo);
			}
			else
			{
			$tpl->set_var("date_to","");
			}
		$tpl->SetAllValues($arrLeaveInfo);
	}

	$tpl->pparse('main',false);
?>
