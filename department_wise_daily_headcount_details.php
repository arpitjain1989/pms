<?php

	include('common.php');

	$tpl = new Template($app_path);

	$tpl->load_file('department_wise_daily_headcount_details.html','main');

	include_once("includes/class.employee.php");
	$objEmployee = new employee();

	$tpl->set_var("DisplayEmployeesBlock", "");
	if(isset($_REQUEST["a"]) && in_array($_REQUEST["a"], array(1,2,3,4)) && isset($_REQUEST["id"]) && $_REQUEST["id"] != '' && isset($_REQUEST["d"]) && $_REQUEST["d"] != '')
	{
		$arrEmployee = $objEmployee->fnGetDepartmentWiseHeadCountDetails($_REQUEST["id"], $_REQUEST["d"], $_REQUEST["a"]);

		if(count($arrEmployee) > 0)
		{
			foreach($arrEmployee as $curEmployee)
			{
				$tpl->set_var("employee_code", $curEmployee["employee_code"]);
				$tpl->set_var("employee_name", $curEmployee["name"]);
				$tpl->set_var("date_of_joining", $curEmployee["date_of_joining"]);
				$tpl->set_var("current_designation", $curEmployee["current_designation"]);

				$tpl->parse("DisplayEmployeesBlock", true);
			}
		}
	}

	$tpl->pparse("main",false);

?>
