<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('stock_register_report.html','main_container');

	/* Rights management */
	$PageIdentifier = "StockSummaryReport";
	include_once('userrights.php');
	
	/* Set heading */
	$tpl->set_var("mainheading","Stock Register Report Detail");
	
	/* Set breadcrumb */
	$breadcrumb = '<li><a href="stock_summary_report.php">Stock Summary Report</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Stock Summary Detail</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	/* Include file */
	include_once('includes/class.stock_register.php');

	/* Create object */
	$objStockRegister = new stock_register();
	
	$type_id = 0;
	$status = '';
	
	if(isset($_REQUEST["type"]) && trim($_REQUEST["type"]) != '')
		$type_id = trim($_REQUEST["type"]);

	if(isset($_REQUEST["status"]) && trim($_REQUEST["status"]) != '')
		$status = trim($_REQUEST["status"]);
	
	$tpl->set_var("type_id",$type_id);
	$tpl->set_var("status_exp",$status);
	
	$arrStockRegister = $objStockRegister->fnGetAllStockRegister($type_id, $status);

	/* Export to excel */
	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "export") 
	{
		$filename = "Stock Register Report - ".Date('Y-m-d H:i').".xls";
		
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
		xlsWriteLabel(0,6,"Warranty");
		xlsWriteLabel(0,7,"Warranty expiry");
		xlsWriteLabel(0,8,"In Warranty");
		xlsWriteLabel(0,9,"Status");
		xlsWriteLabel(0,10,"Attributes");

		$xlsRow = 1;

		if(is_array($arrStockRegister) && count($arrStockRegister) > 0)
		{
			foreach($arrStockRegister as $curInventory)
			{
				xlsWriteLabel($xlsRow,0,$curInventory["type"]);
				xlsWriteLabel($xlsRow,1,$curInventory["make"]);
				xlsWriteLabel($xlsRow,2,$curInventory["serialno"]);
				xlsWriteLabel($xlsRow,3,$curInventory["uniqueid"]);
				xlsWriteLabel($xlsRow,4,$curInventory["location_name"]);
				xlsWriteLabel($xlsRow,5,$curInventory["purchasedate"]);
				xlsWriteLabel($xlsRow,6,$curInventory["warranty_text"]);
				xlsWriteLabel($xlsRow,7,$curInventory["warrenty_expiry"]);
				xlsWriteLabel($xlsRow,8,$curInventory["warranty_status_text"]);
				xlsWriteLabel($xlsRow,9,$curInventory["status_text"]);
				xlsWriteLabel($xlsRow,10,$curInventory["attribute_text"]);

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

	/* Display list */
	$tpl->set_var("FillStockRegisterList","");
	if(count($arrStockRegister) > 0)
	{
		foreach($arrStockRegister as $curStockEntry)
		{
			$tpl->SetAllValues($curStockEntry);
			$tpl->parse("FillStockRegisterList",true);
		}
	}

	$tpl->pparse('main',false);

?>
