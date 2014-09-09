<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('emp_test.add.html','main_container');

	$PageIdentifier = "EmployeeTest";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Add / Edit Employee Test");
	$breadcrumb = '<li><a href="emp_test.php">Manage Test Type</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add / Edit Test Type</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.emp_test.php');
	
	$objEmpTest = new emp_test();

	$arrEmpRootTest = $objEmpTest->fnGetAllRootEmpTest();
	
	
	if(isset($_REQUEST['id']))
	{
		$tpl->set_var('EmpTestid',"$_REQUEST[id]");
	}
	$tpl->set_var('action','hdnadd');
	if(isset($_POST['hdnaction']) && $_POST['hdnaction'] == 'hdnadd')
	{
		$insertdata = $objEmpTest->fnInsertEmpTest($_POST);
		if($insertdata)
		{
			header("Location: emp_test.php?info=succ");
			exit;
		}
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] =='update')
	{
			$updateEmpTest = $objEmpTest->fnUpdateEmpTest($_POST);
			if($updateEmpTest)
		{
			header("Location: emp_test.php?info=update");
			exit;
		}
	}
	
	if(isset($_REQUEST['action']) && $_REQUEST['action']='update')
	{
		$arrEmpTest = $objEmpTest->fnGetEmpTestById($_REQUEST['id']);
		foreach($arrEmpTest as $arrEmpTestvalue)
		{
			$tpl->SetAllValues($arrEmpTestvalue);
		}
		$tpl->set_var('action','update');
	}

	$tpl->set_var("FillEmpRootTestValues","");
	if(count($arrEmpRootTest) > 0 )
	{
		foreach($arrEmpRootTest as $arrEmpRootTestValues)
		{
			$tpl->SetAllValues($arrEmpRootTestValues);
			$tpl->parse("FillEmpRootTestValues",true);
		}
	}
	
	$tpl->pparse('main',false);
?>
