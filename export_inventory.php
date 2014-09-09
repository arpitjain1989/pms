<?php
	
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	
	include_once('includes/class.stock_register.php');
	include_once('includes/class.inventory_attributes.php');
	
	$objStockRegister = new stock_register();
	$objInventoryAttributes = new inventory_attributes();

	$arrStock = $objStockRegister->fnGetDetailCurrentStockStatus();
	$arrAttributes = $objInventoryAttributes->fnMainAttributes();

	/* Rights management */
	$PageIdentifier = "CurrentInventoryExport";
	include_once('userrights.php');

//print_r($arrAttributes);

//	print_r($arrStock);
//	die;
	$filename = "CurrentInventoryStatus-".Date('Y-m-d_H_i').".xls";

	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Content-Type: application/force-download");
	header("Content-Type: application/octet-stream");
	header("Content-Type: application/download");
	header("Content-Disposition: attachment;filename=$filename");
	header("Content-Transfer-Encoding: binary ");
	
	xlsBOF();

	xlsWriteLabel(0,0,"Inventory Type");
	xlsWriteLabel(0,1,"Inventory Make");
	xlsWriteLabel(0,2,"Serial No");
	xlsWriteLabel(0,3,"Unique Id");
	xlsWriteLabel(0,4,"Location");
	xlsWriteLabel(0,5,"Purchase date");
	xlsWriteLabel(0,6,"Invoice No");
	xlsWriteLabel(0,7,"Warranty");
	xlsWriteLabel(0,8,"Warranty expiry");
	xlsWriteLabel(0,9,"In Warranty");
	xlsWriteLabel(0,10,"Vendor Name");
	xlsWriteLabel(0,11,"Status");
	xlsWriteLabel(0,12,"If in repair given to Vendor");
	xlsWriteLabel(0,13,"If in repair Expected Return Date");

	$i = 14;
	
	$arrAttributesList = array();
	if(count($arrAttributes) > 0)
	{
		foreach($arrAttributes as $curAttribute)
		{
			xlsWriteLabel(0,$i,$curAttribute["attribute_name"]);
			
			$arrAttributesList[$curAttribute["id"]] = $i;
			
			$i++;
		}
	}

	$xlsRow = 1;

	if(is_array($arrStock) && count($arrStock) > 0)
	{
		foreach($arrStock as $curStock)
		{
			xlsWriteLabel($xlsRow,0,$curStock["inventory_type"]);
			xlsWriteLabel($xlsRow,1,$curStock["inventory_make"]);
			xlsWriteLabel($xlsRow,2,$curStock["serialno"]);
			xlsWriteLabel($xlsRow,3,$curStock["uniqueid"]);
			xlsWriteLabel($xlsRow,4,$curStock["inventory_location"]);
			xlsWriteLabel($xlsRow,5,$curStock["purchase_date"]);
			xlsWriteLabel($xlsRow,6,$curStock["invoice_no"]);
			xlsWriteLabel($xlsRow,7,$curStock["warranty_text"]);
			xlsWriteLabel($xlsRow,8,$curStock["warrenty_expiry"]);
			xlsWriteLabel($xlsRow,9,$curStock["warranty_status_text"]);
			xlsWriteLabel($xlsRow,10,$curStock["vendor_name"]);
			xlsWriteLabel($xlsRow,11,$curStock["status_text"]);
			xlsWriteLabel($xlsRow,12,$curStock["sendto_vendor_name"]);
			xlsWriteLabel($xlsRow,13,$curStock["expected_return_date"]);

			if(count($curStock["attributes"]) > 0)
			{
				foreach($curStock["attributes"] as $curAttr)
				{
					if(isset($arrAttributesList[$curAttr["attribute_id"]]))
						xlsWriteLabel($xlsRow,$arrAttributesList[$curAttr["attribute_id"]],$curAttr["attribute_value_id_name"]);
				}
			}

			$xlsRow++;
		}
	}
	else
	{
		xlsWriteLabel($xlsRow,1,"No Records");
	}

	xlsEOF();

	exit;
	
	$tpl->pparse('main',false);
?>
