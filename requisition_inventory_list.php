<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('requisition_inventory_list.html','main_container');

	/* Rights management */
	$PageIdentifier = "RequisitionInventory";
	include_once('userrights.php');
	
	/* Set heading */
	$tpl->set_var("mainheading","Manage Requistion Inventory");
	
	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Manage Requistion Inventory</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.requisition_inventory.php');

	/* Display message block */
	$tpl->set_var("DisplayMessageBlock","");
	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'success':
				$messageClass = "alert-success";
				$message = "Requisition Inventory added successfully";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Requisition Inventory already added. Cannot add again.";
				break;
		}

		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	$objRequisitionInventory = new requisition_inventory();
	$arrRequisitionInventory = $objRequisitionInventory->fnGetAllRequisitionInventory();

	/* Display list */
	$tpl->set_var("FillRequisitionInventoryList","");
	if(count($arrRequisitionInventory) >0)
	{
		foreach($arrRequisitionInventory as $curRequisitionInventory)
		{
			$tpl->SetAllValues($curRequisitionInventory);
			$tpl->parse("FillRequisitionInventoryList",true);
		}
	}

	$tpl->pparse('main',false);

?>
