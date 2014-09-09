<?php 
	
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('designation_wise_employees.html','main_container');

	$PageIdentifier = "DesignationWiseEmployeeSummary";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Designation wise employees");
	$breadcrumb = '<li><a href="designation_wise_employee_summary.php">Designation wise employee summary</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Designation wise employees</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once("includes/class.designation.php");
	include_once("includes/class.employee.php");
	
	$objDesignation = new designations();
	$objEmployee = new employee();

	$tpl->set_var("FillDesignationWiseEmployeesBlock","");
	if(isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		/* Fetch Designation By Id */
		$arrDesignation = $objDesignation->fnGetDesignationById($_REQUEST["id"]);
		if(count($arrDesignation) > 0)
		{
			$tpl->set_var("designation_name", $arrDesignation["title"]);
			
			/* Fetch active employees with selected designation */
			$arrEmployee = $objEmployee->fnGetEmployeesByDesignation($_REQUEST["id"]);
			if(count($arrEmployee) > 0)
			{
				foreach($arrEmployee as $curEmployee)
				{
					$tpl->set_var("employee_code",$curEmployee["employee_code"]);
					$tpl->set_var("employee_name",$curEmployee["name"]);
					$tpl->set_var("reporting_head",$curEmployee["reporting_head"]);
					
					$tpl->parse("FillDesignationWiseEmployeesBlock",true);
				}
			}
		}
		else
		{
			/* Designation information not found */
			header("Location: designation_wise_employee_summary.php?info=nodes");
			exit;
		}
	}
	else
	{
		/* If designation not found */
		header("Location: designation_wise_employee_summary.php?info=noid");
		exit;
	}

	$tpl->pparse("main",false);

?>
