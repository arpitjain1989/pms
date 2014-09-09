<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('requisition.html','main_container');

	/* Rights management */
	$PageIdentifier = "Requisition";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","Requisition");
	
	/* Set breadcrumb */
	$breadcrumb = '<li><a href="requisition_inventory_list.php">Manage Requisition</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Requisition</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.requisition_inventory.php');
	include_once('includes/class.requisition.php');

	$objRequisitionInventory = new requisition_inventory();
	$objRequisition = new requisition();

	/* Save requisition */
	if(isset($_POST["action"]) && trim($_POST["action"]) == "SaveRequisition")
	{
		$requisition_status = $objRequisition->fnSaveRequisition($_POST);

		if($requisition_status == 1)
		{
			header("Location: requisition_list.php?info=success");
			exit;
		}
		else if($requisition_status == 0)
		{
			header("Location: requisition_list.php?info=err");
			exit;
		}
	}

	/* Fill requisition for dropdown */
	
	$tpl->set_var("FillRequisitionForBlock","");
	$arrRequisitionFor = $objRequisitionInventory->fnGetRequisitionFor();

	if(count($arrRequisitionFor) > 0)
	{
		foreach($arrRequisitionFor as $curRequisitionFor)
		{
			$tpl->set_var("requisition_for_id",$curRequisitionFor["id"]);
			$tpl->set_var("requisition_for_name",$curRequisitionFor["title"]);
			
			$tpl->parse("FillRequisitionForBlock",true);
		}
	}

	$tpl->pparse('main',false);

?>
