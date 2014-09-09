<?php

	include("common.php");
	
	$tpl = new Template($app_path);
	
	$tpl->load_file("template.html","main");
	$tpl->load_file("qaform.html","main_container");

	$PageIdentifier = "QualityLevelingForm";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Quality Leveling Form");
	$breadcrumb = '<li class="active">Quality Leveling Form</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	$arrParameters = array();
	$formName = "";

	$tpl->set_var("action",'formadd');

	include("includes/class.quality_form.php");
	include("includes/class.employee.php");

	$objQualityForm = new quality_form();
	$objEmployee = new employee();

	$message = "";
	$messageClass = "";

	$tpl->set_var("DisplayMessageBlock","");

	if(isset($_REQUEST['info']))
	{
		switch($_REQUEST["info"])
		{
			case 'suc':
				$messageClass = "alert-success";
				$message = "Data Successfully inserted.";
				break;
			case 'error':
				$messageClass = "alert-error";
				$message = "Record already exist, Can not Insert Again.";
				break;
			case 'errdisabled':
				$messageClass = "alert-error";
				$message = "Leveling form is disabled.";
				break;
		}
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);
		}
	}

	/* Fetch master for the leveling form */
	$masterId = $objQualityForm->fnFetchLastMaster();
	$masterName = $objEmployee->fnGetEmployeeNameById($masterId);

	$tpl->set_var("master_name", "-");
	if($masterName != "")
		$tpl->set_var("master_name", $masterName);

	if(isset($_POST["action"]) && $_POST["action"] == "LoadLevelingForm")
	{
		$_SESSION["qa_leveling"]["qa_form_id"] = $_POST["qa_form_id"];
		header("Location: qaform.php");
		exit;
	}

	if(!isset($_SESSION["qa_leveling"]["qa_form_id"]))
		$_SESSION["qa_leveling"]["qa_form_id"] = 0;

	$tpl->set_var("qa_form_id", $_SESSION["qa_leveling"]["qa_form_id"]);

	if(isset($_REQUEST['action']) && $_REQUEST['action'] =='updatepage')
	{
		if($objQualityForm->fnCheckLevelingEntryEnabledById($_REQUEST['recid']))
		{
			$tpl->set_var("action",'formupdate');
			$arrGetRecord = $objQualityForm->fnGetRecordById($_REQUEST['recid'],$_REQUEST['date'],$_REQUEST['ftype']);
		}
		else
		{
			header("Location: viewrecord.php?info=disabled");
			exit;
		}
	}

	if(isset($_POST['hdnaction']))
	{
		if($_POST['hdnaction'] == 'formadd')
		{ 
			$insertStatus = $objQualityForm->insertFormData($_POST);

			if($insertStatus == "-1")
			{
				header("Location: qaform.php?info=errdisabled");
				exit;
			}
			else if($insertStatus == true)
			{
				header("Location: qaform.php?info=suc");
				exit;
			}
			else
			{
				header("Location: qaform.php?info=error");
				exit;
			}
		}
		else if($_POST['hdnaction'] == 'formupdate')
		{
			$updateStatus = $objQualityForm->updateFormData($_POST);
			if($updateStatus == "-1")
			{
				header("Location: viewrecord.php?info=errdup");
				exit;
			}
			else if($updateStatus == "1")
			{
				header("Location: viewrecord.php?info=suc");
				exit;
			}
			else
			{
				header("Location: viewrecord.php?info=error");
				exit;
			}
		}
	}

	/* Fill QA - Form types */
	$arrForm = $objQualityForm->getForm();
	$tpl->set_var("FillForm","");
	if(count($arrForm) > 0)
	{
		foreach($arrForm as $form)
		{
			$tpl->SetAllValues($form);
			$tpl->parse("FillForm",true);
		}
	}

	$tpl->set_var("DisplayFormAFDBlock","");

	if(isset($_SESSION["qa_leveling"]["qa_form_id"]) && $_SESSION["qa_leveling"]["qa_form_id"] != "")
	{
		$formName = $objQualityForm->getFormNameById($_SESSION["qa_leveling"]["qa_form_id"]);
		$arrParameters = $objQualityForm->getParameterByFormId($_SESSION["qa_leveling"]["qa_form_id"]);

		$tpl->set_var('FillParameters','');
		if(count($arrParameters) > 0)
		{
			foreach($arrParameters as $parameters)
			{
				$arrAfdName = $objQualityForm->getAllAfdNames($parameters['para_id']);
				$tpl->set_var('AfdValues','');
				if(count($arrAfdName) > 0)
				{
					foreach($arrAfdName as $afdNames)
					{
						$tpl->setAllValues($afdNames);
						$tpl->parse('AfdValues',true);
					}
				}

				$tpl->setAllValues($parameters);
				if(isset($arrGetRecord[$parameters['para_id']]))
				{
					if($arrGetRecord[$parameters['para_id']]['haserror'] == "Yes")
					{
						$arrGetRecord[$parameters['para_id']]['haserror'] ='1';
					}
					else if($arrGetRecord[$parameters['para_id']]['haserror'] == "No")
					{
						$arrGetRecord[$parameters['para_id']]['haserror'] ='2';
					}
					$tpl->set_var("haserror",$arrGetRecord[$parameters['para_id']]['haserror']);
					$tpl->set_var("afd",$arrGetRecord[$parameters['para_id']]['afdid']);
					$tpl->set_var("formdataid",$arrGetRecord[$parameters['para_id']]['formdataid']);
					$tpl->set_var("newafdid",$arrGetRecord[$parameters['para_id']]['newafdid']);
					$tpl->set_var("recordid",$arrGetRecord[$parameters['para_id']]['recordid']);
					$tpl->set_var("comment",$arrGetRecord[$parameters['para_id']]['comment']);
				}
				$tpl->parse('FillParameters',true);
			}
		}

		$tpl->set_var('form_type',$formName);
		$tpl->parse("DisplayFormAFDBlock",false);
	}
	
	$tpl->pparse("main",false);

?>
