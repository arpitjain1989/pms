<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('get_inventory_attributes_by_type.html','main_container');

	include_once('includes/class.inventory_type.php');
	include_once('includes/class.inventory_attributes.php');
	include_once('includes/class.stock_register.php');

	$objInventoryType = new inventory_type();
	$objInventoryAttribute = new inventory_attributes();
	$objStockRegister = new stock_register();

	$tpl->set_var("DisplayInventoryAttributesBlock","");
	$tpl->set_var("DisplayNoInventoryAttributesBlock","");
	$tpl->set_var("FillSubAttributes","");

	if(isset($_REQUEST["typeid"]) && trim($_REQUEST["typeid"]) != "")
	{
		
		$arrSelectedAttributes = array();
		if(isset($_REQUEST["stock_id"]) && trim($_REQUEST["stock_id"]) != "")
		{
			$arrSelectedAttributes = $objStockRegister->fnGetSelectedStockAttributes(trim($_REQUEST["stock_id"]));
		}
		
		/* Get selected attributes for the specific type */
		$arrAttributes = $objInventoryType->fnGetInventoryAttributeByType($_REQUEST["typeid"]);
		if(is_array($arrAttributes) && count($arrAttributes) > 0)
		{
			foreach($arrAttributes as $curAttribute)
			{
				$tpl->set_var("parent_attribute_id",$curAttribute["id"]);
				$tpl->set_var("parent_attribute_name",$curAttribute["attribute_name"]);
				
				/* Fetch all the sub attributs for the given attribute to create a drop down */
				$tpl->set_var("FillSubAttributes","");
				$arrSubAttributes = $objInventoryAttribute->fnGetSubAttributesByParentAttribute($curAttribute["id"]);
				if(is_array($arrSubAttributes) && count($arrSubAttributes) > 0)
				{
					foreach($arrSubAttributes as $curAttribute)
					{
						$tpl->set_var("sub_menu_id",$curAttribute["id"]);
						$tpl->set_var("sub_menu_name",$curAttribute["attribute_name"]);
						
						$strSeleceted = "";
						if(in_array($curAttribute["id"], $arrSelectedAttributes))
							$strSeleceted = "selected='selected'";
						
						$tpl->set_var("strSeleceted", $strSeleceted);
						
						$tpl->parse("FillSubAttributes",true);
					}
				}
				
				$tpl->parse("DisplayInventoryAttributesBlock",true);
			}
		}
		else
		{
			/* No attributes specified for the given type */
			$tpl->parse("DisplayNoInventoryAttributesBlock",false);
		}
	}
	else
	{
		/* No attributes specified for the given type */
		$tpl->parse("DisplayNoInventoryAttributesBlock",false);
	}

	$tpl->pparse('main_container',false);

?>
