<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('requisition_inventory_view.html','main_container');

	/* Rights management */
	$PageIdentifier = "RequisitionInventory";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","View Requisition Inventory");
	
	/* Set breadcrumb */
	$breadcrumb = '<li><a href="requisition_inventory_list.php">Manage Requisition Inventory</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Requisition Inventory</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.requisition_inventory.php');
	include_once('includes/class.designation.php');

	$objRequisitionInventory = new requisition_inventory();
	$objDesignation = new designations();

	$tpl->set_var("DisplayRequisitionInventoryInformationBlock","");
	$tpl->set_var("DisplayNoRequisitionInventoryInformationBlock","");

	if(isset($_REQUEST['id']) && trim($_REQUEST['id']) != "")
	{
		$arrRequisitionInventory = $objRequisitionInventory->fnGetRequisitionInventoryById($_REQUEST['id']);
		
		$allowed_designation_text = $comma = "";
		
		if(count($arrRequisitionInventory) > 0)
		{
			$tpl->SetAllValues($arrRequisitionInventory);
			
			if(isset($arrRequisitionInventory["allowed_designation"]) && trim($arrRequisitionInventory["allowed_designation"]) != "")
			{
				$arrDesignation = explode(",", $arrRequisitionInventory["allowed_designation"]);
				if(count($arrDesignation) > 0)
				{
					foreach($arrDesignation as $curDesignation)
					{
						$desName = $objDesignation->fnGetDesignationNameById($curDesignation);
						if($desName != "")
						{
							$allowed_designation_text .= $comma . $desName;
							$comma = ", ";
						}
					}
				}
			}
			
			$tpl->set_var("allowed_designation_text",$allowed_designation_text);

			$tpl->parse("DisplayRequisitionInventoryInformationBlock",false);
		}
		else
		{
			$tpl->parse("DisplayNoRequisitionInventoryInformationBlock",false);
		}
	}
	else
	{
		$tpl->parse("DisplayNoRequisitionInventoryInformationBlock",false);
	}
	
	$tpl->pparse('main',false);
?>
