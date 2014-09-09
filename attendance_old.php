<?php 

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('attendance.html','main_container');

	$PageIdentifier = "Attendance";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Attendances");
	$breadcrumb = '<li class="active">Manage Attendances</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.attendance.php');
	
	$objAttendance = new attendance();
	$arrAttendances = $objAttendance->fnGetAllAttendances();
	
	
	if(isset($_REQUEST['info']))
	{
		if($_REQUEST['info'] == 'succ')
		{
			$tpl->set_var('message',"Attendance inserted successfully.");
		}
		else if($_REQUEST['info'] == 'update')
		{
			$tpl->set_var('message',"Attendance updated successfully.");
		}
		else if($_REQUEST['info'] == 'delete')
		{
			$tpl->set_var('message',"Attendance deleted successfully.");
		}
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] = 'delete')
	{
		$delteattendance = $objAttendance->fnDeleteAttendance($_POST);
		if($delteattendance)
		{
			header("Location: attendance.php?info=delete");
		}
		else
		{
			
		}
	}
	
	$tpl->set_var("FillAttendanceValues","");
	foreach($arrAttendances as $arrAttendancevalue)
	{
		$tpl->SetAllValues($arrAttendancevalue);
		$tpl->parse("FillAttendanceValues",true);
	}

	$tpl->pparse('main',false);
?>