<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('inventory_type_view.html','main_container');

	/* Rights management */
	$PageIdentifier = "InventoryType";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","View Inventory Type");
	
	/* Set breadcrumb */
	$breadcrumb = '<li><a href="inventory_type_list.php">Manage Inventory Type</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Inventory Type</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.inventory_type.php');

	$objInventoryType = new inventory_type();

	$tpl->set_var("DisplayInventoryTypeInformationBlock","");
	$tpl->set_var("DisplayNoInventoryTypeInformationBlock","");
	$tpl->set_var("DisplaySelectedAttributes","");
	$tpl->set_var("DisplayNoSelectedAttributes","");

	if(isset($_REQUEST['id']) && trim($_REQUEST['id']) != "")
	{
		$arrInventoryType = $objInventoryType->fnGetInventoryTypeById($_REQUEST['id']);
		
		if(count($arrInventoryType) > 0)
		{
			$tpl->SetAllValues($arrInventoryType);
			
			/* Display selected attributes */
			$arrSelectedAttributes = $objInventoryType->fnGetInventoryAttributeByType($arrInventoryType["id"]);
			if(count($arrSelectedAttributes) > 0)
			{
				foreach($arrSelectedAttributes as $curAttribute)
				{
					$tpl->set_var("selected_attribute_name", $curAttribute["attribute_name"]);
					$tpl->parse("DisplaySelectedAttributes",true);
				}
			}
			else
			{
				$tpl->parse("DisplayNoSelectedAttributes",false);
			}
			
			$tpl->parse("DisplayInventoryTypeInformationBlock",false);
		}
		else
		{
			$tpl->parse("DisplayNoInventoryTypeInformationBlock",false);
		}
	}
	else
	{
		$tpl->parse("DisplayNoInventoryTypeInformationBlock",false);
	}
	
	$tpl->pparse('main',false);
?>
