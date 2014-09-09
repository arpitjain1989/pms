<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('it_requisition_view.html','main_container');

	/* Rights management */
	$PageIdentifier = "ITRequisition";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","View Requistion");

	/* Set breadcrumb */
	$breadcrumb = '<li><a href="it_requisition_list.php">Manage Requistion</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Requistion</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.requisition.php');

	$objRequisition = new requisition();

	if(isset($_POST["action"]) && trim($_POST["action"]) == "CloseRequisition")
	{
		$requisitionStatus = $objRequisition->fnCloseRequisition($_POST);
		if($requisitionStatus == true)
		{
			header("Location: it_requisition_list.php?info=success");
			exit;
		}
		else
		{
			header("Location: it_requisition_list.php?info=err");
			exit;
		}
	}

	/* Fetch user requisition information by ID */
	$tpl->set_var("DisplayRequisitionRequestInformationBlock","");
	$tpl->set_var("DisplayNoRequisitionRequestInformationBlock","");
	$tpl->set_var("DisplayTillDateBlock","");
	$tpl->set_var("DisplaySubmitButtonBlock","");
	$tpl->set_var("DisplayItSupportViewBlock","");
	$tpl->set_var("EntryItSupportViewBlock","");
	$tpl->set_var("DisplayDelegateViewBlock","");

	if(isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		$requisitionInformation = $objRequisition->fnGetApprovedRequisitionById($_REQUEST["id"]);

		if(count($requisitionInformation) > 0)
		{
			$tpl->setAllValues($requisitionInformation);

			if(isset($requisitionInformation["request_type"]) && $requisitionInformation["request_type"] == "2")
				$tpl->parse("DisplayTillDateBlock",false);

			if(isset($requisitionInformation["delegated_reporting_head_id"]) && $requisitionInformation["delegated_reporting_head_id"] != 0)
				$tpl->parse("DisplayDelegateViewBlock",false);

			if($requisitionInformation["isclosed"] == "0")
			{
				$tpl->parse("EntryItSupportViewBlock",false);
				$tpl->parse("DisplaySubmitButtonBlock",false);
			}
			else
				$tpl->parse("DisplayItSupportViewBlock",false);

			$tpl->parse("DisplayRequisitionRequestInformationBlock",false);
		}
		else
		{
			$tpl->parse("DisplayNoRequisitionRequestInformationBlock",false);
		}
	}
	else
	{
		$tpl->parse("DisplayNoRequisitionRequestInformationBlock",false);
	}

	$tpl->pparse('main',false);

?>
