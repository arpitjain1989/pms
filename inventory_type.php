<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('inventory_type.html','main_container');

	/* Rights management */
	$PageIdentifier = "InventoryType";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","Inventory Type");
	
	/* Set breadcrumb */
	$breadcrumb = '<li><a href="inventory_type_list.php">Manage Inventory Type</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Inventory Type</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.inventory_type.php');
	$objInventoryType = new inventory_type();

	include_once('includes/class.inventory_attributes.php');
	$objInventoryAttribute = new inventory_attributes();

	$arrSelectedAttributes = array();

	/* Set values for update mode */
	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "update" && isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		$inventory_type = $objInventoryType->fnGetInventoryTypeById($_REQUEST["id"]);
		if(count($inventory_type) > 0)
		{
			$arrSelectedAttributes = $objInventoryType->fnGetInventoryAttributeIdByType($inventory_type["id"]);
			$tpl->SetAllValues($inventory_type);
		}
	}

	/* Save inventory type */
	if(isset($_POST["action"]) && trim($_POST["action"]) == "InventoryType")
	{
		$inventory_type_status = $objInventoryType->fnSaveInventoryType($_POST);

		if($inventory_type_status == 1)
		{
			header("Location: inventory_type_list.php?info=success");
			exit;
		}
		else if($inventory_type_status == 0)
		{
			header("Location: inventory_type_list.php?info=err");
			exit;
		}
	}

	$tpl->set_var("FillInventoryMainAttributesBlock","");
	$tpl->set_var("FillNoInventoryMainAttributesBlock","");

print_r($arrSelectedAttributes);

	$arrAttributes = $objInventoryAttribute->fnMainAttributes();
	if(count($arrAttributes) > 0)
	{
		foreach($arrAttributes as $curAttribute)
		{
			$checked_txt = "";
			if(in_array($curAttribute["id"], $arrSelectedAttributes))
				$checked_txt = "checked='checked'";
			
			$tpl->set_var("checked_txt",$checked_txt);
			
			$tpl->set_var("attribute_id",$curAttribute["id"]);
			$tpl->set_var("attribute_name",$curAttribute["attribute_name"]);
			
			$tpl->parse("FillInventoryMainAttributesBlock",true);
		}
	}
	else
	{
		$tpl->parse("FillNoInventoryMainAttributesBlock",false);
	}

	$tpl->pparse('main',false);

?>
