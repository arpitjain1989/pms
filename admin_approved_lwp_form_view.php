<?php
	include('common.php');

	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('admin_approved_lwp_form_view.html','main_container');

	$PageIdentifier = "AdminApprovedLWPForm";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Leave Form");
	$breadcrumb = '<li><a href="admin_leave_form.php">Manage Admin Leave Form</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Admin Leave Form</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.leave.php');
	include_once('includes/class.employee.php');

	$objLeave = new leave();
	$objEmployee = new employee();

	$arrLeaves = $objLeave->fnGetAdminApprovedLwpById($_REQUEST['id']);
	if(isset($arrLeaves) && count($arrLeaves) > 0)
	{
		$tpl->SetAllValues($arrLeaves);
		$arrStatus = array("0"=>"Pending", "1"=>"Approved", "2"=>"Unapproved");
		$curStatus = "";
		if(isset($arrStatus[$arrLeaves["approval_status"]]))
			$curStatus = $arrStatus[$arrLeaves["approval_status"]];
		$tpl->set_var("approval_status", $curStatus);
	}

	$tpl->pparse('main',false);
?>
