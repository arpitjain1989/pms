<?php

	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('designationajax.html','main');

	include_once('includes/class.employee.php');
	include_once('includes/class.designation.php');
	
	$objEmployee = new employee();
	$objDesignation = new designations();

	$arrEmployees = array();
	if(isset($_REQUEST['designation']) && trim($_REQUEST['designation']) != "")
	{
		/* Modified the function to consider the parent designation from designation master
		 * 
		 * $arrDesignations = $objEmployee->fnGetAllEmployeeByDesignationId($_REQUEST['designation']);
		 * 
		 **/
		$arrDesignation = $objDesignation->fnGetDesignationById($_REQUEST['designation']);
		if(count($arrDesignation) > 0)
		{
			if(isset($arrDesignation['parent_designation_id']) && trim($arrDesignation['parent_designation_id']) != "")
				$arrEmployees = $objEmployee->fnGetEmployeesByDesignation($arrDesignation['parent_designation_id']);
		}
	}

	$tpl->set_var('DesignationValues','');
	if(count($arrEmployees)> 0)
	{
		foreach($arrEmployees as $curEmployees)
		{
			$tpl->set_var("employee_id",$curEmployees["id"]);
			$tpl->set_var("employee_name",$curEmployees["name"]);

			$tpl->parse('DesignationValues',true);
		}
	}
	
	$tpl->pparse('main',false);
?>
