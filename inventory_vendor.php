<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('inventory_vendor.html','main_container');

	/* Rights management */
	$PageIdentifier = "InventoryVendor";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","Inventory Vendor");
	
	/* Set breadcrumb */
	$breadcrumb = '<li><a href="inventory_vendor_list.php">Manage Inventory Vendor</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Inventory Vendor</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.inventory_vendor.php');

	$objInventoryVendor = new inventory_vendor();

	/* Set values for update mode */
	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "update" && isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		$inventory_vendor = $objInventoryVendor->fnGetInventoryVendorById($_REQUEST["id"]);
		if(count($inventory_vendor) > 0)
		{
			$tpl->set_var("id",$inventory_vendor["id"]);
			$tpl->set_var("vendor_name",$inventory_vendor["vendor_name"]);

			$arrContactPerson = array();
			$arrContactNo = array();
			$arrAddress = array();

			if(isset($inventory_vendor["contact_information"]) && count($inventory_vendor["contact_information"]))
			{
				foreach($inventory_vendor["contact_information"] as $curVendor)
				{
					$arrContactPerson[] = $curVendor["contact_person"];
					$arrContactNo[] = $curVendor["contact_no"];
					$arrAddress[] = $curVendor["address"];
				}
			}
			
			$tpl->set_var("hcontact_person",implode(",",$arrContactPerson));
			$tpl->set_var("hcontactno",implode(",",$arrContactNo));
			$tpl->set_var("haddress",implode(",",$arrAddress));
		}
	}

	/* Save inventory vendor */
	if(isset($_POST["action"]) && trim($_POST["action"]) == "InventoryVendor")
	{
		$inventory_vendor_status = $objInventoryVendor->fnSaveInventoryVendor($_POST);

		if($inventory_vendor_status == 1)
		{
			header("Location: inventory_vendor_list.php?info=success");
			exit;
		}
		else if($inventory_vendor_status == 0)
		{
			header("Location: inventory_vendor_list.php?info=err");
			exit;
		}
	}

	$tpl->pparse('main',false);

?>
