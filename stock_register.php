<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('stock_register.html','main_container');

	/* Rights management */
	$PageIdentifier = "StockRegister";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","Manage Stock Register");
	
	/* Set breadcrumb */
	$breadcrumb = '<li><a href="stock_register_list.php">Manage Stock Register</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Stock Register</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.stock_register.php');
	include_once('includes/class.inventory_location.php');
	include_once('includes/class.inventory_type.php');
	include_once('includes/class.inventory_make.php');
	include_once('includes/class.inventory_vendor.php');

	$objStockRegister = new stock_register();
	$objInventoryLocation = new inventory_location();
	$objInventoryType = new inventory_type();
	$objInventoryMake = new inventory_make();
	$objInventoryVendor = new inventory_vendor();

	/* Save stock register */
	if(isset($_POST["action"]) && trim($_POST["action"]) == "StockRegister")
	{
		$stock_register_status = $objStockRegister->fnSaveStockRegister($_POST);

		if($stock_register_status === true)
		{
			//header("Location: stock_summary.php?info=success");
			header("Location: stock_register_list.php?info=success&type=".$_POST["inventory_type_id"]);
			exit;
		}
		else if($stock_register_status === false)
		{
			//header("Location: stock_summary.php?info=err");
			header("Location: stock_register_list.php?info=err&type=".$_POST["inventory_type_id"]);
			exit;
		}
		else
		{
			//header("Location: stock_summary.php?info=success&id=".$stock_register_status);
			header("Location: stock_register_list.php?info=success&id=".$stock_register_status."&type=".$_POST["inventory_type_id"]);
			exit;
		}
	}

	/* Set values for update mode */
	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "update" && isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		$stock_register = $objStockRegister->fnGetStockRegisterById($_REQUEST["id"]);
		if(count($stock_register) > 0)
		{
			$tpl->SetAllValues($stock_register);
			
			/*if($stock_register["status"] == '3')
			{
				$setchecked = "";
				if(isset($stock_register["isreturnable"]) && $stock_register["isreturnable"] == '1')
					$setchecked = "checked='checked'";
				$tpl->set_var("setchecked", $setchecked);
			}
			else
			{
				$tpl->set_var("setchecked", "checked='checked'");
			}*/
		}
	}
	/*else
	{
		$tpl->set_var("setchecked", "checked='checked'");
	}*/

	/* Fill inventory type */
	$tpl->set_var("FillInventoryType","");
	$arrInventoryType = $objInventoryType->fnGetAllInventoryType();
	if(count($arrInventoryType) > 0)
	{
		foreach($arrInventoryType as $curInventoryType)
		{
			$tpl->set_var("type_id",$curInventoryType["id"]);
			$tpl->set_var("type_name",$curInventoryType["type"]);
			
			$tpl->parse("FillInventoryType",true);
		}
	}
	
	/* Fill inventory make */
	$tpl->set_var("FillInventoryMake","");
	$arrInventoryMake = $objInventoryMake->fnGetAllInventoryMake();
	if(count($arrInventoryMake) > 0)
	{
		foreach($arrInventoryMake as $curInventoryMake)
		{
			$tpl->set_var("make_id",$curInventoryMake["id"]);
			$tpl->set_var("make_name",$curInventoryMake["make"]);
			
			$tpl->parse("FillInventoryMake",true);
		}
	}
	
	/* Fill inventory location */
	$tpl->set_var("FillInventoryLocation","");
	//$arrInventoryLocation = $objInventoryLocation->fnGetAllInventoryLocation();
	$arrInventoryLocation = $objInventoryLocation->fnGetAllVisibleLocations();
	if(count($arrInventoryLocation) > 0)
	{
		foreach($arrInventoryLocation as $curInventoryLocation)
		{
			$tpl->set_var("inventory_location_id",$curInventoryLocation["id"]);
			$tpl->set_var("inventory_location_name",$curInventoryLocation["location_name"]);
			
			$tpl->parse("FillInventoryLocation",true);
		}
	}

	/* Fill inventory vendor */
	$tpl->set_var("FillInventoryVendor","");
	$tpl->set_var("FillSendToVendor","");
	$arrInventoryVendor = $objInventoryVendor->fnGetAllInventoryVendor();
	if(count($arrInventoryVendor) > 0)
	{
		foreach($arrInventoryVendor as $curInventoryVendor)
		{
			$tpl->set_var("inventory_vendor_id",$curInventoryVendor["id"]);
			$tpl->set_var("inventory_vendor_name",$curInventoryVendor["vendor_name"]);
			
			$tpl->parse("FillInventoryVendor",true);
			$tpl->parse("FillSendToVendor",true);
		}
	}
	
	/* Fill warranty year & month */
	$tpl->set_var("FillWarrantyYear","");
	$tpl->set_var("FillWarrantyMonth","");

	for($i=1; $i<13; $i++)
	{
		if($i < 11)
		{
			$tpl->set_var("year",$i);
			$tpl->parse("FillWarrantyYear",true);
		}

		$tpl->set_var("month",$i);
		$tpl->parse("FillWarrantyMonth",true);
	}

	$tpl->pparse('main',false);

?>
