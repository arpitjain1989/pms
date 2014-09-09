<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('inventory_vendor_list.html','main_container');

	/* Rights management */
	$PageIdentifier = "InventoryVendor";
	include_once('userrights.php');
	
	/* Set heading */
	$tpl->set_var("mainheading","Manage Inventory Vendor");
	
	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Manage Inventory Vendor</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.inventory_vendor.php');

	/* Display message block */
	$tpl->set_var("DisplayMessageBlock","");
	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'success':
				$messageClass = "alert-success";
				$message = "Inventory Vendor added successfully";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Inventory Vendor already added. Cannot add again.";
				break;
		}

		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	$objInventoryVendor = new inventory_vendor();
	$arrInventoryVendor = $objInventoryVendor->fnGetAllInventoryVendor();

	/* Display list */
	$tpl->set_var("FillInventoryVendorList","");
	if(count($arrInventoryVendor) >0)
	{
		foreach($arrInventoryVendor as $curInventoryVendor)
		{
			$tpl->set_var("vendor_name",$curInventoryVendor["vendor_name"]);
			$tpl->set_var("id",$curInventoryVendor["id"]);
			
			$tpl->set_var("DisplayContactInformation","");
			$tpl->set_var("DisplayNoContactInformation","");
			
			if(isset($curInventoryVendor["contact_information"]) && count($curInventoryVendor["contact_information"]) > 0)
			{
				foreach($curInventoryVendor["contact_information"] as $curContact)
				{
					$tpl->set_var("contact_person",$curContact["contact_person"]);
					$tpl->set_var("contact_no",$curContact["contact_no"]);
					$tpl->set_var("address",$curContact["address"]);

					$tpl->parse("DisplayContactInformation",true);
				}
			}
			else
			{
				$tpl->parse("DisplayNoContactInformation",false);
			}
			
			$tpl->parse("FillInventoryVendorList",true);
		}
	}

	$tpl->pparse('main',false);

?>
