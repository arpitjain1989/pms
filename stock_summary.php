<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('stock_summary.html','main_container');

	/* Rights management */
	$PageIdentifier = "StockRegister";
	include_once('userrights.php');
	
	/* Set heading */
	$tpl->set_var("mainheading","Manage Stock Register");
	
	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Manage Stock Register</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	/* Include files */
	include_once('includes/class.inventory_type.php');
	include_once('includes/class.stock_register.php');
	include_once("includes/class.inventory_make.php");
	include_once("includes/class.inventory_location.php");
	include_once("includes/class.inventory_vendor.php");

	/* Create objects */
	$objInventoryType = new inventory_type();
	$objStockRegister = new stock_register();
	$objInventoryMake = new inventory_make();
	$objInventoryLocation = new inventory_location();
	$objInventoryVendor = new inventory_vendor();
	/* Display message block */
	$tpl->set_var("DisplayMessageBlock","");
	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'success':
				$messageClass = "alert-success";
				$message = "Stock added successfully";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Stock with this Unique Id already added. Cannot add again.";
				break;
			case 'norec':
				$messageClass = "alert-error";
				$message = "No records found in uploaded CSV.";
				break;
			case 'invalid':
				$messageClass = "alert-error";
				$message = "Invalid file type. Cannot read the data.";
				break;
			case 'upload':
				if(isset($_REQUEST["err"]) && trim($_REQUEST["err"]) != "" && trim($_REQUEST["err"]) != "0")
				{
					$messageClass = "alert-error";
					$message = $_REQUEST["err"]." Stock already added. Cannot add again.";
				}
				else
				{
					$messageClass = "alert-success";
					$message = "Stock uploaded successfully.";
				}
				
				break;
		}

		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);
		}
	}

	if(isset($_POST["action"]) && trim($_POST["action"]) == "UploadStockCsv")
	{
		$filename = $_FILES["stock_csv"]["name"];
		if($filename != "")
		{
			$arrfilename = explode(".", $filename);
			$ext = array_pop($arrfilename);
	
			if($ext == "csv")
			{
				$row = 0;
				$errcnt = 0;
				
				$arrStatus = array("Stock"=>"0","In use"=>"1","Scrap"=>"2","Repair"=>"3");
				
				if (($handle = fopen($_FILES["stock_csv"]["tmp_name"], "r")) !== FALSE) 
				{
					while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
					{
						$arr = array();
						if($row > 0)
						{
							$arr["inventory_type_id"] = $objInventoryType->fnGetInventoryTypeIdByInventoryType(trim($data[0]));
							$arr["inventory_make_id"] = $objInventoryMake->fnGetInventoryMakeIdByInventoryMake(trim($data[1]));
							$arr["serialno"] = trim($data[2]);
							$arr["uniqueid"] = trim($data[3]);
							$arr["location_id"] = $objInventoryLocation->fnGetInventoryLocationIdByInventoryLocation(trim($data[4]));
							$arr["vendor_id"] = $objInventoryVendor->fnGetInventoryVendorIdByInventoryVendor(trim($data[5]));
							$arr["purchasedate"] = trim($data[6]);
							$arr["invoice_no"] = trim($data[7]);
							$arr["warranty_year"] = trim($data[8]);
							$arr["warranty_month"] = trim($data[9]);
							$arr["status"] = $arrStatus[ucfirst(strtolower(trim($data[10])))];
							$arr["remarks"] = trim($data[11]);
							
							if(!$objStockRegister->fnSaveStockRegister($arr))
								$errcnt++;
						}
						$row++;
					}
					fclose($handle);
				}

				if($row > 1)
				{
					header("Location: stock_summary.php?info=upload&err=$errcnt");
					exit;
				}
				else
				{
					header("Location: stock_summary.php?info=norec");
					exit;
				}
			}
			else
			{
				header("Location: stock_summary.php?info=invalid");
				exit;
			}
		}
	}

	$tpl->set_var("GatePassPrintScriptBlock","");
	if(isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		$tpl->set_var("server_url", SERVERURL);
		$tpl->set_var("gatepass_for_stock_id", $_REQUEST["id"]);

		$tpl->parse("GatePassPrintScriptBlock",false);
	}

	/* Get values */
	$arrInventoryType = $objInventoryType->fnGetAllInventoryType();
	$arrStockSummary = $objStockRegister->fnGetStockSummary();

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
