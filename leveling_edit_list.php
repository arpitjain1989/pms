<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('leveling_edit_list.html','main_container');

	$PageIdentifier = "LevelingDataEdit";
	include_once('userrights.php');
	
	$tpl->set_var("mainheading","Manage Leveling Edit");
	$breadcrumb = '<li class="active">Manage Leveling Edit</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.quality_form.php');

	$tpl->set_var("DisplayMessageBlock","");

	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'suc':
				$messageClass = "alert-success";
				$message = "Quality Leveling saved successfully";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Error saving quality leveling record. Cannot add again.";
				break;
			case 'error':
				$messageClass = "alert-error";
				$message = "Error saving quality leveling record. Cannot add again.";
				break;
		}

		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	if(isset($_POST["action"]) && trim($_POST["action"]) == "SearchLevelingEdit")
	{
		$_SESSION["LevelingEdit"]["date"] = $_POST["search_date"];
		$_SESSION["LevelingEdit"]["formtype"] = $_POST["search_formtype"];
		
		header("Location: leveling_edit_list.php");
		exit;
	}

	$search_formtype = $search_date = "0";
	
	if(isset($_SESSION["LevelingEdit"]["date"]))
		$search_date = $_SESSION["LevelingEdit"]["date"];

	if(isset($_SESSION["LevelingEdit"]["formtype"]))
		$search_formtype = $_SESSION["LevelingEdit"]["formtype"];

	$tpl->set_var("search_date", $search_date);
	$tpl->set_var("search_formtype", $search_formtype);

	$objQualityForm = new quality_form();
	
	/* Fill Records */
	$tpl->set_var("FillQualityLevelingList", "");
	$arrLevelingData = $objQualityForm->fnGetLevelingData($search_date, $search_formtype);
	if(count($arrLevelingData) > 0)
	{
		foreach($arrLevelingData as $curLevelingData)
		{
			$tpl->set_var("user_name", $curLevelingData["name"]);
			$tpl->set_var("record_id", $curLevelingData["recordid"]);
			$tpl->set_var("form_id", $curLevelingData["form_id"]);
			$tpl->set_var("form_date", $curLevelingData["form_date"]);
			$tpl->set_var("formdetail_id", $curLevelingData["formdetail_id"]);

			$tpl->parse("FillQualityLevelingList", true);
		}
	}
	
	/* Fill form types */
	$arrQualityFormType = $objQualityForm->getForm();
	$tpl->set_var("FillQualityFormTypesList","");
	if(count($arrQualityFormType) > 0)
	{
		foreach($arrQualityFormType as $curQualityFormType)
		{
			$tpl->SetAllValues($curQualityFormType);
			$tpl->parse("FillQualityFormTypesList",true);
		}
	}
	
	/* Fill leveling dates */
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

	$tpl->pparse('main',false);

?>
