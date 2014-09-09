<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('it_support_time.html','main_container');

	/* Rights management */
	$PageIdentifier = "ITSupportTimings";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","Manage IT Support Timings");

	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Manage IT Support Timings</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	/* Include class */
	include_once("includes/class.it_support_time.php");
	include_once("includes/class.designation.php");

	/* Create object */
	$objItSupportTime = new it_support_time();
	$objDesignation = new designations();

	/* Display message block */
	$tpl->set_var("DisplayMessageBlock","");
	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'success':
				$messageClass = "alert-success";
				$message = "Support time saved";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Support timings same as previous. Cannot save the data.";
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
	if(isset($_POST["action"]) && trim($_POST["action"]) == 'SaveSupportTimings')
	{
		if($objItSupportTime->fnSaveSupportTime($_POST))
			header("Location: it_support_time.php?info=success");
		else
			header("Location: it_support_time.php?info=err");
		exit;
	}

	$arrITDesignation = array();

	/* Fill existing values */
	$arrShiftTiming = $objItSupportTime->fnGetSupportTime();
	if(count($arrShiftTiming) > 0)
	{
		$tpl->SetAllValues($arrShiftTiming);
		if(isset($arrShiftTiming["support_designations"]) && trim($arrShiftTiming["support_designations"]) != '')
		{
			$arrITDesignation = explode(",",$arrShiftTiming["support_designations"]);
		}
	}

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
