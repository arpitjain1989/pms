<?php 
	include('common.php');
	
	$tpl = new Template($app_path);
	//print_r($_SESSION);
	$tpl->load_file('template.html','main');
	$tpl->load_file('employee_profile.html','main_container');

	$PageIdentifier = "Employee Profile";	
	include_once('userrights.php');
	
	$tpl->set_var("mainheading","User Profile");
	$breadcrumb = '<li class="active">User Profile</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.employee.php');
	include_once('includes/class.shifts.php');
	include_once('includes/class.admin.php');
	include_once('includes/class.attendance.php');
	
	$objEmployee = new employee();
	$objShifts = new shifts();
	$objAdmin = new admin();
	$objAttendance = new attendance();
	
	$arrDepartments = $objEmployee->fnGetDepartmentList();
	$arrDesignation = $objEmployee->fnGetDesignationList();
	$arrRole = $objEmployee->fnGetRole();
	$tpl->set_var('password123','Insert password if you want to change.');
	$tpl->set_var("DisplayMessageBlock","");
	$tpl->set_var("action","update");
	$tpl->set_var("emp_id",$_SESSION['id']);
	
	

	if(isset($_SESSION['error']))
	{
		$error = $_SESSION['error'];
		//print_r($error);
		unset($_SESSION['error']);
	}
	
	$tpl->set_var("DisplayMessageBlock","");
	
	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'succ':
				$messageClass = "alert-success";
				$message = "Profile updated successfully";
				break;
			case 'fail':
				$messageClass = "alert-error";
				$message = "Error updating profile";
				break;
		}
		
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);
			
			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	
	
	if(isset($_POST['hdnaction']) && $_POST['hdnaction'] == 'update')
	{
		//echo 'hello'; die;
		$updatevalues = $objEmployee->fnUpdateProfile($_POST);
		if($updatevalues)
		{
			header("Location: employee_profile.php?info=succ");
			exit;
		}
	}
	
	
	$EmployeeDetail = $objEmployee->fnGetEmployeeDetailById($_SESSION['id']);
	//print_r($EmployeeDetail);
	if(isset($EmployeeDetail) && count($EmployeeDetail) > 0)
	{
		$pendingLeaves = $objAttendance->fnGetLastLeaveBalance($_SESSION['id']);

		$unpaid_leaves = $objAttendance->fnGetUserLeavesWithoutPayByMonthAndYear($_SESSION['id'], Date('m'), Date('Y'));

		$pendingLeaveBalance = 0;
		if($pendingLeaves > 0)
			$pendingLeaveBalance = $pendingLeaves;

		$tpl->set_var("pending_leave_balance", $pendingLeaveBalance);
		
		$tpl->SetAllValues($EmployeeDetail);
	}
	
	/*
	$tpl->set_var('DepartmentValues','');
	if(count($arrDepartments)> 0)
	{
		foreach($arrDepartments as $departments) 
		{
			$tpl->setAllValues($departments);
			$tpl->parse('DepartmentValues',true);
		}
	}
	
	$tpl->set_var('DesignationValues','');
	if(count($arrDesignation)> 0)
	{
		foreach($arrDesignation as $designations) 
		{
			$tpl->setAllValues($designations);
			$tpl->parse('DesignationValues',true);
		}
	}
	
	$tpl->set_var('RolesValues','');
	if(count($arrRole)> 0)
	{
		foreach($arrRole as $roles) 
		{
			$tpl->setAllValues($roles);
			$tpl->parse('RolesValues',true);
		}
	}
	
	$tpl->set_var("FillShiftTimings","");
	$arrShifts = $objShifts->fnGetAllShifts();
	if(count($arrShifts) > 0)
	{
		foreach($arrShifts as $curShift)
		{
			$tpl->set_var("shifttimings_id",$curShift["id"]);
			$tpl->set_var("shifttimings",$curShift["title"]);
			
			$tpl->parse("FillShiftTimings",true);
		}
	}
	*/
	$tpl->pparse('main',false);
?>
