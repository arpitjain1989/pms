<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('scrap_disposal_report.html','main_container');

	/* Rights management */
	$PageIdentifier = "ScrapDisposalReport";
	include_once('userrights.php');
	
	/* Set heading */
	$tpl->set_var("mainheading","Scrap Disposal");
	
	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Inventory Purchase Report</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.stock_register.php');
	include_once('includes/class.inventory_type.php');
	include_once('includes/class.inventory_make.php');

	$objStockRegister = new stock_register();
	$objInventoryType = new inventory_type();
	$objInventoryMake = new inventory_make();

	/* Search inventory purchase */
	if(isset($_POST["action"]) && trim($_POST["action"]) == "ScrapDisposalReport")
	{
		$_SESSION["ScrapDisposalReport"]["inventory_type_id"] = trim($_POST["search_inventory_type_id"]);
		$_SESSION["ScrapDisposalReport"]["inventory_make_id"] = trim($_POST["search_inventory_make_id"]);
		$_SESSION["ScrapDisposalReport"]["scrap_disposal_date_from"] = trim($_POST["search_scrap_disposal_date_from"]);
		$_SESSION["ScrapDisposalReport"]["scrap_disposal_date_to"] = trim($_POST["search_scrap_disposal_date_to"]);
		$_SESSION["ScrapDisposalReport"]["unique_id"] = trim($_POST["search_unique_id"]);
		
		header("Location: scrap_disposal_report.php");
		exit;
	}
	
	/* Set values that are selected and stored in session */
	$inventory_type_id = $inventory_make_id = 0;
	$scrap_disposal_date_from = $scrap_disposal_date_to = Date('Y-m-d');
	$unique_id = "";

	if(isset($_SESSION["ScrapDisposalReport"]["inventory_type_id"]))
		$inventory_type_id = $_SESSION["ScrapDisposalReport"]["inventory_type_id"];

	if(isset($_SESSION["ScrapDisposalReport"]["inventory_make_id"]))
		$inventory_make_id = $_SESSION["ScrapDisposalReport"]["inventory_make_id"];

	if(isset($_SESSION["ScrapDisposalReport"]["scrap_disposal_date_from"]))
		$scrap_disposal_date_from = $_SESSION["ScrapDisposalReport"]["scrap_disposal_date_from"];

	if(isset($_SESSION["ScrapDisposalReport"]["scrap_disposal_date_to"]))
		$scrap_disposal_date_to = $_SESSION["ScrapDisposalReport"]["scrap_disposal_date_to"];

	if(isset($_SESSION["ScrapDisposalReport"]["unique_id"]))
		$unique_id = $_SESSION["ScrapDisposalReport"]["unique_id"];

	$tpl->set_var("search_inventory_type_id",$inventory_type_id);
	$tpl->set_var("search_inventory_make_id",$inventory_make_id);
	$tpl->set_var("search_scrap_disposal_date_from",$scrap_disposal_date_from);
	$tpl->set_var("search_scrap_disposal_date_to",$scrap_disposal_date_to);
	$tpl->set_var("search_unique_id",$unique_id);

	$arrScrapDisposal = $objStockRegister->fnGetScrapDisposal($inventory_type_id, $inventory_make_id, $scrap_disposal_date_from, $scrap_disposal_date_to, $unique_id);
	
	/* Export to excel */
	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "export") 
	{
		$filename = "Scrap Disposal Report - ".Date('Y-m-d H:i').".xls";
		
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
		xlsWriteLabel(0,3,"Invoice No");
		xlsWriteLabel(0,4,"Unique Id");
		xlsWriteLabel(0,5,"Purchase date");
		xlsWriteLabel(0,6,"Gatepass No.");
		xlsWriteLabel(0,7,"Disposal Date");

		$xlsRow = 1;

		if(is_array($arrScrapDisposal) && count($arrScrapDisposal) > 0)
		{
			foreach($arrScrapDisposal as $curInventory)
			{
				xlsWriteLabel($xlsRow,0,$curInventory["type"]);
				xlsWriteLabel($xlsRow,1,$curInventory["make"]);
				xlsWriteLabel($xlsRow,2,$curInventory["serialno"]);
				xlsWriteLabel($xlsRow,3,$curInventory["invoice_no"]);
				xlsWriteLabel($xlsRow,4,$curInventory["uniqueid"]);
				xlsWriteLabel($xlsRow,5,$curInventory["purchasedate"]);
				xlsWriteLabel($xlsRow,6,$curInventory["disposal_gatepass_no"]);
				xlsWriteLabel($xlsRow,7,$curInventory["disposeddate"]);

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
	$tpl->set_var("FillScrapDisposalList","");
	if(count($arrScrapDisposal) > 0)
	{
		foreach($arrScrapDisposal as $currInventory)
		{
			$tpl->SetAllValues($currInventory);
			$tpl->parse("FillScrapDisposalList",true);
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

	$tpl->pparse('main',false);

?>
