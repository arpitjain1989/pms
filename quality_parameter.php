<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('quality_parameter.html','main_container');

	$PageIdentifier = "QualityParameter";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Add Quality Parameter");
	$breadcrumb = '<li><a href="quality_parameter_list.php">Manage Quality Parameter</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add Quality Parameter</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.quality_form.php');
	$objQualityForm = new quality_form();

	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "update" && isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		$arrQualityParameter = $objQualityForm->fnGetParameterById($_REQUEST["id"]);
		if(count($arrQualityParameter) > 0)
		{
			$tpl->SetAllValues($arrQualityParameter);
		}
	}

	if(isset($_POST["action"]) && trim($_POST["action"]) == "SaveQualityParameter")
	{
		$quality_parameter_status = $objQualityForm->fnSaveQualityParameter($_POST);

		if($quality_parameter_status == 1)
		{
			header("Location: quality_parameter_list.php?info=suc");
			exit;
		}
		else if($quality_parameter_status == 0)
		{
			header("Location: quality_parameter_list.php?info=err");
			exit;
		}
	}
	
	/* Fill Quality Form Type */
	$tpl->set_var("FillQualityFormTypeBlock","");
	$arrQualityFormType = $objQualityForm->getForm();
	if(count($arrQualityFormType) > 0)
	{
		foreach($arrQualityFormType as $curQualityFormType)
		{
			$tpl->set_var("quality_formtype_id",$curQualityFormType["form_id"]);
			$tpl->set_var("quality_formtype",$curQualityFormType["form_type"]);
			
			$tpl->parse("FillQualityFormTypeBlock",true);
		}
	}

	$tpl->pparse('main',false);
?>
