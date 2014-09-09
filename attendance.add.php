<?php
	include('common.php');

	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('attendance.add.html','main_container');

	$PageIdentifier = "Attendance";
	include_once('userrights.php');

	$curdate = date('Y-m-d');
	$tpl->set_var("currentdate",$curdate);

	$tpl->set_var("mainheading","Add / Edit Attendances");
	$breadcrumb = '<li><a href="attendance.php">Manage Attendances</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add / Edit Attendances</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.attendance.php');

	$objattendance = new attendance();
	
	//echo '<pre>'; print_r($arrEmployees);

	if(isset($_REQUEST['id']))
	{
		$tpl->set_var('attendanceid',"$_REQUEST[id]");
	}
	
	if(isset($_POST['hdnaction']) && $_POST['hdnaction'] == 'add')
	{
		$insertdata = $objattendance->fnInsertAttendance($_POST);
		if($insertdata)
		{
			header("Location: attendance.php?info=succ");
			exit;
		}
		else
		{
			header("Location: attendance.php?info=err");
			exit;
		}
	}

	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] =='update')
	{
		$updateAttendances = $objattendance->fnUpdateAttendances($_POST);
		if($updateAttendances)
		{
			header("Location: attendance.php?info=update");
			exit;
		}
	}

	//print_r($_REQUEST);

	if(isset($_REQUEST['action']) && $_REQUEST['action']=='update')
	{
		$attendanceDate = $curdate = $_REQUEST['date'];
		$tpl->set_var('currentdate',$attendanceDate);
		if(isset($_REQUEST['id']))
		{
			$arrAttendances = $objattendance->fnGetAttendanceById($_REQUEST['id']);
			if(count($arrAttendances) > 0)
			{
				$tpl->SetAllValues($arrAttendances);
			}
		}
		$tpl->set_var('action','update');
	}

	$arrEmployees = $objattendance->fnGetEmployees($curdate);
	$tpl->set_var('EmployeeValues','');
	if(count($arrEmployees)> 0)
	{
		foreach($arrEmployees as $Employees)
		{
			$tpl->setAllValues($Employees);
			$tpl->parse('EmployeeValues',true);
		}
	}

	$tpl->pparse('main',false);
?>
