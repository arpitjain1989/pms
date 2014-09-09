<?php
	include('common.php');

	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('admin_half_leave_form_view.html','main_container');

	$PageIdentifier = "AdminHalfLeaveForm";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Leave Form");
	$breadcrumb = '<li><a href="admin_half_leave_form.php">Manage Admin Half Leave Form</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Admin Half Leave Form</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.leave.php');
	include_once('includes/class.employee.php');

	$objLeave = new leave();
	$objEmployee = new employee();

	$arrHalfLeave = $objLeave->fnGetHalfLeaveDetailsById($_REQUEST['id']);
	if(isset($arrHalfLeave) && count($arrHalfLeave) > 0)
	{
		$halfdayform_text = "";
		switch($arrHalfLeave["halfdayfor"])
		{
			case '1':
				$halfdayform_text = "First Half";
				break;
			case '2':
				$halfdayform_text = "Second Half";
				break;
		}
		$tpl->set_var("halfdayform_text", $halfdayform_text);
		
		$tpl->SetAllValues($arrHalfLeave);
	}

	$tpl->pparse('main',false);
?>
