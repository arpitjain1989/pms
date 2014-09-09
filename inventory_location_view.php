<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('inventory_location_view.html','main_container');

	/* Rights management */
	$PageIdentifier = "InventoryLocation";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","View Inventory Location");
	
	/* Set breadcrumb */
	$breadcrumb = '<li><a href="inventory_location_list.php">Manage Inventory Location</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Inventory Location</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.inventory_location.php');

	$objInventoryLocation = new inventory_location();

	$tpl->set_var("DisplayInventoryLocationInformationBlock","");
	$tpl->set_var("DisplayNoInventoryLocationInformationBlock","");

	if(isset($_REQUEST['id']) && trim($_REQUEST['id']) != "")
	{
		$arrInventoryLocation = $objInventoryLocation->fnGetInventoryLocationById($_REQUEST['id']);
		
		if(count($arrInventoryLocation) > 0)
		{
			$tpl->SetAllValues($arrInventoryLocation);
			$tpl->parse("DisplayInventoryLocationInformationBlock",false);
		}
		else
		{
			$tpl->parse("DisplayNoInventoryLocationInformationBlock",false);
		}
	}
	else
	{
		$tpl->parse("DisplayNoInventoryLocationInformationBlock",false);
	}
	
	$tpl->pparse('main',false);
?>
