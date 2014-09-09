<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('graduation.html','main_container');

	$PageIdentifier = "Graduation";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Add Graduation");
	$breadcrumb = '<li><a href="graduation_list.php">Manage Graduation</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add Graduation</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.graduation.php');

	$objGraduation = new graduation();

	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "update" && isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		$graduation = $objGraduation->fnGetGraduationById($_REQUEST["id"]);
		if(count($graduation) > 0)
		{
			$tpl->SetAllValues($graduation);
		}
	}

	if(isset($_POST["action"]) && trim($_POST["action"]) == "SaveGraduation")
	{
		//echo 'hello'; die;
		$graduation_status = $objGraduation->fnSaveGraduation($_POST);

		if($graduation_status == 1)
		{
			header("Location: graduation_list.php?info=success");
			exit;
		}
		else if($graduation_status == 0)
		{
			header("Location: graduation_list.php?info=err");
			exit;
		}
	}

	$tpl->pparse('main',false);
?>
