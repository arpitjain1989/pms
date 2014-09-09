<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('inventory_make_list.html','main_container');

	/* Rights management */
	$PageIdentifier = "InventoryMake";
	include_once('userrights.php');
	
	/* Set heading */
	$tpl->set_var("mainheading","Manage Inventory Make");
	
	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Manage Inventory Make</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.inventory_make.php');

	/* Display message block */
	$tpl->set_var("DisplayMessageBlock","");
	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'success':
				$messageClass = "alert-success";
				$message = "Inventory Make added successfully";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Inventory Make already added. Cannot add again.";
				break;
		}

		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	$objInventoryMake = new inventory_make();
	$arrInventoryMake = $objInventoryMake->fnGetAllInventoryMake();

	/* Display list */
	$tpl->set_var("FillInventoryMakeList","");
	if(count($arrInventoryMake) >0)
	{
		foreach($arrInventoryMake as $curInventoryMake)
		{
			$tpl->SetAllValues($curInventoryMake);
			$tpl->parse("FillInventoryMakeList",true);
		}
	}

	$tpl->pparse('main',false);

?>
