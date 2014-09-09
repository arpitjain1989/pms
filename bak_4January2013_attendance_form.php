<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('attendance_form.html','main');
	

	include_once('includes/class.attendance.php');
	
	$objattendance = new attendance();
	
	//$arrAttendanceData = $objattendance->fnGetAttendanceAll($_REQUEST['id']);
	//print_r($_REQUEST); die;
	$arrEmployeeData = $objattendance->fnGetEmployeeDetails($_REQUEST['designation']);
	
	if(isset($_REQUEST['id']))
	{
		$tpl->set_var('attendanceid',"$_REQUEST[id]");
	}
	if(isset($_REQUEST['date']))
	{
		$tpl->set_var('attendance_date',"$_REQUEST[date]");
	}
	
	$tpl->set_var('action','hdnadd');
	if(isset($_POST['hdnaction']) && $_POST['hdnaction'] == 'hdnadd')
	{
		$insertdata = $objattendance->fnInsertAttendance($_POST);
		if($insertdata)
		{
			header("Location: attendance.php?info=succ");
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
	
	$tpl->set_var('EmployeeValues','');
	if(count($arrEmployeeData)> 0)
	{
		foreach($arrEmployeeData as $Employees) 
		{
			$tpl->setAllValues($Employees);
			$tpl->parse('EmployeeValues',true);
		}
	}
	
	$tpl->pparse('main',false);
?>	