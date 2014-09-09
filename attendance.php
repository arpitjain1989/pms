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
	//$arrAttendances = $objAttendance->fnGetAllAttendances();
	
	$message = "";
	$messageClass = "";
	$tpl->set_var("DisplayMessageBlock","");
	if(isset($_REQUEST['info']))
	{
		switch($_REQUEST["info"])
		{
			case 'succ':
				$messageClass = "alert-success";
				$message = "Attendance inserted successfully.";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Cannot update attendance for back dates.";
				break;
			case 'update':
				$messageClass = "alert-success";
				$message = "Attendance updated successfully.";
				break;
			case 'delete':
				$messageClass = "alert-success";
				$message = "Attendance deleted successfully.";
				break;
		}
		
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);
			
			$tpl->parse("DisplayMessageBlock",false);	
		}
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] = 'delete')
	{
		$delteattendance = $objAttendance->fnDeleteAttendance($_POST);
		if($delteattendance)
		{
			header("Location: attendance.php?info=delete");
		}
	}
	
	/*$tpl->set_var("FillAttendanceValues","");
	foreach($arrAttendances as $arrAttendancevalue)
	{
		$tpl->SetAllValues($arrAttendancevalue);
		$tpl->parse("FillAttendanceValues",true);
	}*/

	$tpl->pparse('main',false);
?>
