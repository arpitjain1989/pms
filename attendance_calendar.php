<?php 

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('attendance_calendar.html','main_container');

	$PageIdentifier = "AttendanceCalendar";
	include_once('userrights.php');
	
	include_once('includes/class.employee.php');
	
	$objEmployee = new employee();

	$tpl->set_var("mainheading","View Attendence");
	$breadcrumb = '<li class="active">View Attendence</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	//echo '<pre>';
	//print_r($_SESSION);
	
	$requested_id = 0;
	if(isset($_REQUEST['userid']))
		$requested_id = $_REQUEST['userid'];
		
	if(isset($requested_id) && $requested_id != '')
	{
		$tpl->set_var("requestedId",$requested_id);
	}
	
	$type = "self";
	if(isset($_REQUEST["type"]) && trim($_REQUEST["type"]) != "")
	{
		$type = trim($_REQUEST["type"]);
	}
	$tpl->set_var("type",$type);
	
	$tpl->pparse('main',false);
?>
