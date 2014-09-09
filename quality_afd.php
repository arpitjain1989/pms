<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('quality_afd.html','main_container');

	$PageIdentifier = "QualityAFD";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Add Quality AFD");
	$breadcrumb = '<li><a href="quality_afd_list.php">Manage Quality AFD</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add Quality AFD</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.quality_form.php');
	$objQualityForm = new quality_form();

	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "update" && isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		$arrQualityAfd = $objQualityForm->fnGetAfdById($_REQUEST["id"]);
		if(count($arrQualityAfd) > 0)
		{
			$tpl->SetAllValues($arrQualityAfd);
		}
	}

	if(isset($_POST["action"]) && trim($_POST["action"]) == "SaveQualityAFD")
	{
		$quality_afd_status = $objQualityForm->fnSaveQualityAfd($_POST);

		if($quality_afd_status == 1)
		{
			header("Location: quality_afd_list.php?info=suc");
			exit;
		}
		else if($quality_afd_status == 0)
		{
			header("Location: quality_afd_list.php?info=err");
			exit;
		}
	}
	
	/* Fill Quality Form Type */
	$tpl->set_var("FillQualityParameterBlock","");
	$arrQualityParameter = $objQualityForm->fnGetAllQualityParameters();
	if(count($arrQualityParameter) > 0)
	{
		foreach($arrQualityParameter as $curQualityParameter)
		{
			$tpl->set_var("parameter_id",$curQualityParameter["id"]);
			$tpl->set_var("parameter_name",$curQualityParameter["parameter_title"] . " - " . $curQualityParameter["form_type"]);
			
			$tpl->parse("FillQualityParameterBlock",true);
		}
	}

	$tpl->pparse('main',false);
?>
