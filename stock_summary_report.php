<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('stock_summary_report.html','main_container');

	/* Rights management */
	$PageIdentifier = "StockSummaryReport";
	include_once('userrights.php');
	
	/* Set heading */
	$tpl->set_var("mainheading","Stock Summary Report");
	
	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Stock Summary Report</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	/* Include files */
	include_once('includes/class.inventory_type.php');
	include_once('includes/class.stock_register.php');

	/* Create objects */
	$objInventoryType = new inventory_type();
	$objStockRegister = new stock_register();

	/* Get values */
	$arrInventoryType = $objInventoryType->fnGetAllInventoryType();
	$arrStockSummary = $objStockRegister->fnGetStockSummary();

	/* Export to excel */
	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "export") 
	{
		$filename = "Inventory Summary Report - ".Date('Y-m-d H:i').".xls";
		
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
		xlsWriteLabel(0,1,"Stock");
		xlsWriteLabel(0,2,"In Use");
		xlsWriteLabel(0,3,"Repair");
		xlsWriteLabel(0,4,"Scrap");
		xlsWriteLabel(0,5,"Total");

		$xlsRow = 1;

		if(is_array($arrInventoryType) && count($arrInventoryType) > 0)
		{
			foreach($arrInventoryType as $curInventory)
			{
				$stock_count = $inuse_count = $repair_count = $scrap_count = $total_count = 0;
				
				/* In stock */
				if(isset($arrStockSummary[$curInventory["id"]]["0"]))
					$stock_count = $arrStockSummary[$curInventory["id"]]["0"];

				/* In use */
				if(isset($arrStockSummary[$curInventory["id"]]["1"]))
					$inuse_count = $arrStockSummary[$curInventory["id"]]["1"];

				/* Scrap */
				if(isset($arrStockSummary[$curInventory["id"]]["2"]))
					$scrap_count = $arrStockSummary[$curInventory["id"]]["2"];

				/* Repair */
				if(isset($arrStockSummary[$curInventory["id"]]["3"]))
					$repair_count = $arrStockSummary[$curInventory["id"]]["3"];

				/* Calcumating the total */
				$total_count = $stock_count + $inuse_count + $repair_count + $scrap_count;
				
				xlsWriteLabel($xlsRow,0,$curInventory["type"]);
				xlsWriteNumber($xlsRow,1,$stock_count);
				xlsWriteNumber($xlsRow,2,$inuse_count);
				xlsWriteNumber($xlsRow,3,$repair_count);
				xlsWriteNumber($xlsRow,4,$scrap_count);
				xlsWriteNumber($xlsRow,5,$total_count);

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

	$tpl->set_var("FillStockRegisterSummaryList","");
	if(count($arrInventoryType) > 0)
	{
		foreach($arrInventoryType as $curType)
		{
			$tpl->set_var("type_id", $curType["id"]);
			$tpl->set_var("type", $curType["type"]);
			
			$stock_count = $inuse_count = $repair_count = $scrap_count = $total_count = 0;
			
			/* In stock */
			if(isset($arrStockSummary[$curType["id"]]["0"]))
				$stock_count = $arrStockSummary[$curType["id"]]["0"];

			/* In use */
			if(isset($arrStockSummary[$curType["id"]]["1"]))
				$inuse_count = $arrStockSummary[$curType["id"]]["1"];

			/* Scrap */
			if(isset($arrStockSummary[$curType["id"]]["2"]))
				$scrap_count = $arrStockSummary[$curType["id"]]["2"];

			/* Repair */
			if(isset($arrStockSummary[$curType["id"]]["3"]))
				$repair_count = $arrStockSummary[$curType["id"]]["3"];

			/* Calcumating the total */
			$total_count = $stock_count + $inuse_count + $repair_count + $scrap_count;

			$tpl->set_var("stock_count",$stock_count);
			$tpl->set_var("inuse_count",$inuse_count);
			$tpl->set_var("scrap_count",$scrap_count);
			$tpl->set_var("repair_count",$repair_count);
			$tpl->set_var("total_count",$total_count);

			$tpl->parse("FillStockRegisterSummaryList",true);
		}
	}

	$tpl->pparse('main',false);

?>
