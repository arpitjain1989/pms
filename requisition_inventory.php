<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('requisition_inventory.html','main_container');

	/* Rights management */
	$PageIdentifier = "RequisitionInventory";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","Requisition Inventory");
	
	/* Set breadcrumb */
	$breadcrumb = '<li><a href="requisition_inventory_list.php">Manage Requisition Inventory</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Requisition Inventory</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.requisition_inventory.php');
	include_once('includes/class.designation.php');

	$objRequisitionInventory = new requisition_inventory();
	$objDesignation = new designations();

	/* Set values for update mode */
	$arrSelectedDesignations = array();
	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "update" && isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		$requisition_inventory = $objRequisitionInventory->fnGetRequisitionInventoryById($_REQUEST["id"]);
		if(count($requisition_inventory) > 0)
		{
			/*$set_allow_teamleaders = "";
			if(isset($requisition_inventory["allow_teamleaders"]) && trim($requisition_inventory["allow_teamleaders"]) == "1")
				$set_allow_teamleaders = "checked='checked'";

			$tpl->set_var("set_allow_teamleaders",$set_allow_teamleaders);

			$set_allow_managers = "";
			if(isset($requisition_inventory["allow_managers"]) && trim($requisition_inventory["allow_managers"]) == "1")
				$set_allow_managers = "checked='checked'";

			$tpl->set_var("set_allow_managers",$set_allow_managers);*/

			$set_is_approval_required = "";
			if(isset($requisition_inventory["is_approval_required"]) && trim($requisition_inventory["is_approval_required"]) == "1")
				$set_is_approval_required = "checked='checked'";

			if(isset($requisition_inventory["allowed_designation"]) && trim($requisition_inventory["allowed_designation"]) != "")
				$arrSelectedDesignations = explode(",", $requisition_inventory["allowed_designation"]);

			$tpl->set_var("set_is_approval_required",$set_is_approval_required);

			$tpl->SetAllValues($requisition_inventory);
		}
	}

	/* Save requisition inventory */
	if(isset($_POST["action"]) && trim($_POST["action"]) == "RequisitionInventory")
	{
		$requisition_inventory_status = $objRequisitionInventory->fnSaveRequisitionInventory($_POST);

		if($requisition_inventory_status == 1)
		{
			header("Location: requisition_inventory_list.php?info=success");
			exit;
		}
		else if($requisition_inventory_status == 0)
		{
			header("Location: requisition_inventory_list.php?info=err");
			exit;
		}
	}

	/* Fill designation */
	$tpl->set_var("FillAllowedDesignation","");
	$arrDesignation = $objDesignation->fnGetAllDesignations();
	if(count($arrDesignation) > 0)
	{
		foreach($arrDesignation as $curDesignation)
		{
			$tpl->set_var("allowed_designation_id",$curDesignation["id"]);
			$tpl->set_var("allowed_designation_name",$curDesignation["title"]);

			$set_Selected = "";
			if(in_array($curDesignation["id"],$arrSelectedDesignations))
				$set_Selected = "selected='selected'";
			$tpl->set_var("set_Selected", $set_Selected);

			$tpl->parse("FillAllowedDesignation",true);
		}
	}
	
	$tpl->pparse('main',false);

?>
