<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('inventory_attributes.html','main_container');

	/* Rights management */
	$PageIdentifier = "InventoryAttributes";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","Inventory Attributes");
	
	/* Set breadcrumb */
	$breadcrumb = '<li><a href="inventory_attributes_list.php">Manage Inventory Attributes</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Inventory Attributes</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.inventory_attributes.php');

	$objInventoryAttributes = new inventory_attributes();

	/* Set values for update mode */
	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "update" && isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		$inventory_attributes = $objInventoryAttributes->fnGetInventoryAttributesById($_REQUEST["id"]);
		if(count($inventory_attributes) > 0)
		{
			$tpl->SetAllValues($inventory_attributes);
		}
	}

	/* Save inventory attribute */
	if(isset($_POST["action"]) && trim($_POST["action"]) == "InventoryAttribute")
	{
		$inventory_attributes_status = $objInventoryAttributes->fnSaveInventoryAttributes($_POST);

		if($inventory_attributes_status == 1)
		{
			header("Location: inventory_attributes_list.php?info=success");
			exit;
		}
		else if($inventory_make_status == 0)
		{
			header("Location: inventory_attributes_list.php?info=err");
			exit;
		}
	}

	/* Fill main attribute */
	$tpl->set_var("FillParentCategoryBlock","");
	$arrMainAttributes = $objInventoryAttributes->fnMainAttributes();
	if(count($arrMainAttributes) > 0)
	{
		foreach($arrMainAttributes as $curMainAttribute)
		{
			$tpl->set_var("parent_id",$curMainAttribute["id"]);
			$tpl->set_var("parent_name",$curMainAttribute["attribute_name"]);
			
			$tpl->parse("FillParentCategoryBlock",true);
		}
	}
	
	$tpl->pparse('main',false);

?>
