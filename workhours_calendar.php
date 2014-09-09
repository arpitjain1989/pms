<?php 

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('workhours_calendar.html','main_container');

	$PageIdentifier = "WorkhoursCalendar";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Attendence");
	$breadcrumb = '<li class="active">View Attendence</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	$tpl->pparse('main',false);
?>
