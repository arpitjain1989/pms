<?php

	include('common.php');

	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('current_openings.html','main_container');

	$PageIdentifier = "CurrentOpenings";
	include_once('userrights.php');
	$tpl->set_var("mainheading","Corrent Openings");
	$breadcrumb = '<li class="active">Corrent Openings</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.designation.php');
	
	$objDesignation = new designations();
	
	$tpl->set_var("DisplayCurrentOpenings","");
	$tpl->set_var("DisplayNoCurrentOpenings","");

	if(isset($_POST["hdnaction"]) && trim($_POST["hdnaction"]) == "addOpenings")
	{
		//print_r($_POST);
		
		if($objDesignation->fnAddCurrentOpenings($_POST))
		{
			header("Location: current_openings.php?info=succ");
			exit;
		}
		else
		{
			header("Location: current_openings.php?info=err");
			exit;
		}
	}

	$AllDesignations = $objDesignation->fnGetAllDesignations();
	$currentOpenings = $objDesignation->fnGetCurrentDesignations();
	//echo '<pre>'; print_r($currentOpenings);
	
	
	if(count($AllDesignations) > 0)
	{
		foreach($AllDesignations as $Designations)
		{
			$tpl->set_var("shiftid",$Designations["id"]);
			$tpl->set_var("shifttitle",$Designations["title"]);
			
			$strChecked = "";
			if(in_array($Designations["id"],$currentOpenings))
					$strChecked = "checked='checked'";
			
			$tpl->set_var("strChecked",$strChecked);
			$tpl->parse("DisplayDesignation",true);
		}
		
		$tpl->parse("DisplayCurrentOpenings",false);
	}
	else
	{
		$tpl->parse("DisplayNoCurrentOpenings",false);
	}
	
	
	$tpl->pparse('main',false);
?>
