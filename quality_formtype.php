<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('quality_formtype.html','main_container');

	$PageIdentifier = "QualityFormType";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Add Quality Form Type");
	$breadcrumb = '<li><a href="quality_fromtype_list.php">Manage Quality Form Type</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add Quality Form Type</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.quality_form.php');
	$objQualityForm = new quality_form();

	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "update" && isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		$arrQualityForm = $objQualityForm->fnGetQualityFormTypeById($_REQUEST["id"]);
		if(count($arrQualityForm) > 0)
		{
			$tpl->SetAllValues($arrQualityForm);
		}
	}

	if(isset($_POST["action"]) && trim($_POST["action"]) == "SaveQualityFormType")
	{
		$quality_form_status = $objQualityForm->fnSaveQualityFormType($_POST);

		if($quality_form_status == 1)
		{
			header("Location: quality_formtype_list.php?info=suc");
			exit;
		}
		else if($quality_form_status == 0)
		{
			header("Location: quality_formtype_list.php?info=err");
			exit;
		}
	}

	$tpl->pparse('main',false);
?>
