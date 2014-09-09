<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('employee_list.html','main_container');

	$PageIdentifier = "EmployeeList";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Employee List");
	$breadcrumb = '<li class="active">Employee List</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once("includes/class.employee.php");
	$objEmployee = new employee();

	$tpl->set_var("employee_list_for", $_SESSION["displayname"]);

	$arrEmployee = $objEmployee->fnGetAllEmployeesDetails($_SESSION["id"]);
	$tpl->set_var("FillEmployeeDetailsBlock","");
	if(count($arrEmployee) > 0)
	{
		foreach($arrEmployee as $curEmployee)
		{
			$tpl->set_var("employee_code", $curEmployee["employee_code"]);
			$tpl->set_var("employee_contact", $curEmployee["contact"]);
			$tpl->set_var("date_of_joining", $curEmployee["date_of_joining"]);
			$tpl->set_var("name", $curEmployee["name"]);
			$tpl->set_var("reporting_head_name", $curEmployee["reporting_head_name"]);
			$tpl->set_var("designation_title", $curEmployee["designation_title"]);
			$tpl->set_var("emergency_contact", $curEmployee["emergency_contact"]);
			$tpl->set_var("emergency_contact_name", $curEmployee["emergency_contact_name"]);
			$tpl->set_var("email", $curEmployee["email"]);
			if(isset($curEmployee["relation"]) && $curEmployee["relation"] != "")
				$tpl->set_var("contact_person_relation", " - " . $curEmployee["relation"]);
			else
				$tpl->set_var("contact_person_relation", "");

			$tpl->parse("FillEmployeeDetailsBlock",true);
		}
	}

	$tpl->pparse('main',false);
?>
