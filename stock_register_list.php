<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('stock_register_list.html','main_container');

	/* Rights management */
	$PageIdentifier = "StockRegister";
	include_once('userrights.php');
	
	/* Set heading */
	$tpl->set_var("mainheading","Manage Stock Register");
	
	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Manage Stock Register</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.stock_register.php');

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
		}

		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);
		}
	}

	$tpl->set_var("GatePassPrintScriptBlock","");
	if(isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		$tpl->set_var("server_url", SERVERURL);
		$tpl->set_var("gatepass_for_stock_id", $_REQUEST["id"]);

		$tpl->parse("GatePassPrintScriptBlock",false);
	}

	$objStockRegister = new stock_register();
	
	$type_id = 0;
	$status = '';
	
	if(isset($_REQUEST["type"]) && trim($_REQUEST["type"]) != '')
		$type_id = trim($_REQUEST["type"]);

	if(isset($_REQUEST["status"]) && trim($_REQUEST["status"]) != '')
		$status = trim($_REQUEST["status"]);
	
	$arrStockRegister = $objStockRegister->fnGetAllStockRegister($type_id, $status);

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
