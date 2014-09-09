<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('emp_test.view.html','main_container');

	$PageIdentifier = "EmployeeTest";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Employee Test Type");
	$breadcrumb = '<li><a href="emp_test.php">Manage Employee Test Type</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Employee Test Type</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.emp_test.php');
	
	$objEmpTest = new emp_test();
	
	$arrEmpTest = $objEmpTest->fnGetEmpTestById($_REQUEST['id']);
	
	foreach($arrEmpTest as $arrEmpTestvalue)
	{
		if($arrEmpTestvalue['parents_id'] == '0')
		{
			$tpl->set_var("parents_test_name",'Root');
		}
		else
		{
			$tpl->set_var("parents_test_name",$arrEmpTestvalue['par_title']);
		}
		$tpl->SetAllValues($arrEmpTestvalue);
	}

	$tpl->pparse('main',false);
?>
