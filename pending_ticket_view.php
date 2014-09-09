<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('pending_ticket_view.html','main_container');

	/* Rights management */
	$PageIdentifier = "PendingTicket";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","Pending Tickets");
	
	/* Set breadcrumb */
	$breadcrumb = '<li><a href="pending_ticket_list.php">Manage Pending Ticket</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Pending Ticket</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.ticket.php');
	include_once('includes/class.inventory_type.php');
	include_once('includes/class.stock_register.php');

	$objTicket = new ticket();
	$objInventoryType = new inventory_type();
	$objStockRegister = new stock_register();

	/* Save information */
	if(isset($_POST["action"]) && trim($_POST["action"]) == "UpdateResolutionStatus")
	{
		if($objTicket->fnUpdateResolutionStatus($_POST))
		{
			header("Location: pending_ticket_list.php?info=success");
			exit;
		}
		else
		{
			header("Location: pending_ticket_list.php?info=err");
			exit;
		}
	}

	/* Fetch pending ticket by ID */
	
	$tpl->set_var("DisplayTicketInformationBlock","");
	$tpl->set_var("DisplayNoTicketInformationBlock","");
	
	if(isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		$tpl->set_var("ticket_id",$_REQUEST["id"]);
		$ticketInformation = $objTicket->fnGetPendingTicketById(trim($_REQUEST["id"]));
		
		if(count($ticketInformation) > 0)
		{
			/* Fill dropdown status */
			
			/*$status = array("0"=>"Not attended", "1"=>"Attending", "2"=>"Additional Requirements", "3"=>"Pending", "4"=>"Under observation", "5"=>"Closed");*/
			
			$status = array("0"=>"In queue", "1"=>"Being attended", "2"=>"Resolved", "3"=>"Work in progress", "4"=>"Under observation");
			
			$tpl->set_var("FillStatusBlock","");
			foreach($status as $curStatusKey => $curStatus)
			{
				$tpl->set_var("status_id",$curStatusKey);
				$tpl->set_var("status_name",$curStatus);
				
				$tpl->parse("FillStatusBlock",true);
			}
			
			/* Fill additional requirements dropdown */
			/*$tpl->set_var("FillAdditionalAssetsRequired","");
			$arrInventoryType = $objInventoryType->fnGetAllInventoryType();
			if(count($arrInventoryType) > 0)
			{
				foreach($arrInventoryType as $curInventoryType)
				{
					$tpl->set_var("additional_asset_id", $curInventoryType["id"]);
					$tpl->set_var("additional_asset_name", $curInventoryType["type"]);
					
					$tpl->parse("FillAdditionalAssetsRequired",true);
				}
			}*/

			$tpl->SetAllValues($ticketInformation);
			
			/* Fill the assets drop down on the particular location */
			$LocationInventory = $objStockRegister->fnGetLocationWiseInventoryList($ticketInformation["location_id"]);

			$tpl->set_var("FillReplaceWithBlock","");
			if(count($LocationInventory) > 0)
			{
				foreach($LocationInventory as $curLocationInventory)
				{
					$tpl->set_var("location_inventory_id",$curLocationInventory["srid"]);
					$tpl->set_var("location_inventory_type",$curLocationInventory["type"] . " - " . $curLocationInventory["uniqueid"]);

					$tpl->parse("FillReplaceWithBlock",true);
				}
			}
			$tpl->parse("DisplayTicketInformationBlock",false);
		}
		else
		{
			$tpl->parse("DisplayNoTicketInformationBlock",false);
		}
	}
	else
	{
		$tpl->parse("DisplayNoTicketInformationBlock",false);
	}

	$tpl->pparse('main',false);

?>
