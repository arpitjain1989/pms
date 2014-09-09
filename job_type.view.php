<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('job_type.view.html','main_container');

	$PageIdentifier = "BillingMethod";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Billing Method");
	$breadcrumb = '<li><a href="job_type.php">Manage Job Types</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Job Type</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.job_type.php');
	
	$objJobType = new job_type();
	
	$arrJobType = $objJobType->fnGetJobTypeById($_REQUEST['id']);
	if($arrJobType)
	{
		if($arrJobType['status'] == '0')
		{
			$tpl->set_var("stat","Active");
		}
		else
		{
			$tpl->set_var("stat","Active");
		}
	}
	
	foreach($arrJobType as $arrJobTypevalue)
	{
		if($arrJobTypevalue[has_target] == '1')
		{
			$tpl->set_var("haserror","No");
		}
		else if($arrJobTypevalue[has_target] == '0')
		{
			$tpl->set_var("haserror","Yes");
		}
		$tpl->SetAllValues($arrJobTypevalue);
	}

	$tpl->pparse('main',false);
?>