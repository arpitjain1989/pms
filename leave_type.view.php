<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('leave_type.view.html','main_container');

	$PageIdentifier = "LeaveType";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Leave Type");
	$breadcrumb = '<li><a href="leave_type.php">Manage Leave Type</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Leave Type</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.leave.php');
	
	$objLeaveType = new leave();
	
	$arrLeaveType = $objLeaveType->fnGetLeaveTypeId($_REQUEST['id']);
	
	if(isset($arrLeaveType))
	{
		$tpl->SetAllValues($arrLeaveType);
	}

	$tpl->pparse('main',false);
?>