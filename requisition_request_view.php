<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('requisition_request_view.html','main_container');

	/* Rights management */
	$PageIdentifier = "RequisitionRequest";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","Requisition");

	/* Set breadcrumb */
	$breadcrumb = '<li><a href="requisition_request_list.php">Manage Requisition Request</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Requisition Request</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.requisition.php');
	include_once('includes/class.leave.php');
	include_once('includes/class.employee.php');

	$objRequisition = new requisition();
	$objLeaves = new leave();
	$objEmployee = new employee();

	if(isset($_POST["action"]) && trim($_POST["action"]) == "RequisitionRequest")
	{
		$requisitionStatus = $objRequisition->fnUpdateRequisitionRequest($_POST);
		if($requisitionStatus == true)
		{
			header("Location: requisition_request_list.php?info=success");
			exit;
		}
		else
		{
			header("Location: requisition_request_list.php?info=err");
			exit;
		}
	}

	/* Fetch user requisition information by ID */
	$tpl->set_var("DisplayRequisitionRequestInformationBlock","");
	$tpl->set_var("DisplayNoRequisitionRequestInformationBlock","");
	$tpl->set_var("DisplayTillDateBlock","");
	$tpl->set_var("ApprovalStatusDisplayBlock","");
	$tpl->set_var("DelegateApprovalStatusDisplayBlock","");
	$tpl->set_var("ApprovalStatusEntryBlock","");
	$tpl->set_var("DelegateApprovalStatusEntryBlock","");
	$tpl->set_var("DisplaySubmitButtonBlock","");
	$tpl->set_var("DisplayDelegatedTeamLeaderBlock","");

	if(isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		$requisitionInformation = $objRequisition->fnGetRequisitionRequestById(trim($_REQUEST["id"]), $_SESSION["id"]);

		if(count($requisitionInformation) > 0)
		{
			$tpl->setAllValues($requisitionInformation);

			if(isset($requisitionInformation["request_type"]) && $requisitionInformation["request_type"] == "2")
				$tpl->parse("DisplayTillDateBlock",false);

			$displaySubmit = false;

			if($_SESSION["id"] == $requisitionInformation["reporting_head"])
			{
				if($requisitionInformation["approval_status"] == 0 && $requisitionInformation["delegated_reporting_head_status"] == 0)
				{
					$tpl->parse("ApprovalStatusEntryBlock",false);
					$displaySubmit = true;
				}
				else
				{
					$tpl->parse("ApprovalStatusDisplayBlock",false);
				}
			}
			else
			{
				$tpl->parse("ApprovalStatusDisplayBlock",false);
			}
			
			if($_SESSION["id"] == $requisitionInformation["delegated_reporting_head_id"])
			{
				if($requisitionInformation["approval_status"] == 0 && $requisitionInformation["delegated_reporting_head_status"] == 0)
				{
					$tpl->parse("DelegateApprovalStatusEntryBlock",false);
					$displaySubmit = true;
				}
				else
				{
					$tpl->parse("DelegateApprovalStatusDisplayBlock",false);
				}
			}
			else
			{
				$considerCurDelegate = 0;
				$checkDeligateId = $objLeaves->fnCheckDeligate($requisitionInformation["reporting_head"]);
				if(isset($checkDeligateId) && $checkDeligateId != "")
					$considerCurDelegate = $checkDeligateId;
				
				/* Get delegated teamleader id */
				$arrDelegatedTeamLeaderId = $objEmployee->fnGetDelegateEmployeeId($_SESSION['id']);

				/* Get Delegated Manager id */
				$arrDelegatedManagerId = $objEmployee->fnGetDelegateManagerId($_SESSION['id']);
				
				$arrDelegatedEmployee = array();
				$arrtemp = array();
				if(count($arrDelegatedTeamLeaderId) > 0 )
				{
					foreach($arrDelegatedTeamLeaderId as $delegatesIds)
					{
						$arrtemp = $objEmployee->fnGetAllemployees($delegatesIds);
						$arrDelegatedEmployee =$arrDelegatedEmployee + $arrtemp ;
					}
				}
				if(count($arrDelegatedManagerId) > 0 )
				{
					foreach($arrDelegatedManagerId as $delegatesManagerIds)
					{
						$arrtemp = $objEmployee->fnGetAllemployees($delegatesManagerIds);
						$arrDelegatedEmployee = $arrDelegatedEmployee + $arrtemp;
					}
				}
				
				$temp1 = $objEmployee->fnGetAllemployees($_SESSION['id']);
				$arrEmployee = $temp1 + $arrDelegatedEmployee;
				$arrAllDelegatedEmployees = $arrDelegatedEmployee;

				/* If user delegated, but leave form added before delegation */
				if(in_array($requisitionInformation['user_id'],$arrAllDelegatedEmployees) && $considerCurDelegate == $_SESSION["id"])
				{
					if($requisitionInformation["approval_status"] == 0 && $requisitionInformation["delegated_reporting_head_status"] == 0)
					{
						$tpl->parse("DelegateApprovalStatusEntryBlock",false);
						$displaySubmit = true;
						
						$tpl->set_var("curdelegated_reporting_head_id",$_SESSION["id"]);
						$tpl->parse("DisplayDelegatedTeamLeaderBlock",false);
					}
					else
					{
						$tpl->parse("DelegateApprovalStatusDisplayBlock",false);
					}
				}
			}

			if($requisitionInformation["delegated_reporting_head_id"] != "" && $requisitionInformation["delegated_reporting_head_id"] != "0")
			{
				$tpl->parse("DelegateApprovalStatusDisplayBlock",false);
			}

			if($displaySubmit)
				$tpl->parse("DisplaySubmitButtonBlock",false);

			/*if($requisitionInformation["approval_status"] == 0)
			{
				$tpl->parse("ApprovalStatusEntryBlock",false);
				$tpl->parse("DisplaySubmitButtonBlock",false);
			}
			else
				$tpl->parse("ApprovalStatusDisplayBlock",false);
			*/

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
