<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('inventory_vendor_view.html','main_container');

	/* Rights management */
	$PageIdentifier = "InventoryVendor";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","View Inventory Vendor");
	
	/* Set breadcrumb */
	$breadcrumb = '<li><a href="inventory_vendor_list.php">Manage Inventory Vendor</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Inventory Vendor</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.inventory_vendor.php');

	$objInventoryVendor = new inventory_vendor();

	$tpl->set_var("DisplayInventoryVendorInformationBlock","");
	$tpl->set_var("DisplayNoInventoryVendorInformationBlock","");
	$tpl->set_var("FillContactInformation","");
	$tpl->set_var("FillNoContactInformation","");

	if(isset($_REQUEST['id']) && trim($_REQUEST['id']) != "")
	{
		$arrInventoryVendor = $objInventoryVendor->fnGetInventoryVendorById($_REQUEST['id']);
		
		if(count($arrInventoryVendor) > 0)
		{
			$tpl->set_var("vendor_name",$arrInventoryVendor["vendor_name"]);
			
			if(isset($arrInventoryVendor["contact_information"]) && count($arrInventoryVendor["contact_information"]) > 0)
			{
				foreach($arrInventoryVendor["contact_information"] as $curInfo)
				{
					$tpl->set_var("contact_person", $curInfo["contact_person"]);
					$tpl->set_var("contact_no", $curInfo["contact_no"]);
					$tpl->set_var("address", $curInfo["address"]);
					
					$tpl->parse("FillContactInformation",true);
				}
			}
			else
			{
				$tpl->parse("FillNoContactInformation",false);
			}
			
			$tpl->SetAllValues($arrInventoryVendor);
			$tpl->parse("DisplayInventoryVendorInformationBlock",false);
		}
		else
		{
			$tpl->parse("DisplayNoInventoryVendorInformationBlock",false);
		}
	}
	else
	{
		$tpl->parse("DisplayNoInventoryVendorInformationBlock",false);
	}
	
	$tpl->pparse('main',false);
?>
