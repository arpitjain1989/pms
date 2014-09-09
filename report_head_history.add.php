<?php
	include('common.php');
	//print_r($_SESSION);
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('report_head_history.add.html','main_container');

	$PageIdentifier = "ReportingHeadHistory";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Edit Reporting Head History");
	$breadcrumb = '<li><a href="employee.php">Reporting Head History</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Edit Reporting Head History</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.employee.php');
	include_once('includes/class.shifts.php');
	include_once('includes/class.designation.php');
	
	$objEmployee = new employee();
	$objShifts = new shifts();
	$objDesignation = new designations();
	
	$arrDepartments = $objEmployee->fnGetDepartmentList();
	$arrDesignation = $objEmployee->fnGetDesignationList();
	//$arrEmployee = $objEmployee->fnGetAllEmployee();

	$tpl->set_var("FillHrRelatedDetails","");
	$tpl->set_var("FillEmployeeValues","");

	$tpl->set_var("sess_username",$_SESSION['username']);
	$tpl->set_var("sess_id",$_SESSION['id']);

	if(isset($_POST['hdnaction']) && $_POST['hdnaction'] == 'edit')
	{
		$arrEmployeeData1 = $objEmployee->fnUpdateReportingHeadHistory($_POST);
		//echo 'employeedata'.$arrEmployeeData1; die;
		
		if($arrEmployeeData1)
		{
			header("Location: report_head_history.php?info=succ&id=".$arrEmployeeData1);
			exit;
		}
		else
		{
			header("Location: report_head_history.php?info=err");
			exit;
		}
	}

	
	$getEmployee = $objEmployee->fnGetReportingHistoryHeadById($_REQUEST['id']);
	//echo '<pre>'; print_r($getEmployee); die;
	if(count($getEmployee) > 0)
	{
		//echo '<pre>'; print_r($getEmployee);
		$tpl->set_var("hid",$getEmployee["his_id"]);
		$tpl->set_var("reporting_head",$getEmployee["rep_head"]);
		$tpl->set_var("eff_date",$getEmployee["ef_date"]);
		$tpl->set_var("eName",$getEmployee["employeeName"]);
		$tpl->set_var("eId",$getEmployee["userid"]);
	}

	
	
	if(isset($getEmployee['emp_designation']) && trim($getEmployee['emp_designation']) != "")
	{
		/* Modified the function to consider the parent designation from designation master
		 * 
		 * $arrDesignations = $objEmployee->fnGetAllEmployeeByDesignationId($_REQUEST['designation']);
		 * 
		 **/
		$arrDesignation = $objDesignation->fnGetDesignationById($getEmployee['emp_designation']);
		if(count($arrDesignation) > 0)
		{
			if(isset($arrDesignation['parent_designation_id']) && trim($arrDesignation['parent_designation_id']) != "")
				$arrEmployees = $objEmployee->fnGetEmployeesByDesignation($arrDesignation['parent_designation_id']);
		}
	}
	$tpl->set_var('ReportingHeadValues','');
	//echo '<pre>'; print_r($arrEmployees);
	if(count($arrEmployees)> 0)
	{
		foreach($arrEmployees as $curEmployees)
		{
			$tpl->set_var("employee_id",$curEmployees["id"]);
			$tpl->set_var("employee_name",$curEmployees["name"]);

			$tpl->parse('ReportingHeadValues',true);
		}
	}
	//echo '<pre>';
	$error = "";
	if(isset($_SESSION['error']))
	{
		$error = $_SESSION['error'];
	}
	//print_r($error);
	//die;
	$arrEmp = array();
	if(isset($_SESSION['arrEmp']))
	{
		$arrEmp = $_SESSION['arrEmp'];
	}
	
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
	if(isset($_REQUEST['info']) && $_REQUEST['info'] == 'fail')
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
	/*$tpl->set_var('action','hdnadd');
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
	}*/
	
	if(isset($_REQUEST['action']) && $_REQUEST['action']='update')
	{
		$tpl->set_var('password123','Insert password if you want to change.');
		$arrEmployee = $objEmployee->fnGetEmployeeById($_REQUEST['id']);
		//echo '<pre>'; print_r($arrEmployee);
		if(isset($arrEmployee))
		{
			$tpl->SetAllValues($arrEmployee);
		}
		$tpl->set_var('action','update');
	}
	$tpl->pparse('main',false);
?>
