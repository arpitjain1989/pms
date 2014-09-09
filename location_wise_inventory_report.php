<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('location_wise_inventory_report.html','main_container');

	/* Rights management */
	$PageIdentifier = "LocationWiseInventoryReport";
	include_once('userrights.php');
	
	/* Set heading */
	$tpl->set_var("mainheading","Location Wise Inventory Report");
	
	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Location Wise Inventory Report</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.inventory_location.php');

	$objInventoryLocation = new inventory_location();
	$arrInventoryLocation = $objInventoryLocation->fnGetAllInventoryLocation();

	/* Display list */
	$tpl->set_var("FillInventoryLocationList","");
	if(count($arrInventoryLocation) >0)
	{
		foreach($arrInventoryLocation as $curInventoryLocation)
		{
			$tpl->SetAllValues($curInventoryLocation);
			$tpl->parse("FillInventoryLocationList",true);
		}
	}

	$tpl->pparse('main',false);

?>
