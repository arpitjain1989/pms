<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('it_support_designations.html','main_container');

	/* Rights management */
	$PageIdentifier = "ITSupportDesignation";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","Manage IT Support Designations");

	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Manage IT Support Designations</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	/* Include class */
	include_once("includes/class.it_support_designations.php");
	include_once("includes/class.designation.php");

	/* Create object */
	$objItSupportDesignation = new it_support_designations();
	$objDesignation = new designations();

	/* Display message block */
	$tpl->set_var("DisplayMessageBlock","");
	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'success':
				$messageClass = "alert-success";
				$message = "Support designations saved";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Support designations same as previous. Cannot save the data.";
				break;
		}

		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	/* Save Support time information */
	if(isset($_POST["action"]) && trim($_POST["action"]) == 'SaveSupportDesignations')
	{
		if($objItSupportDesignation->fnSaveSupportDesignation($_POST))
			header("Location: it_support_designations.php?info=success");
		else
			header("Location: it_support_designations.php?info=err");
		exit;
	}

	$arrITDesignation = array();

	/* Fill existing values */
	$arrITDesignation = $objItSupportDesignation->fnGetSupportDesignations();

	/* Fill Designations */
	$tpl->set_var("FillDesignationBlock","");
	$arrDesignations = $objDesignation->fnGetAllDesignations();
	if(count($arrDesignations) > 0)
	{
		foreach($arrDesignations as $curDesignations)
		{
			$tpl->set_var("designation_id",$curDesignations["id"]);
			$tpl->set_var("designation_title",$curDesignations["title"]);
		
			$selected = "";
			if(in_array($curDesignations["id"],$arrITDesignation))
			{
				$selected = "selected='selected'";
			}
			$tpl->set_var("selected",$selected);
			
			$tpl->parse("FillDesignationBlock",true);
		}
	}

	$tpl->pparse('main',false);

?>
