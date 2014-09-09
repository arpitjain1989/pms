<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('inventory_attributes_view.html','main_container');

	/* Rights management */
	$PageIdentifier = "InventoryAttributes";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","View Inventory Attribute");
	
	/* Set breadcrumb */
	$breadcrumb = '<li><a href="inventory_make_list.php">Manage Inventory Attribute</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Inventory Attribute</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.inventory_attributes.php');

	$objInventoryAttribute = new inventory_attributes();

	$tpl->set_var("DisplayInventoryAttributeInformationBlock","");
	$tpl->set_var("DisplayNoInventoryAttributeInformationBlock","");

	if(isset($_REQUEST['id']) && trim($_REQUEST['id']) != "")
	{
		$arrInventoryAttribute = $objInventoryAttribute->fnGetInventoryAttributesById($_REQUEST['id']);
		
		if(count($arrInventoryAttribute) > 0)
		{
			$tpl->SetAllValues($arrInventoryAttribute);
			$tpl->parse("DisplayInventoryAttributeInformationBlock",false);
		}
		else
		{
			$tpl->parse("DisplayNoInventoryAttributeInformationBlock",false);
		}
	}
	else
	{
		$tpl->parse("DisplayNoInventoryAttributeInformationBlock",false);
	}
	
	$tpl->pparse('main',false);
?>
