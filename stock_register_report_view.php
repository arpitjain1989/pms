<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('stock_register_report_view.html','main_container');

	/* Rights management */
	$PageIdentifier = "StockSummaryReport";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","Stock Register Report View");
	
	/* Set breadcrumb */
	$breadcrumb = '<li><a href="stock_summary_report.php">Stock Summary Report</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li><a href="stock_register_report.php">Stock Summary Detail</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Stock Summary View</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.stock_register.php');

	$objStockRegister = new stock_register();

	$tpl->set_var("DisplayStockInformationBlock","");
	$tpl->set_var("DisplayNoStockInformationBlock","");
	$tpl->set_var("DisplayInventoryAttributesBlock","");
	$tpl->set_var("DisplayNoInventoryAttributesBlock","");
	$tpl->set_var("DisplayRepairInformationBlock","");

	if(isset($_REQUEST['id']) && trim($_REQUEST['id']) != "")
	{
		$arrStock = $objStockRegister->fnGetStockRegisterById($_REQUEST['id']);
		if(count($arrStock) > 0)
		{
			$tpl->SetAllValues($arrStock);
			
			$arrSelectedAttributes = $objStockRegister->fnGetSelectedStockAttributesDetail(trim($_REQUEST['id']));
			
			if(count($arrSelectedAttributes) > 0)
			{
				foreach($arrSelectedAttributes as $curSelected)
				{
					$tpl->set_var("inventory_attribute_name",$curSelected["attribute_id_name"]." : ");
					$tpl->set_var("inventory_sub_attribute_name",$curSelected["attribute_value_id_name"]);
					
					$tpl->parse("DisplayInventoryAttributesBlock",true);
				}
			}
			else
			{
				$tpl->parse("DisplayNoInventoryAttributesBlock",false);
			}
			
			/* If in repair, display repair related information */
			if($arrStock["status"] == 3)
				$tpl->parse("DisplayRepairInformationBlock",false);
			
			$tpl->parse("DisplayStockInformationBlock",false);
		}
		else
		{
			$tpl->parse("DisplayNoStockInformationBlock",false);
		}
	}
	else
	{
		$tpl->parse("DisplayNoStockInformationBlock",false);
	}
	
	$tpl->pparse('main',false);
?>
