<?php

	include('common.php');

	$tpl = new Template($app_path);

	$tpl->load_file('leavers_details.html','main');

	include_once("includes/class.employee.php");
	$objEmployee = new employee();

	$tpl->set_var("DisplayEmployeesBlock", "");
	$tpl->set_var("DisplayNoRecordsBlock", "");

	if(isset($_REQUEST["a"]) && in_array($_REQUEST["a"], array(1,2,3)) && isset($_REQUEST["id"]) && $_REQUEST["id"] != "")
	{
		if($_REQUEST["id"] == 0)
		{
			if(is_array($_SESSION["SearchAttrition"]["reporting_head"]))
			{
				/* Viewing form HR attrition report */
				$reporting_head = $_SESSION["SearchAttrition"]["reporting_head"];
			}
			else if($_SESSION["SearchAttrition"]["reporting_head"] != 'all' && $_SESSION["SearchAttrition"]["reporting_head"] != '0')
			{
				/* view details form tl and manager attrition report */
				$reporting_head = array($_SESSION["SearchAttrition"]["reporting_head"]);
			}
			else
			{
				if(isset($_SESSION["usertype"]) && trim($_SESSION["usertype"]) == 'admin')
				{
					/* If login as admin, check for all reporting heads */
					$reporting_head = 0;				
				}
				else
				{
					/* view details form tl and manager attrition report */
					$reporting_head = array($_SESSION["id"]);
				}
			}
		}
		else
		{
			/* Viewing form HR attrition report */
			$reporting_head = array($_REQUEST["id"]);
		}

		$arrEmployee = $objEmployee->fnGetLeaversForAttrition($reporting_head, $_REQUEST["a"], $_SESSION["SearchAttrition"]["month"], $_SESSION["SearchAttrition"]["year"]);

		if(count($arrEmployee) > 0)
		{
			foreach($arrEmployee as $curEmployee)
			{
				$tpl->set_var("employee_code", $curEmployee["employee_code"]);
				$tpl->set_var("employee_name", $curEmployee["name"]);

				$tpl->parse("DisplayEmployeesBlock", true);
			}
		}
		else
			$tpl->parse("DisplayNoRecordsBlock", false);
	}
	else
		$tpl->parse("DisplayNoRecordsBlock", false);

	$tpl->pparse("main",false);

?>
