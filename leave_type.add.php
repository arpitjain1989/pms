<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('leave_type.add.html','main_container');

	$PageIdentifier = "LeaveType";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Add / Edit Leave Type");
	$breadcrumb = '<li><a href="leave_type.php">Manage Leave Type</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add / Edit Leave Type</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.leave.php');
	
	$objLeaveType = new leave();
	
	if(isset($_REQUEST['id']))
	{
		$tpl->set_var('departmentid',"$_REQUEST[id]");
	}
	$tpl->set_var('action','hdnadd');
	if(isset($_POST['hdnaction']) && $_POST['hdnaction'] == 'hdnadd')
	{
		$insertdata = $objLeaveType->fnInsertLeaveType($_POST);
		if($insertdata)
		{
			header("Location: leave_type.php?info=succ");
			exit;
		}
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] =='update')
	{
			$updateLeaveType = $objLeaveType->fnUpdateLeaveType($_POST);
			if($updateLeaveType)
		{
			header("Location: leave_type.php?info=update");
			exit;
		}
	}
	
	if(isset($_REQUEST['action']) && $_REQUEST['action']='update')
	{
		$arrLeaveType = $objLeaveType->fnGetLeaveTypeId($_REQUEST['id']);
		if(isset($arrLeaveType))
		{
			$tpl->SetAllValues($arrLeaveType);
		}
		$tpl->set_var('action','update');
	}
	
	
	$tpl->pparse('main',false);
?>