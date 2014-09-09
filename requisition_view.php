<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('requisition_view.html','main_container');

	/* Rights management */
	$PageIdentifier = "Requisition";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","Requisition");
	
	/* Set breadcrumb */
	$breadcrumb = '<li><a href="requisition_list.php">Manage Requisition</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Requisition</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.requisition.php');

	$objRequisition = new requisition();

	/* Fetch user requisition information by ID */
	$tpl->set_var("DisplayRequisitionInformationBlock","");
	$tpl->set_var("DisplayNoRequisitionInformationBlock","");
	$tpl->set_var("DisplayTillDateBlock","");
	$tpl->set_var("DisplayDelegateViewBlock","");
	
	if(isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		$requisitionInformation = $objRequisition->fnGetUserRequisitionById(trim($_REQUEST["id"]), $_SESSION["id"]);

		if(count($requisitionInformation) > 0)
		{
			$tpl->setAllValues($requisitionInformation);
			
			if(isset($requisitionInformation["delegated_reporting_head_id"]) && $requisitionInformation["delegated_reporting_head_id"] != '0')
				$tpl->parse("DisplayDelegateViewBlock",false);
			
			if(isset($requisitionInformation["request_type"]) && $requisitionInformation["request_type"] == "2")
				$tpl->parse("DisplayTillDateBlock",false);
			
			$tpl->parse("DisplayRequisitionInformationBlock",false);
		}
		else
		{
			$tpl->parse("DisplayNoRequisitionInformationBlock",false);
		}
	}
	else
	{
		$tpl->parse("DisplayNoRequisitionInformationBlock",false);
	}

	$tpl->pparse('main',false);

?>
