<?php

	/* Class to manage the designation */

	/* include common file */
	include('common.php');

	/* Create template object */
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('designation.add.html','main_container');

	/* Set module for checking access rights */
	$PageIdentifier = "Designation";
	include_once('userrights.php');

	/* Set header and bread crumb */
	$tpl->set_var("mainheading","Add / Edit Designation");
	$breadcrumb = '<li><a href="designation.php">Manage Designation</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add / Edit Designation</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	/* Include designation class */
	include_once('includes/class.designation.php');

	/* Create object of designation class */
	$objdesignations = new designations();

	$desinationId = 0;
	if(isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
		$desinationId = $_REQUEST["id"];
		
	if(isset($_REQUEST['id']))
	{
		$tpl->set_var('designationid',"$_REQUEST[id]");
	}
	$tpl->set_var('action','hdnadd');
	if(isset($_POST['hdnaction']) && $_POST['hdnaction'] == 'hdnadd')
	{
		$insertdata = $objdesignations->fnInsertDesignation($_POST);
		if($insertdata)
		{
			header("Location: designation.php?info=succ");
			exit;
		}
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] =='update')
	{
		$updateDesignations = $objdesignations->fnUpdateDesignations($_POST);
		if($updateDesignations)
		{
			header("Location: designation.php?info=update");
			exit;
		}
	}

	$selected_delegation_designation = array();
	
	if(isset($_REQUEST['action']) && $_REQUEST['action']=='update')
	{
		$arrDesignations = $objdesignations->fnGetDesignationById($_REQUEST['id']);
		if(count($arrDesignations) > 0)
		{
			$tpl->SetAllValues($arrDesignations);
			
			$selected_delegation_designation = explode(",",$arrDesignations["delegation_designation"]);
		}
		$tpl->set_var('action','update');
	}
	
	/* Fill parent designation designation */
	$arrParentDesignation = $objdesignations->fnGetParentDesignation();

	/* Fill dropdown */
	$tpl->set_var("FillParentDesignationBlock","");
	$tpl->set_var("FillDelegationDesignationBlock","");
	if(count($arrParentDesignation) > 0)
	{
		foreach($arrParentDesignation as $curDesignationId => $curDesignationName)
		{
			$tpl->set_var("designation_id", $curDesignationId);
			$tpl->set_var("designation_title", $curDesignationName);

			if($desinationId != $curDesignationId)
				$tpl->parse("FillParentDesignationBlock",true);

			$set_selected = "";
			if(in_array($curDesignationId,$selected_delegation_designation))
				$set_selected = "selected='selected'";
			$tpl->set_var("set_selected",$set_selected);

			$tpl->parse("FillDelegationDesignationBlock",true);
		}
	}
	
	$tpl->pparse('main',false);
?>
