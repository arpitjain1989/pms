<?php

	include('common.php');

	$tpl = new Template($app_path);
	
	$tpl->load_file('scrap_disposal_gatepass_print.html','main');
	
	include_once("includes/class.stock_register.php");
	
	$objStockRegister =  new stock_register();

	$srno = 1;
	$qty = 1;
	
	$tpl->set_var("DisplayScrapDisposalGatePassBlock","");
	$tpl->set_var("DisplayInventoryInformationBlock","");
	$tpl->set_var("DisplayNoInventoryInformationBlock","");

	if(isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		$tpl->set_var("gatepassid",$_REQUEST["id"]);
		
		$arrDisposedInventory = $objStockRegister->fnGetScrapDisposalInformationBySerialNo($_REQUEST["id"]);
		if(count($arrDisposedInventory) > 0)
		{
			foreach($arrDisposedInventory as $curDisposedInventory)
			{
				$tpl->set_var("srno",$srno);
				$tpl->set_var("disposal_date",$curDisposedInventory["disposal_date"]);
				$tpl->set_var("quantity",$qty);
				$tpl->set_var("description",$curDisposedInventory["type"].", Serial No.: ".$curDisposedInventory["serialno"]);

				//$tpl->SetAllValues($arrGatePass);
				
				$tpl->parse("DisplayInventoryInformationBlock",true);
				
				$srno++;
			}
		}
		else
		{
			$tpl->parse("DisplayNoInventoryInformationBlock",false);
		}
		
		$tpl->parse("DisplayScrapDisposalGatePassBlock",true);
		$tpl->parse("DisplayScrapDisposalGatePassBlock",true);
	}
	
	$tpl->pparse('main',false);

?>
