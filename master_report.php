<?php 

	include('common.php');
	$tpl = new Template($app_path);

	ini_set("max_execution_time",300);

	$tpl->load_file('template.html','main');
	$tpl->load_file('master_report.html','main_container');

	$PageIdentifier = "MasterReport";
	include_once('userrights.php');

	
	$tpl->set_var("mainheading","Employee Master");
	$breadcrumb = '<li class="active">Employee Master</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.employee.php');
	
	$objEmployee = new employee();

	$arrEmployeeAllData = $objEmployee->fnGetAllEmployeeDetails();

	$tpl->set_var("FillEmployeeDetails","");
	foreach($arrEmployeeAllData as $arrEmployee)
	{
		if(isset($arrEmployee['status']) && $arrEmployee['status'] == '0')
		{
			$tpl->set_var("stat","Active");
		}
		else
		{
			$tpl->set_var("stat","Active");
		}
		
		if(isset($arrEmployee['namaz']) && $arrEmployee['namaz'] == '0')
		{
			$tpl->set_var("namaz_status","No");
		}
		else
		{
			$tpl->set_var("namaz_status","Yes");
		}

		if(isset($arrEmployee['gender']))
		{
			if($arrEmployee['gender'] == '1')
			{
				$tpl->set_var("emp_gender","Male");
			}
			else if($arrEmployee['gender'] == '2')
			{
				$tpl->set_var("emp_gender","Female");
			}
		}
		
		if(isset($arrEmployee['id_card']))
		{
			if($arrEmployee['id_card'] == '1')
			{
				$tpl->set_var("emp_id_card","Yes");
			}
			else if($arrEmployee['id_card'] == '2')
			{
				$tpl->set_var("emp_id_card","No");
			}
		}

		if(isset($arrEmployee['retention_bonus_scheme']))
		{
			if($arrEmployee['retention_bonus_scheme'] == '1')
			{
				$tpl->set_var("emp_retention_bonus_scheme","Yes");
			}
			else if($arrEmployee['retention_bonus_scheme'] == '2')
			{
				$tpl->set_var("emp_retention_bonus_scheme","No");
			}
		}

		if(isset($arrEmployee['offer_letter_issued']))
		{
			if($arrEmployee['offer_letter_issued'] == '1')
			{
				$tpl->set_var("emp_offer_letter_issued","Yes");
			}
			else if($arrEmployee['offer_letter_issued'] == '2')
			{
				$tpl->set_var("emp_offer_letter_issued","No");
			}
		}

		if(isset($arrEmployee['terminated_absconding_resigned']))
		{
			if($arrEmployee['terminated_absconding_resigned'] == '1')
			{
				$tpl->set_var("emp_terminated_absconding_resigned","Terminated");
			}
			else if($arrEmployee['terminated_absconding_resigned'] == '2')
			{
				$tpl->set_var("emp_terminated_absconding_resigned","Absconding");
			}
			else if($arrEmployee['terminated_absconding_resigned'] == '3')
			{
				$tpl->set_var("emp_terminated_absconding_resigned","Resigned");
			}
		}

		if(isset($arrEmployee['issues_relieving_offer_letter']))
		{
			if($arrEmployee['issues_relieving_offer_letter'] == '1')
			{
				$tpl->set_var("emp_issues_relieving_offer_letter","Yes");
			}
			else if($arrEmployee['issues_relieving_offer_letter'] == '2')
			{
				$tpl->set_var("emp_issues_relieving_offer_letter","No");
			}
		}
		$tpl->SetAllValues($arrEmployee);
		$tpl->parse("FillEmployeeDetails",true);
	}

	$tpl->pparse('main',false);
?>
