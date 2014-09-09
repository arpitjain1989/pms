<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('emp_test_marks.view.html','main_container');

	$PageIdentifier = "EmployeeTest";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Test Crieteria");
	$breadcrumb = '<li><a href="emp_test_marks.php">Manage Employee Test criteria</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Test Criteria</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.emp_test.php');
	
	$objEmpTest = new emp_test();
	
	$arrEmpTest = $objEmpTest->fnGetEmpTestMarksById($_REQUEST['id']);
	
	foreach($arrEmpTest as $arrEmpTestvalue)
	{
		$tpl->SetAllValues($arrEmpTestvalue);
	}

	$tpl->pparse('main',false);
?>
