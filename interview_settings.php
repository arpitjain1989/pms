<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('interview_settings.html','main_container');

	$PageIdentifier = "InterviewSettings";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Interview Settings");
	$breadcrumb = '<li class="active">Manage Interview Settings</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.designation.php');
	include_once('includes/class.interview_settings.php');
	
	$objDesignation = new designations();
	$objInterviewSettings = new interview_settings();

	$message = "";
	$messageClass = "";
	
	$tpl->set_var("DisplayMessageBlock","");

	if(isset($_REQUEST['info']))
	{
		switch($_REQUEST["info"])
		{
			case 'suc':
				$messageClass = "alert-success";
				$message = "Interview settings saved.";
				break;
		}
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);
			
			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	if(isset($_POST["action"]) && trim($_POST["action"]) == "SaveInterviewSettings")
	{
		if($objInterviewSettings->fnSaveInterviewSettings($_POST))
		{
			header("Location: interview_settings.php?info=suc");
			exit;
		}
	}

	$arrInterviewSettings = $objInterviewSettings->fnGetInterviewSettings();
	$arrInterviewers = array(0);
	$arrManagers = array(0);
	if(count($arrInterviewSettings) > 0)
	{
		$tpl->set_var("id", $arrInterviewSettings["id"]);

		if(isset($arrInterviewSettings["interviewer_designations"]) && trim($arrInterviewSettings["interviewer_designations"]) != "")
			$arrInterviewers = explode(",",$arrInterviewSettings["interviewer_designations"]);

		if(isset($arrInterviewSettings["managers_designations"]) && trim($arrInterviewSettings["managers_designations"]) != "")
			$arrManagers = explode(",",$arrInterviewSettings["managers_designations"]);
	}

	/* Fill designations for interviewers and managers */
	$tpl->set_var("FillInterviewerDesignationsBlock","");
	$tpl->set_var("FillManagersDesignationsBlock","");
	$arrDesignation = $objDesignation->fnGetAllDesignations();
	if(count($arrDesignation) > 0)
	{
		foreach($arrDesignation as $curDesignation)
		{
			$tpl->set_var("designation_id", $curDesignation["id"]);
			$tpl->set_var("designation_title", $curDesignation["title"]);

			$selected_interviewer = "";
			if(in_array($curDesignation["id"],$arrInterviewers))
				$selected_interviewer = "selected='selected'";
			$tpl->set_var("selected_interviewer", $selected_interviewer);

			$selected_managers = "";
			if(in_array($curDesignation["id"],$arrManagers))
				$selected_managers = "selected='selected'";
			$tpl->set_var("selected_managers", $selected_managers);

			$tpl->parse("FillInterviewerDesignationsBlock",true);
			$tpl->parse("FillManagersDesignationsBlock",true);
		}
	}

	$tpl->pparse('main',false);

?>
