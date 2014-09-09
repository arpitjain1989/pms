<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('inventory_make.html','main_container');

	/* Rights management */
	$PageIdentifier = "InventoryMake";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","Inventory Make");
	
	/* Set breadcrumb */
	$breadcrumb = '<li><a href="inventory_make_list.php">Manage Inventory Make</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Inventory Make</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.inventory_make.php');

	$objInventoryMake = new inventory_make();

	/* Set values for update mode */
	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "update" && isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		$inventory_make = $objInventoryMake->fnGetInventoryMakeById($_REQUEST["id"]);
		if(count($inventory_make) > 0)
		{
			$tpl->SetAllValues($inventory_make);
		}
	}

	/* Save inventory make */
	if(isset($_POST["action"]) && trim($_POST["action"]) == "InventoryMake")
	{
		$inventory_make_status = $objInventoryMake->fnSaveInventoryMake($_POST);

		if($inventory_make_status == 1)
		{
			header("Location: inventory_make_list.php?info=success");
			exit;
		}
		else if($inventory_make_status == 0)
		{
			header("Location: inventory_make_list.php?info=err");
			exit;
		}
	}

	$tpl->pparse('main',false);

?>
