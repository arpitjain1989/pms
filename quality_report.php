<?php
 
	include('common.php');
	
	$tpl = new Template($app_path);
	
	$tpl->load_file("template.html","main");
	$tpl->load_file("quality_report.html","main_container");

	$PageIdentifier = "QualityLevelingReport";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Quality Leveling Report");
	$breadcrumb = '<li class="active">Quality Leveling Report</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include("includes/class.quality_form.php");
	$objQualityForm = new quality_form();
	
	$arrFormDate = $objQualityForm->getFormDates();

	$tpl->set_var("FillFormDate","");
	if(count($arrFormDate) > 0)
	{
		foreach($arrFormDate as $formDate)
		{
			$tpl->SetAllValues($formDate);
			$tpl->parse("FillFormDate",true);
		}
	}

	$arrFormType = $objQualityForm->getForm();

	$tpl->set_var("FillFormType","");
	if(count($arrFormType) > 0)
	{
		foreach($arrFormType as $formtype)
		{
			$tpl->SetAllValues($formtype);
			$tpl->parse("FillFormType",true);
		}
	}
		
	
	$tpl->pparse("main",false);
?>
