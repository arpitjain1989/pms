<?php 
	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('job_type.html','main_container');

	$PageIdentifier = "BillingMethod";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Billing Method");
	$breadcrumb = '<li class="active">Manage Job Types</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.job_type.php');
	
	$objJobType = new job_type();
	$arrJobType = $objJobType->fnGetAllJobType();
	
	
	if(isset($_REQUEST['info']))
	{
		if($_REQUEST['info'] == 'succ')
		{
			$tpl->set_var('message',"Job Type inserted successfully.");
		}
		else if($_REQUEST['info'] == 'update')
		{
			$tpl->set_var('message',"Job Type updated successfully.");
		}
		else if($_REQUEST['info'] == 'delete')
		{
			$tpl->set_var('message',"Job Type deleted successfully.");
		}
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] = 'delete')
	{
		$deltejobtype = $objJobType->fnDeleteJobType($_POST);
		if($deltejobtype)
		{
			header("Location: job_type.php?info=delete");
		}
		else
		{
			
		}
	}
	
	$tpl->set_var("FillJobTypeValues","");

	if(count($arrJobType) >0)
	{
		foreach($arrJobType as $arrJobTypevalue)
		{	
			$tpl->SetAllValues($arrJobTypevalue);
			if($arrJobTypevalue['has_target'] == '0')
			{
				$tpl->set_var("state","Yes");
			}
			else
			{
				$tpl->set_var("state","No");
			}
			$tpl->parse("FillJobTypeValues",true);
		}
	}
	else
	{
		$tpl->set_var("FillHideDelete","");
		$tpl->set_var("norecord","No records found.");
	}
	
	
	
	
	$tpl->pparse('main',false);
?>