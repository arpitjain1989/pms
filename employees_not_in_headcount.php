<?php

	include('common.php');
	$tpl = new Template($app_path);

	ini_set("max_execution_time",0);

	$tpl->load_file('template.html','main');
	$tpl->load_file('employees_not_in_headcount.html','main_container');

	$PageIdentifier = "EmployeesNotIncludedInHeadCountList";
	include_once('userrights.php');

	$tpl->set_var("mainheading","List of employees not includeded in head count");
	$breadcrumb = '<li class="active">List of employees not includeded in head count</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.employee.php');
	$objEmployee = new employee();

	$tpl->set_var("DisplayEmployeeBlock", "");
	$arrEmployee = $objEmployee->fnGetNotIncludedInHeadCountEmployees();
	if(count($arrEmployee) > 0)
	{
		foreach($arrEmployee as $curEmployee)
		{
			$tpl->SetAllValues($curEmployee);
			$tpl->parse("DisplayEmployeeBlock", true);
		}
	}

	$tpl->pparse('main',false);

?>
