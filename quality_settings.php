<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('quality_settings.html','main_container');

	$PageIdentifier = "QualitySettings";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Quality Settings");
	$breadcrumb = '<li class="active">Manage Quality Settings</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	$tpl->set_var("DisplayMessageBlock","");

	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'suc':
				$messageClass = "alert-success";
				$message = "Quality settings saved.";
				break;
		}

		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	include_once('includes/class.quality_form.php');
	include_once('includes/class.employee.php');
	include_once('includes/class.roles.php');

	$objQualityForm = new quality_form();
	$objEmployee = new employee();
	$objRoles = new roles();

	if(isset($_POST["action"]) && trim($_POST["action"]) == "SaveQualitySettings")
	{
		$objQualityForm->fnSaveQualitySettings($_POST["master_id"], $_POST["leveling_status"]);
		header("Location: quality_settings.php?info=suc");
		exit;
	}

	/* Fetch last master */
	$masterId = $objQualityForm->fnFetchLastMaster();
	$tpl->set_var("master_id", $masterId);

	if($objQualityForm->fnCheckLevelingEntryEnabled())
		$tpl->set_var("leveling_status", "0");
	else
		$tpl->set_var("leveling_status", "1");

	/* Fill Employee For Master */
	$arrRoles = $objRoles->fnGetRoleForQualityMasterSelection();
	$tpl->set_var("DisplayQualityMasterBlock","");
	$arrEmployee = $objEmployee->fnGetEmployeeByRoles($arrRoles);
	if(count($arrEmployee) > 0)
	{
		foreach($arrEmployee as $curEmployee)
		{
			$tpl->set_var("employee_id",$curEmployee["id"]);
			$tpl->set_var("employee_name",$curEmployee["name"]);

			$tpl->parse("DisplayQualityMasterBlock",true);
		}
	}

	$tpl->pparse('main',false);
?>
