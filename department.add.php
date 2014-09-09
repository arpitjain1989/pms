<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('department.add.html','main_container');

	$PageIdentifier = "Departments";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Add / Edit Departments");
	$breadcrumb = '<li><a href="departments.php">Manage Departments</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add / Edit Departments</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.departments.php');
	
	$objdepartments = new departments();
	
	if(isset($_REQUEST['id']))
	{
		$tpl->set_var('departmentid',"$_REQUEST[id]");
	}
	$tpl->set_var('action','hdnadd');
	if(isset($_POST['hdnaction']) && $_POST['hdnaction'] == 'hdnadd')
	{
		$insertdata = $objdepartments->fnInsertDepartment($_POST);
		if($insertdata)
		{
			header("Location: departments.php?info=succ");
			exit;
		}
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] =='update')
	{
			$updateDepartments = $objdepartments->fnUpdateDepartments($_POST);
			if($updateDepartments)
		{
			header("Location: departments.php?info=update");
			exit;
		}
	}
	
	if(isset($_REQUEST['action']) && $_REQUEST['action']='update')
	{
		$arrDepartments = $objdepartments->fnGetDepartmentById($_REQUEST['id']);
		foreach($arrDepartments as $arrDepartmentvalue)
		{
			$tpl->SetAllValues($arrDepartmentvalue);
		}
		$tpl->set_var('action','update');
	}
	
	
	$tpl->pparse('main',false);
?>
