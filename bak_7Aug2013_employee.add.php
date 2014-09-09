<?php
	include('common.php');
	//print_r($_SESSION);
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('employee.add.html','main_container');

	$PageIdentifier = "Employee";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Add / Edit Employees");
	$breadcrumb = '<li><a href="employee.php">Manage Employees</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add / Edit Employees</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.employee.php');
	include_once('includes/class.shifts.php');
	
	$objEmployee = new employee();
	$objShifts = new shifts();
	
	$arrDepartments = $objEmployee->fnGetDepartmentList();
	$arrDesignation = $objEmployee->fnGetDesignationList();
	$arrRole = $objEmployee->fnGetRole();
	//echo '<pre>';
	$error = $_SESSION['error'];
	//print_r($error);
	//die;
	$arrEmp = $_SESSION['arrEmp'];
	
	if(isset($_SESSION['error']))
	{
		//print_r($error);
		unset($_SESSION['error']);
	}
	
	if(isset($_SESSION['arrEmp']))
	{
		unset($_SESSION['arrEmp']);
	}
	
	$tpl->set_var("FillErrors","");
	if($_REQUEST['info'] == 'fail')
	{
		$tpl->set_var('error',$error);
		$tpl->parse("FillErrors",true);
		if(isset($arrEmp))
		{
			$tpl->setAllValues($arrEmp);
		}
	}
	
	if(isset($_REQUEST['id']))
	{
		$tpl->set_var('employeeid',"$_REQUEST[id]");
	}
	$tpl->set_var('action','hdnadd');
	if(isset($_POST['hdnaction']) && $_POST['hdnaction'] == 'hdnadd')
	{
		$insertdata = $objEmployee->fnInsertEmployee($_POST);
		//echo $insertdata; die;
		if($insertdata)
		{
			header("Location: employee.php?info=succ");
			exit;
		}
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] =='update')
	{
		$updateEmployee = $objEmployee->fnUpdateEmployee($_POST);
		if($updateEmployee)
		{
			header("Location: employee.php?info=update");
			exit;
		}
	}
	
	if(isset($_REQUEST['action']) && $_REQUEST['action']='update')
	{
	$tpl->set_var('password123','Insert password if you want to change.');
	$arrEmployee = $objEmployee->fnGetEmployeeById($_REQUEST['id']);
	if(isset($arrEmployee))
	{
		$tpl->SetAllValues($arrEmployee);
	}
		$tpl->set_var('action','update');
	}
	
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
	
	$tpl->pparse('main',false);
?>