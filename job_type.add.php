<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('job_type.add.html','main_container');

	$PageIdentifier = "BillingMethod";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Add / Edit Billing Method");
	$breadcrumb = '<li><a href="job_type.php">Manage Job Types</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add / Edit Job Type</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.job_type.php');
	
	$objJobType = new job_type();
	
	$arrJobTypes = $objJobType->fnGetJobTypes($_REQUEST['id']);
	
	$tpl->set_var("FillJob","");
	if(count($arrJobTypes) > 0)
	{
		foreach($arrJobTypes as $arrJobs)
		{
			$tpl->SetAllValues($arrJobs);
			$tpl->parse("FillJob",true);
		}
	}
	
	if(isset($_REQUEST['id']))
	{
		$tpl->set_var('employeeid',"$_REQUEST[id]");
	}
	$tpl->set_var('action','hdnadd');
	if(isset($_POST['hdnaction']) && $_POST['hdnaction'] == 'hdnadd')
	{
		$insertdata = $objJobType->fnInsertJobType($_POST);
		if($insertdata)
		{
			header("Location: job_type.php?info=succ");
			exit;
		}
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] =='update')
	{
			$updateJobType = $objJobType->fnUpdateJobType($_POST);
			if($updateJobType)
		{
			header("Location: job_type.php?info=update");
			exit;
		}
	}
	
	if(isset($_REQUEST['action']) && $_REQUEST['action']='update')
	{
	$arrJobType = $objJobType->fnGetJobTypeById($_REQUEST['id']);
	foreach($arrJobType as $arrJobTypevalue)
	{
		$tpl->SetAllValues($arrJobTypevalue);
	}
		$tpl->set_var('action','update');
	}
	
		
	$tpl->pparse('main',false);
?>