<?php

	include('common.php');

	$tpl = new Template($app_path);
	
	$tpl->load_file('gatepass_print.html','main');
	
	include_once("includes/class.stock_register.php");
	
	$objStockRegister =  new stock_register();

	$srno = 1;
	$qty = 1;
	
	$tpl->set_var("DisplayGatePassBlock","");
	$tpl->set_var("DisplayInventoryInformationBlock","");
	$tpl->set_var("DisplayNoInventoryInformationBlock","");

	if(isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		$arrGatePass = $objStockRegister->fnGetGatePassInformationByStockId($_REQUEST["id"]);
		if(count($arrGatePass) > 0)
		{
			$tpl->set_var("current_date",Date('d-m-Y'));
			$tpl->set_var("srno",$srno);
			$tpl->set_var("quantity",$qty);
			$tpl->set_var("description",$arrGatePass["type"].", Serial No.: ".$arrGatePass["serialno"]);

			/*$setreturnable = "Returnable";
			if(isset($arrGatePass["isreturnable"]) && $arrGatePass["isreturnable"] == '0')
				$setreturnable = "Non Returnable";

			$tpl->set_var("setreturnable", $setreturnable);*/

			$tpl->SetAllValues($arrGatePass);
			
			$tpl->parse("DisplayInventoryInformationBlock",false);
		}
		else
		{
			$tpl->parse("DisplayNoInventoryInformationBlock",false);
		}
		
		$tpl->parse("DisplayGatePassBlock",true);
		$tpl->parse("DisplayGatePassBlock",true);
	}
	
	$tpl->pparse('main',false);

?>
