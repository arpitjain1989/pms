<?php

	include("common.php");
	include("includes/class.quality_form.php");

	$tpl = new Template($app_path);
	$objQualityForm = new quality_form();

	$tpl->load_file("template.html","main");
	$tpl->load_file("leveling_edit.html","main_container");

	$PageIdentifier = "LevelingDataEdit";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Quality Leveling Edit");
	$breadcrumb = '<li><a href="leveling_edit_list.php">Manage Quality Leveling</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Quality Leveling Edit</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	$arrParameters = array();
	$formName = "";

	$tpl->set_var("action",'formadd');

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
		}
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);
		}
	}

	if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'update')
	{
		$arrGetRecord = $objQualityForm->fnGetRecordByIdForMasterEdit($_REQUEST['recid'],$_REQUEST['date'],$_REQUEST['ftype']);
	}

	if(isset($_POST['hdnaction']) && trim($_POST['hdnaction']) == 'formadd')
	{
		if($objQualityForm->updateMasterFormData($_POST))
		{
			header("Location: leveling_edit_list.php?info=suc");
			exit;
		}
		else
		{
			header("Location: leveling_edit_list.php?info=error");
			exit;
		}
	}

	$tpl->set_var("DisplayFormAFDBlock","");

	if(isset($_REQUEST['ftype']) && $_REQUEST['ftype'] != "")
	{
		$formName = $objQualityForm->getFormNameById($_REQUEST['ftype']);
		$arrParameters = $objQualityForm->getParameterByFormId($_REQUEST['ftype']);

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
					$tpl->set_var("insert_date",$arrGetRecord[$parameters['para_id']]['insert_date']);
				}
				$tpl->parse('FillParameters',true);
			}
		}

		$tpl->set_var('form_type',$formName);
		$tpl->parse("DisplayFormAFDBlock",false);
	}
	
	$tpl->pparse("main",false);

?>
