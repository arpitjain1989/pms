<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('shift_movement_compensation_request_view.html','main_container');

	$PageIdentifier = "ShiftMovementCompensationRequest";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Compensation Request");
	$breadcrumb = '<li><a href="shift_movement_compensation_requests.php">Manage Compensation Request</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Compensation Request</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.shift_movement.php');
	include_once('includes/class.leave.php');
	include_once('includes/class.employee.php');

	$objShiftMovement = new shift_movement();
	$objLeaves = new leave();
	$objEmployee = new employee();
	
	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "CompensationRequest")
	{
		$retStatus = $objShiftMovement->fnUpdateShiftMovementCompensation($_POST);
		if($retStatus!= false)
		{
			header("Location: shift_movement_compensation_requests.php?info=success&s=".$retStatus);
			exit;
		}
		else
		{
			header("Location: shift_movement_compensation_requests.php?info=err");
			exit;
		}
	}
	
	$tpl->set_var("DisplayMovementCompensationReqestInformationBlock","");
	$tpl->set_var("DisplayNoMovemenCompensationtRequestBlock","");
	$tpl->set_var("DisplayHeadEntryBlock","");
	$tpl->set_var("DisplayHeadDisplayBlock","");
	$tpl->set_var("DisplaySubmitButtonBlock","");
	$tpl->set_var("DisplayDelegatedTeamleaderEntryBlock","");
	$tpl->set_var("DisplayDelegatedTeamleaderDisplayBlock","");
	$tpl->set_var("DisplayAdminNoteBlock","");
	$tpl->set_var("DisplayDelegatedTeamLeaderBlock","");
	
	if(isset($_REQUEST["id"]))
	{
		$CompensationInfo = $objShiftMovement->fnShiftMovementCompensationById($_REQUEST["id"]);

		if(count($CompensationInfo) > 0)
		{
			$tpl->SetAllValues($CompensationInfo);
			
			if(isset($MovementInfo["isadminadded"]) && trim($MovementInfo["isadminadded"]) == "1")
			{
				/* If added by admin, display the admin note. */
				$tpl->parse("DisplayAdminNoteBlock",false);
			}
			else
			{
				if($CompensationInfo["firstreportingheadid"] == $_SESSION["id"])
				{
					if($CompensationInfo["approvedby_tl"] == "0")
					{
						$tpl->parse("DisplayHeadEntryBlock",false);
						$tpl->parse("DisplaySubmitButtonBlock",false);
					}
					else
					{
						$tpl->parse("DisplayHeadDisplayBlock",false);
					}
					
					if($CompensationInfo["delegatedtl_id"] != "0" && $CompensationInfo["delegatedtl_id"] != "")
					{
						$tpl->parse("DisplayDelegatedTeamleaderDisplayBlock",false);
					}
				}
				else
				{
					$tpl->parse("DisplayHeadDisplayBlock",false);
					if($CompensationInfo["delegatedtl_id"] != "0" && $CompensationInfo["delegatedtl_id"] != "")
					{
						$tpl->parse("DisplayDelegatedTeamleaderDisplayBlock",false);
					}
				}

				if($CompensationInfo["delegatedtl_id"] == $_SESSION["id"])
				{
					if($CompensationInfo["delegatedtl_status"] == "0")
					{
						$tpl->parse("DisplayHeadDisplayBlock",false);
						$tpl->parse("DisplayDelegatedTeamleaderEntryBlock",false);
						$tpl->parse("DisplaySubmitButtonBlock",false);
					}
					else
					{
						$tpl->parse("DisplayHeadDisplayBlock",false);
						$tpl->parse("DisplayDelegatedTeamleaderDisplayBlock",false);
					}
				}
				else
				{
					$considerCurDelegate = 0;
					$checkDeligateId = $objLeaves->fnCheckDeligate($CompensationInfo["firstreportingheadid"]);
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
					if(in_array($CompensationInfo['employeeid'],$arrAllDelegatedEmployees) && $considerCurDelegate == $_SESSION["id"])
					{
						if($CompensationInfo["delegatedtl_status"] == "0")
						{
							$tpl->parse("DisplayHeadDisplayBlock",false);
							$tpl->parse("DisplayDelegatedTeamleaderEntryBlock",false);
							$tpl->parse("DisplaySubmitButtonBlock",false);
							
							$tpl->set_var("curdelegatedtl_id",$_SESSION["id"]);
							$tpl->parse("DisplayDelegatedTeamLeaderBlock",false);
						}
						else
						{
							$tpl->parse("DisplayHeadDisplayBlock",false);
							$tpl->parse("DisplayDelegatedTeamleaderDisplayBlock",false);
						}
					}
				}
			}
			$tpl->parse("DisplayMovementCompensationReqestInformationBlock",false);
		}
		else
		{
			$tpl->parse("DisplayNoMovemenCompensationtRequestBlock",false);
		}
	}
	else
	{
		$tpl->parse("DisplayNoMovemenCompensationtRequestBlock",false);
	}
	
	$tpl->pparse('main',false);
?>
