<?php

	include('common.php');

	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('scrap_disposal.html','main_container');
	
	/* Rights management */
	$PageIdentifier = "ScrapDisposal";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","Manage Scrap Disposal");
	
	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Manage Scrap Disposal</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once("includes/class.stock_register.php");
	$objStockRegister = new stock_register();

	/* Display message block */
	$tpl->set_var("DisplayMessageBlock","");
	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'success':
				$messageClass = "alert-success";
				$message = "Scrap inventory disposed successfully";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Problem disposing scrap.";
				break;
		}

		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	/* Display block for scrap disposal page */
	$tpl->set_var("ScrapDisposalPrintScriptBlock","");
	if(isset($_REQUEST["srno"]) && trim($_REQUEST["srno"]) != "")
	{
		$tpl->set_var("server_url", SERVERURL);
		$tpl->set_var("gatepass_for_scrap_disposal_id", $_REQUEST["srno"]);

		$tpl->parse("ScrapDisposalPrintScriptBlock",false);
	}

	if(isset($_POST["action"]) && trim($_POST["action"]) == "ScrapDisposal")
	{
		$srno = $objStockRegister->fnScrapDisposal($_POST["stock_register_id"]);
		if($srno > 0)
		{
			header("Location: scrap_disposal.php?info=success&srno=".$srno);
			exit;
		}
		else
		{
			header("Location: scrap_disposal.php?info=error");
			exit;
		}
	}

	$arrScrapInventory = $objStockRegister->fnGetScrapInventory();

	$tpl->set_var("FillScrapInventoryBlock","");
	$tpl->set_var("SaveBlock","");
	$tpl->set_var("NoRecordsBlock","");
	if(count($arrScrapInventory) > 0)
	{
		foreach($arrScrapInventory as $curScrapInventory)
		{
			$tpl->setAllValues($curScrapInventory);
			$tpl->parse("FillScrapInventoryBlock",true);
		}
		$tpl->parse("SaveBlock",false);
	}
	else
	{
		$tpl->parse("NoRecordsBlock",false);
	}

	$tpl->pparse('main',false);

?>
