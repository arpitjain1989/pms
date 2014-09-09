<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('settings.html','main_container');
	
	$PageIdentifier = "SiteSettings";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Site settings");
	$breadcrumb = '<li class="active">Site settings</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.admin.php');
	
	$objAdmin = new admin();
	
	$tpl->set_var("MessageSuccess","");
	
	if(isset($_REQUEST['info']))
	{
		if($_REQUEST['info'] == 'succ')
		{
			$tpl->set_var("messageclass","alert-success");
			$tpl->set_var("message","Information successfully updated.");
			$tpl->parse("MessageSuccess",false);
		}
	}
	
	if(isset($_POST['hdnaction']) && $_POST['hdnaction'] == 'update')
	{
		$updatevalues = $objAdmin->fnUpdateSettings($_POST);
		if($updatevalues)
		{
			header("Location: settings.php?info=succ");
			exit;
		}
	}
	
	$UserDetail = $objAdmin->fnGetUserSettings();
	foreach($UserDetail as $uDetails)
	{
		$tpl->SetAllValues($uDetails);
	}
	
	$tpl->pparse('main',false);
?>