<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('inventory_repair_report.html','main_container');

	/* Rights management */
	$PageIdentifier = "InventoryRepairReport";
	include_once('userrights.php');
	
	/* Set heading */
	$tpl->set_var("mainheading","Inventory Repair Report");
	
	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Inventory Repair Report</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.stock_register.php');
	include_once('includes/class.inventory_type.php');
	include_once('includes/class.inventory_make.php');
	include_once('includes/class.inventory_vendor.php');

	$objStockRegister = new stock_register();
	$objInventoryType = new inventory_type();
	$objInventoryMake = new inventory_make();
	$objInventoryVendor = new inventory_vendor();

	/* Search for repair inventory */
	if(isset($_POST["action"]) && trim($_POST["action"]) == "InventoryRepairReport")
	{
		$_SESSION["InventoryRepairReport"]["inventory_type_id"] = trim($_POST["search_inventory_type_id"]);
		$_SESSION["InventoryRepairReport"]["inventory_make_id"] = trim($_POST["search_inventory_make_id"]);
		$_SESSION["InventoryRepairReport"]["inventory_vendor_id"] = trim($_POST["search_inventory_sentto_vendor_id"]);
		$_SESSION["InventoryRepairReport"]["expected_return_date_from"] = trim($_POST["search_expected_return_date_from"]);
		$_SESSION["InventoryRepairReport"]["expected_return_date_to"] = trim($_POST["search_expected_return_date_to"]);
		
		header("Location: inventory_repair_report.php");
		exit;
	}
	
	/* Set values that are selected and stored in session */
	$inventory_type_id = $inventory_make_id = 0;
	$expected_return_date_from = $expected_return_date_to = Date('Y-m-d');
	$inventory_vendor_id = "";
	
	if(isset($_SESSION["InventoryRepairReport"]["inventory_type_id"]))
		$inventory_type_id = $_SESSION["InventoryRepairReport"]["inventory_type_id"];

	if(isset($_SESSION["InventoryRepairReport"]["inventory_make_id"]))
		$inventory_make_id = $_SESSION["InventoryRepairReport"]["inventory_make_id"];

	if(isset($_SESSION["InventoryRepairReport"]["inventory_vendor_id"]))
		$inventory_vendor_id = $_SESSION["InventoryRepairReport"]["inventory_vendor_id"];

	if(isset($_SESSION["InventoryRepairReport"]["expected_return_date_from"]))
		$expected_return_date_from = $_SESSION["InventoryRepairReport"]["expected_return_date_from"];

	if(isset($_SESSION["InventoryRepairReport"]["expected_return_date_to"]))
		$expected_return_date_to = $_SESSION["InventoryRepairReport"]["expected_return_date_to"];
	
	$tpl->set_var("search_inventory_type_id",$inventory_type_id);
	$tpl->set_var("search_inventory_make_id",$inventory_make_id);
	$tpl->set_var("search_inventory_sentto_vendor_id",$inventory_vendor_id);
	$tpl->set_var("search_expected_return_date_from",$expected_return_date_from);
	$tpl->set_var("search_expected_return_date_to",$expected_return_date_to);

	$arrRepairInventory = $objStockRegister->fnGetInventoryInRepair($inventory_type_id, $inventory_make_id, $inventory_vendor_id, $expected_return_date_from, $expected_return_date_to);

	/* Export to excel */
	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "export") 
	{
		$filename = "Inventory Repair Report - ".Date('Y-m-d H:i').".xls";
		
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
		xlsWriteLabel(0,2,"Serial No");
		xlsWriteLabel(0,3,"Unique Id");
		xlsWriteLabel(0,4,"Location");
		xlsWriteLabel(0,5,"Purchase date");
		xlsWriteLabel(0,6,"Vendor Name");
		xlsWriteLabel(0,7,"Warranty expiry");
		xlsWriteLabel(0,8,"Sent To Vendor");
		xlsWriteLabel(0,9,"Expected Return Date");

		$xlsRow = 1;

		if(is_array($arrRepairInventory) && count($arrRepairInventory) > 0)
		{
			foreach($arrRepairInventory as $curInventory)
			{
				xlsWriteLabel($xlsRow,0,$curInventory["type"]);
				xlsWriteLabel($xlsRow,1,$curInventory["make"]);
				xlsWriteLabel($xlsRow,2,$curInventory["serialno"]);
				xlsWriteLabel($xlsRow,3,$curInventory["uniqueid"]);
				xlsWriteLabel($xlsRow,4,$curInventory["location_name"]);
				xlsWriteLabel($xlsRow,5,$curInventory["purchasedate"]);
				xlsWriteLabel($xlsRow,6,$curInventory["vendor_name"]);
				xlsWriteLabel($xlsRow,7,$curInventory["warrenty_expiry"]);
				xlsWriteLabel($xlsRow,8,$curInventory["senttovendor"]);
				xlsWriteLabel($xlsRow,9,$curInventory["expected_return_date"]);

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
	$tpl->set_var("FillInventoryRepairList","");
	if(count($arrRepairInventory) > 0)
	{
		foreach($arrRepairInventory as $currInventory)
		{
			$tpl->SetAllValues($currInventory);
			$tpl->parse("FillInventoryRepairList",true);
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

	$tpl->pparse('main',false);

?>
