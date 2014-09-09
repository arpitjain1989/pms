<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('it_expired_requisition_view.html','main_container');

	/* Rights management */
	$PageIdentifier = "ITExpiredRequisition";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","View Expired Requistion");

	/* Set breadcrumb */
	$breadcrumb = '<li><a href="it_expired_requisition_list.php">Manage Expired Requistion</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Expired Requistion</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.requisition.php');

	$objRequisition = new requisition();

	if(isset($_POST["action"]) && trim($_POST["action"]) == "CloseExpiredRequisition")
	{
		$requisitionStatus = $objRequisition->fnCloseExpiredRequisition($_POST);
		if($requisitionStatus == true)
		{
			header("Location: it_expired_requisition_list.php?info=success");
			exit;
		}
		else
		{
			header("Location: it_expired_requisition_list.php?info=err");
			exit;
		}
	}

	/* Fetch user requisition information by ID */
	$tpl->set_var("DisplayExpiredRequisitionInformationBlock","");
	$tpl->set_var("DisplayNoExpiredRequisitionInformationBlock","");

	if(isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		$requisitionInformation = $objRequisition->fnGetExpiredRequisitionByRequisitionId($_REQUEST["id"]);

		if(count($requisitionInformation) > 0)
		{
			$tpl->setAllValues($requisitionInformation);

			$tpl->parse("DisplayExpiredRequisitionInformationBlock",false);
		}
		else
		{
			$tpl->parse("DisplayNoExpiredRequisitionInformationBlock",false);
		}
	}
	else
	{
		$tpl->parse("DisplayNoExpiredRequisitionInformationBlock",false);
	}

	$tpl->pparse('main',false);

?>
