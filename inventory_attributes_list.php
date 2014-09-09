<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('inventory_attributes_list.html','main_container');

	/* Rights management */
	$PageIdentifier = "InventoryAttributes";
	include_once('userrights.php');
	
	/* Set heading */
	$tpl->set_var("mainheading","Manage Inventory Attributes");
	
	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Manage Inventory Attributes</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.inventory_attributes.php');

	/* Display message block */
	$tpl->set_var("DisplayMessageBlock","");
	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'success':
				$messageClass = "alert-success";
				$message = "Inventory Attribute added successfully";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Inventory Attribute already added. Cannot add again.";
				break;
		}

		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	$objInventoryAttribute = new inventory_attributes();
	$arrInventoryAttribute = $objInventoryAttribute->fnGetAllInventoryAttributes();

	/* Display list */
	$tpl->set_var("FillInventoryAttributesList","");
	if(count($arrInventoryAttribute) >0)
	{
		foreach($arrInventoryAttribute as $curInventoryAttribute)
		{
			$tpl->SetAllValues($curInventoryAttribute);
			$tpl->parse("FillInventoryAttributesList",true);
		}
	}

	$tpl->pparse('main',false);

?>
