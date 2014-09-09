<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('inventory_location.html','main_container');

	/* Rights management */
	$PageIdentifier = "InventoryLocation";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","Inventory Location");
	
	/* Set breadcrumb */
	$breadcrumb = '<li><a href="inventory_location_list.php">Manage Inventory Location</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Inventory Location</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.inventory_location.php');

	$objInventoryLocation = new inventory_location();

	/* Set values for update mode */
	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "update" && isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		$inventory_location = $objInventoryLocation->fnGetInventoryLocationById($_REQUEST["id"]);
		if(count($inventory_location) > 0)
		{
			$tpl->SetAllValues($inventory_location);
		}
	}

	/* Save inventory type */
	if(isset($_POST["action"]) && trim($_POST["action"]) == "InventoryLocation")
	{
		$inventory_location_status = $objInventoryLocation->fnSaveInventoryLocation($_POST);

		if($inventory_location_status == 1)
		{
			header("Location: inventory_location_list.php?info=success");
			exit;
		}
		else if($inventory_location_status == 0)
		{
			header("Location: inventory_location_list.php?info=err");
			exit;
		}
	}

	$tpl->pparse('main',false);

?>
