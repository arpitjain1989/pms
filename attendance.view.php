<?php
	include('common.php');

	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('attendance.view.html','main_container');

	include_once('userrights.php');

	$tpl->set_var("mainheading","View Attendances");
	$breadcrumb = '<li><a href="attendance.php">Manage Attendances</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Attendances</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.attendance.php');

	$objAttendance = new attendance();

	$arrAttendances = $objAttendance->fnGetAttendanceById($_REQUEST['id']);
	if(count($arrAttendances) > 0)
	{
		$tpl->SetAllValues($arrAttendances);
	}
	/*foreach($arrAttendances as $arrAttendancevalue)
	{
		$tpl->SetAllValues($arrAttendancevalue);
	}*/

	$tpl->pparse('main',false);
?>