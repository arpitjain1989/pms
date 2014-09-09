<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('inventory_purchase_report.html','main_container');

	/* Rights management */
	$PageIdentifier = "InventoryPurchaseReport";
	include_once('userrights.php');
	
	/* Set heading */
	$tpl->set_var("mainheading","Inventory Purchase Report");
	
	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Inventory Purchase Report</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.stock_register.php');
	include_once('includes/class.inventory_type.php');
	include_once('includes/class.inventory_make.php');
	include_once('includes/class.inventory_vendor.php');
	include_once('includes/class.inventory_location.php');

	$objStockRegister = new stock_register();
	$objInventoryType = new inventory_type();
	$objInventoryMake = new inventory_make();
	$objInventoryVendor = new inventory_vendor();
	$objInventoryLocation = new inventory_location();

	/* Search inventory purchase */
	if(isset($_POST["action"]) && trim($_POST["action"]) == "InventoryPurchaseReport")
	{
		$_SESSION["InventoryPurchaseReport"]["inventory_type_id"] = trim($_POST["search_inventory_type_id"]);
		$_SESSION["InventoryPurchaseReport"]["inventory_make_id"] = trim($_POST["search_inventory_make_id"]);
		$_SESSION["InventoryPurchaseReport"]["inventory_vendor_id"] = trim($_POST["search_inventory_vendor_id"]);
		$_SESSION["InventoryPurchaseReport"]["inventory_location_id"] = trim($_POST["search_inventory_location_id"]);
		$_SESSION["InventoryPurchaseReport"]["purchase_date_from"] = trim($_POST["search_purchase_date_from"]);
		$_SESSION["InventoryPurchaseReport"]["purchase_date_to"] = trim($_POST["search_purchase_date_to"]);
		$_SESSION["InventoryPurchaseReport"]["unique_id"] = trim($_POST["search_unique_id"]);
		
		header("Location: inventory_purchase_report.php");
		exit;
	}
	
	/* Set values that are selected and stored in session */
	$inventory_location_id = $inventory_type_id = $inventory_make_id = 0;
	$purchase_date_from = $purchase_date_to = Date('Y-m-d');
	$inventory_vendor_id = $unique_id = "";

	if(isset($_SESSION["InventoryPurchaseReport"]["inventory_type_id"]))
		$inventory_type_id = $_SESSION["InventoryPurchaseReport"]["inventory_type_id"];

	if(isset($_SESSION["InventoryPurchaseReport"]["inventory_make_id"]))
		$inventory_make_id = $_SESSION["InventoryPurchaseReport"]["inventory_make_id"];

	if(isset($_SESSION["InventoryPurchaseReport"]["inventory_vendor_id"]))
		$inventory_vendor_id = $_SESSION["InventoryPurchaseReport"]["inventory_vendor_id"];

	if(isset($_SESSION["InventoryPurchaseReport"]["inventory_location_id"]))
		$inventory_location_id = $_SESSION["InventoryPurchaseReport"]["inventory_location_id"];

	if(isset($_SESSION["InventoryPurchaseReport"]["purchase_date_from"]))
		$purchase_date_from = $_SESSION["InventoryPurchaseReport"]["purchase_date_from"];

	if(isset($_SESSION["InventoryPurchaseReport"]["purchase_date_to"]))
		$purchase_date_to = $_SESSION["InventoryPurchaseReport"]["purchase_date_to"];

	if(isset($_SESSION["InventoryPurchaseReport"]["unique_id"]))
		$unique_id = $_SESSION["InventoryPurchaseReport"]["unique_id"];

	$tpl->set_var("search_inventory_type_id",$inventory_type_id);
	$tpl->set_var("search_inventory_make_id",$inventory_make_id);
	$tpl->set_var("search_inventory_vendor_id",$inventory_vendor_id);
	$tpl->set_var("search_inventory_location_id",$inventory_location_id);
	$tpl->set_var("search_purchase_date_from",$purchase_date_from);
	$tpl->set_var("search_purchase_date_to",$purchase_date_to);
	$tpl->set_var("search_unique_id",$unique_id);

	$arrPurchaseInventory = $objStockRegister->fnGetPurchaseInventory($inventory_type_id, $inventory_make_id, $inventory_vendor_id, $inventory_location_id, $purchase_date_from, $purchase_date_to, $unique_id);
	
	/* Export to excel */
	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "export") 
	{
		$filename = "Inventory Purchase Report - ".Date('Y-m-d H:i').".xls";
		
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");;
		header("Content-Disposition: attachment;filename=".$filename);
		header("Content-Transfer-Encoding: binary ");
		
		xlsBOF();

		xlsWriteLabel(0,0,"Inventory Type");
		xlsWriteLabel(0,1,"Inventory Make");
		xlsWriteLabel(0,2,"Invoice No");
		xlsWriteLabel(0,3,"Unique Id");
		xlsWriteLabel(0,4,"Location");
		xlsWriteLabel(0,5,"Purchase date");
		xlsWriteLabel(0,6,"Vendor Name");
		xlsWriteLabel(0,7,"Warranty expiry");
		xlsWriteLabel(0,8,"Status");

		$xlsRow = 1;

		if(is_array($arrPurchaseInventory) && count($arrPurchaseInventory) > 0)
		{
			foreach($arrPurchaseInventory as $curInventory)
			{
				xlsWriteLabel($xlsRow,0,$curInventory["type"]);
				xlsWriteLabel($xlsRow,1,$curInventory["make"]);
				xlsWriteLabel($xlsRow,2,$curInventory["invoice_no"]);
				xlsWriteLabel($xlsRow,3,$curInventory["uniqueid"]);
				xlsWriteLabel($xlsRow,4,$curInventory["location_name"]);
				xlsWriteLabel($xlsRow,5,$curInventory["purchasedate"]);
				xlsWriteLabel($xlsRow,6,$curInventory["vendor_name"]);
				xlsWriteLabel($xlsRow,7,$curInventory["warrenty_expiry"]);
				xlsWriteLabel($xlsRow,8,$curInventory["status_text"]);

				$xlsRow++;
			}
		}
		else
		{
			xlsWriteLabel($xlsRow,1,"No Records");
		}

		xlsEOF();

		exit;
	}
	
	/* Display search result for the inventory in repair */
	$tpl->set_var("FillInventoryPurchaseList","");
	if(count($arrPurchaseInventory) > 0)
	{
		foreach($arrPurchaseInventory as $currInventory)
		{
			$tpl->SetAllValues($currInventory);
			$tpl->parse("FillInventoryPurchaseList",true);
		}
	}

	/* Fill inventory type */
	$tpl->set_var("FillInventoryType","");
	$arrInventoryType = $objInventoryType->fnGetAllInventoryType();
	if(count($arrInventoryType) > 0)
	{
		foreach($arrInventoryType as $curType)
		{
			$tpl->set_var("type_id",$curType["id"]);
			$tpl->set_var("type_name",$curType["type"]);
			
			$tpl->parse("FillInventoryType",true);
		}
	}
	
	/* Fill inventory make */
	$tpl->set_var("FillInventoryMake","");
	$arrInventoryMake = $objInventoryMake->fnGetAllInventoryMake();
	if(count($arrInventoryMake) > 0)
	{
		foreach($arrInventoryMake as $curMake)
		{
			$tpl->set_var("make_id",$curMake["id"]);
			$tpl->set_var("make_name",$curMake["make"]);
			
			$tpl->parse("FillInventoryMake",true);
		}
	}
	
	/* Fill inventory vendor */
	$tpl->set_var("FillInventoryVendor","");
	$arrInventoryVendor = $objInventoryVendor->fnGetAllInventoryVendor();
	if(count($arrInventoryVendor) > 0)
	{
		foreach($arrInventoryVendor as $curVendor)
		{
			$tpl->set_var("vendor_id",$curVendor["id"]);
			$tpl->set_var("vendor_name",$curVendor["vendor_name"]);

			$tpl->parse("FillInventoryVendor",true);
		}
	}

	/* Fill inventory location */
	$tpl->set_var("FillInventoryLocation","");
	$arrInventoryLocation = $objInventoryLocation->fnGetAllVisibleLocations();
	if(count($arrInventoryLocation) > 0)
	{
		foreach($arrInventoryLocation as $curLocation)
		{
			$tpl->set_var("location_id",$curLocation["id"]);
			$tpl->set_var("location_name",$curLocation["location_name"]);

			$tpl->parse("FillInventoryLocation",true);
		}
	}

	$tpl->pparse('main',false);

?>
