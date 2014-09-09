<?php 
	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('emp_test_marks.html','main_container');

	$PageIdentifier = "EmployeeTestMarks";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Test Criteria");
	$breadcrumb = '<li class="active">Manage Test Criteria</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.emp_test.php');
	
	$objEmpTest = new emp_test();
	$arrEmpTest = $objEmpTest->fnGetAllEmpTestMarks();
	

	$message = "";
	$messageClass = "";
	$tpl->set_var("DisplayMessageBlock","");
	
	if(isset($_REQUEST['info']))
	{

		switch($_REQUEST["info"])
		{
			case 'succ':
				$messageClass = "alert-success";
				$message = "Criteria for test inserted successfully.";
				break;
			case 'update':
				$messageClass = "alert-success";
				$message = "Criteria for test updated successfully.";
				break;
			case 'delete':
				$messageClass = "alert-success";
				$message = "No records found.";
				break;
			case 'exist':
				$messageClass = "alert-success";
				$message = "Criteria for test deleted successfully.";
				break;
		}
		
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);
			$tpl->parse("DisplayMessageBlock",false);	
		}
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] = 'delete')
	{
		$delteEmpTest = $objEmpTest->fnDeleteEmpMarksTest($_POST);
		if($delteEmpTest)
		{
			header("Location: emp_test_marks.php?info=delete");
		}
		else
		{
			
		}
	}
	
	$tpl->set_var("FillEmpTestValues","");
	if(count($arrEmpTest) > 0 )
	{
		foreach($arrEmpTest as $arrEmpTestvalue)
		{
			$tpl->SetAllValues($arrEmpTestvalue);
			$tpl->parse("FillEmpTestValues",true);
		}
	}
	
	
	$tpl->pparse('main',false);
?>
