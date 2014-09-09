<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('inventory_type_list.html','main_container');

	/* Rights management */
	$PageIdentifier = "InventoryType";
	include_once('userrights.php');
	
	/* Set heading */
	$tpl->set_var("mainheading","Manage Inventory Type");
	
	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Manage Inventory Type</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.inventory_type.php');

	/* Display message block */
	$tpl->set_var("DisplayMessageBlock","");
	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'success':
				$messageClass = "alert-success";
				$message = "Inventory Type added successfully";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Inventory Type already added. Cannot add again.";
				break;
		}

		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	$objInventoryType = new inventory_type();
	$arrInventoryType = $objInventoryType->fnGetAllInventoryType();

	/* Display list */
	$tpl->set_var("FillInventoryTypeList","");
	if(count($arrInventoryType) >0)
	{
		foreach($arrInventoryType as $curInventoryType)
		{
			$tpl->SetAllValues($curInventoryType);
			$tpl->parse("FillInventoryTypeList",true);
		}
	}

	$tpl->pparse('main',false);

?>
