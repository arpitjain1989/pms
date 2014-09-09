<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('employee.view.html','main_container');

	$PageIdentifier = "Employee";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Employees");
	$breadcrumb = '<li><a href="employee.php">Manage Employees</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Employees</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.employee.php');
	
	$objEmployee = new employee();

	$arrEmployee = $objEmployee->fnGetEmployeeById($_REQUEST['id']);
	$referalInfo = $objEmployee->fnGetReferredEmployeeById($arrEmployee['employee_reference_detail']);
	if($referalInfo)
	{
		$tpl->set_var("reffer_name",$referalInfo);
	}
	if($arrEmployee)
	{
		if($arrEmployee['status'] == '0')
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
		if($arrEmployee['gender'] == '1')
		{
			$tpl->set_var("emp_gender","Male");
		}
		else if($arrEmployee['gender'] == '2')
		{
			$tpl->set_var("emp_gender","Female");
		}
		else
		{
			$tpl->set_var("emp_gender","-");
		}

		if($arrEmployee['id_card'] == '1')
		{
			$tpl->set_var("emp_id_card","Issues");
		}
		else if($arrEmployee['id_card'] == '2')
		{
			$tpl->set_var("emp_id_card","Not Issues");
		}
		else
		{
			$tpl->set_var("emp_id_card","-");
		}
		
		
		if($arrEmployee['offer_letter_issued'] == '1')
		{
			$tpl->set_var("emp_offer_letter_issued","Yes");
		}
		else if($arrEmployee['offer_letter_issued'] == '2')
		{
			$tpl->set_var("emp_offer_letter_issued","No");
		}
		else
		{
			$tpl->set_var("emp_offer_letter_issued","-");
		}
		
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
		else
		{
			$tpl->set_var("emp_terminated_absconding_resigned","-");
		}

		if($arrEmployee['date_of_resign_terminate'] == '0000-00-00 00:00:00')
		{
			$tpl->set_var("resignation_date","-");
		}
		else
		{
			$tpl->set_var("resignation_date",$arrEmployee['date_of_resign_terminate']);
		}
		//echo $arrEmployee['relieving_date_by_manager'];
		
		if($arrEmployee['relieving_date_by_manager'] == '0000-00-00 00:00:00')
		{
			$tpl->set_var("relieving_date_manager","-");
		}
		else
		{
			$tpl->set_var("relieving_date_manager",$arrEmployee['date_of_resign_terminate']);
		}

		
		if($arrEmployee['issues_relieving_offer_letter'] == '1')
		{
			$tpl->set_var("emp_issues_relieving_offer_letter","Yes");
		}
		else if($arrEmployee['issues_relieving_offer_letter'] == '2')
		{
			$tpl->set_var("emp_issues_relieving_offer_letter","No");
		}
		else
		{
			$tpl->set_var("emp_issues_relieving_offer_letter","-");
		}

		if($arrEmployee['induction'] == '1')
		{
			$tpl->set_var("induction_status","Attend");
		}
		else if($arrEmployee['induction'] == '2')
		{
			$tpl->set_var("induction_status","Pending");
		}
		else
		{
			$tpl->set_var("induction_status","-");
		}
	}
	
	if(isset($arrEmployee))
	{
		$tpl->SetAllValues($arrEmployee);
		if($arrEmployee['teamleader'] =='')
		{
			$tpl->set_var("teamleader","Admin");
		}
	}

	$tpl->pparse('main',false);
?>
