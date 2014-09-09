<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('salary_offered.add.html','main_container');

	$PageIdentifier = "SalaryOffered";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Add / Edit RCT Division");
	$breadcrumb = '<li><a href="salary_offered.php">Manage RCT Division</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add / Edit RCT Division</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.salary_offered.php');
	include_once('includes/class.designation.php');
	
	$objSalaryOffered = new salary_offered();
	$objDesignation = new designations();

	$arrAllDesignations = $objDesignation->fnGetAllDesignations();
	
	if(isset($_REQUEST['id']))
	{
		$tpl->set_var('salary_offer_id',"$_REQUEST[id]");
	}
	$tpl->set_var('action','hdnadd');
	if(isset($_POST['hdnaction']) && $_POST['hdnaction'] == 'hdnadd')
	{
		//echo 'helo'; print_r($_POST); die;
		$insertdata = $objSalaryOffered->fnInsertSalaryOffered($_POST);
		if($insertdata)
		{
			header("Location: salary_offered.php?info=succ");
			exit;
		}
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] =='update')
	{
		$updateRCTDivision = $objSalaryOffered->fnUpdateRCTDivision($_POST);
		if($updateRCTDivision)
		{
			header("Location: salary_offered.php?info=update");
			exit;
		}
	}
	
	if(isset($_REQUEST['action']) && $_REQUEST['action']='update')
	{
		$arrRCTDivision = $objSalaryOffered->fnGetRCTDivisionById($_REQUEST['id']);
		foreach($arrRCTDivision as $arrRCTDivisionvalue)
		{
			$tpl->SetAllValues($arrRCTDivisionvalue);
		}
		$tpl->set_var('action','update');
	}

	//echo '<pre>'; print_r($arrAllDesignations);
	if(count($arrAllDesignations) > 0)
	{
		foreach($arrAllDesignations as $designation)
		{
			
			$tpl->SetAllValues($designation);
			$tpl->parse("FillDesignation",true);
		}
	}
	
	
	$tpl->pparse('main',false);
?>
