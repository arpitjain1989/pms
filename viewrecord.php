<?php 

	include('common.php');

	$tpl = new Template($app_path);

	include("includes/class.quality_form.php");
	$objQualityForm = new quality_form();

	$tpl->load_file("template.html","main");
	$tpl->load_file("viewrecord.html","main_container");

	$PageIdentifier = "QualityLevelingForm";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Quality Leveling Data");
	$breadcrumb = '<li><a href="qaform.php">Quality Leveling Form</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Quality Leveling Data</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	$tpl->set_var("DisplayMessageBlock","");

	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'suc':
				$messageClass = "alert-success";
				$message = "Record saved successfully.";
				break;
			case 'disabled':
				$messageClass = "alert-error";
				$message = "Data editing is disabled.";
				break;
			case 'errdup':
				$messageClass = "alert-error";
				$message = "Error saving data, Record Id already exists.";
				break;
		}

		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	$arrFormDate = $objQualityForm->getFormDates();

	if(isset($_SESSION['date']) && trim($_SESSION['date']) != "")
		$tpl->set_var("newdate",$_SESSION['date']);

	if(isset($_SESSION['form_id']) && trim($_SESSION['form_id']) != "")
		$tpl->set_var("formid",$_SESSION['form_id']);

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
