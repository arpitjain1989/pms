<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('department.view.html','main_container');

	$PageIdentifier = "Departments";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Departments");
	$breadcrumb = '<li><a href="departments.php">Manage Departments</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Departments</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.departments.php');
	
	$objDepartment = new departments();
	
	$arrDepartments = $objDepartment->fnGetDepartmentById($_REQUEST['id']);
	
	foreach($arrDepartments as $arrDepartmentvalue)
	{
		$tpl->SetAllValues($arrDepartmentvalue);
	}

	$tpl->pparse('main',false);
?>