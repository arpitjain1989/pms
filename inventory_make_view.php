<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('inventory_make_view.html','main_container');

	/* Rights management */
	$PageIdentifier = "InventoryMake";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","View Inventory Make");
	
	/* Set breadcrumb */
	$breadcrumb = '<li><a href="inventory_make_list.php">Manage Inventory Make</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Inventory Make</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.inventory_make.php');

	$objInventoryMake = new inventory_make();

	$tpl->set_var("DisplayInventoryMakeInformationBlock","");
	$tpl->set_var("DisplayNoInventoryMakeInformationBlock","");

	if(isset($_REQUEST['id']) && trim($_REQUEST['id']) != "")
	{
		$arrInventoryMake = $objInventoryMake->fnGetInventoryMakeById($_REQUEST['id']);
		
		if(count($arrInventoryMake) > 0)
		{
			$tpl->SetAllValues($arrInventoryMake);
			$tpl->parse("DisplayInventoryMakeInformationBlock",false);
		}
		else
		{
			$tpl->parse("DisplayNoInventoryMakeInformationBlock",false);
		}
	}
	else
	{
		$tpl->parse("DisplayNoInventoryMakeInformationBlock",false);
	}
	
	$tpl->pparse('main',false);
?>
