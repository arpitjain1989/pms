<?php
	include('common.php');

	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('shift_movement_request_view.html','main_container');

	$PageIdentifier = "ShiftMovementRequest";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Shift Movement Request");
	$breadcrumb = '<li><a href="shift_movement_request.php">Manage Shift Movement Request</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Shift Movement Request</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.shift_movement.php');
	include_once('includes/class.employee.php');
	include_once('includes/class.leave.php');

	$objShiftMovement = new shift_movement();
	$objEmployee = new employee();
	$objLeaves = new leave();

	if(isset($_POST["hShiftMovementRequest"]) && trim($_POST["hShiftMovementRequest"]) == "ShiftMovementRequest")
	{
		$status = $objShiftMovement->fnUpdateShiftMovement($_POST);

		if($status == 0)
		{
			header("location: shift_movement_request.php?info=err");
		}
		else if($status == -1)
		{
			header("location: shift_movement_request.php?info=errtime");
		}
		else if($status == -2)
		{
			header("location: shift_movement_request.php?info=errdeactive");
		}
		else
		{
			header("location: shift_movement_request.php?info=success&s=".$status);
		}
		exit;
	}

	$tpl->set_var("DisplayMovementInformationBlock","");
	$tpl->set_var("DisplayNoMovementBlock","");

	$tpl->set_var("TLStatusViewBlock","");
	$tpl->set_var("ManagersStatusViewBlock","");
	$tpl->set_var("TLStatusBlock","");
	$tpl->set_var("ManagersStatusBlock","");

	$tpl->set_var("DelegatedTLStatusViewBlock","");
	$tpl->set_var("DelegatedManagersStatusViewBlock","");
	$tpl->set_var("DelegatedTLStatusBlock","");
	$tpl->set_var("DelegatedManagersStatusBlock","");
	$tpl->set_var("AdminNoteBlock","");

	$tpl->set_var("CancelButtonBlock","");
	
	$tpl->set_var("DisplayDelegatedTeamLeaderBlock","");
	$tpl->set_var("DisplayDelegatedManagerBlock","");

	$arrAllDelegatedEmployees = array();

	if(isset($_REQUEST["id"]))
	{
		if($_SESSION['usertype'] == 'admin')
		{
			$arrEmployee = $objEmployee->fnGetAllemployees(0);
		}
		else
		{
			$arrEmployee = $objEmployee->fnGetAllemployees($_SESSION['id']);
			
			/* Fetch employees who are delegated */
			$arrDelegatedEmployee = array();
			$arrtemp = array();
			
			$arrDelegatedManagerId = $objEmployee->fnGetDelegateManagerId($_SESSION['id']);
			if(count($arrDelegatedManagerId) > 0 )
			{
				foreach($arrDelegatedManagerId as $delegatesManagerIds)
				{
					$arrtemp = $objEmployee->fnGetAllemployees($delegatesManagerIds);
					$arrDelegatedEmployee = $arrDelegatedEmployee + $arrtemp;
				}
			}

			$arrDelegatedTeamLeaderId = $objEmployee->fnGetDelegateEmployeeId($_SESSION['id']);	
			if(count($arrDelegatedTeamLeaderId) > 0 )
			{
				foreach($arrDelegatedTeamLeaderId as $delegatesIds)
				{
					$arrtemp = $objEmployee->fnGetAllemployees($delegatesIds);
					$arrDelegatedEmployee = $arrDelegatedEmployee + $arrtemp ;
				}
			}
			
			/* Merge current employees with delegated employees */
			$arrEmployee = $arrEmployee + $arrDelegatedEmployee;
			$arrAllDelegatedEmployees = $arrDelegatedEmployee;
		}

		$arrEmployee[] = "";

		if(count($arrEmployee) > 0)
		{
			$arrEmployee = array_filter($arrEmployee,'strlen');
		}

		$ids = "";
		if(count($arrEmployee) > 0)
		{
			$ids = implode(',',$arrEmployee);
		}

		$MovementInfo = $objShiftMovement->fnShiftMovementById($_REQUEST["id"], $ids);

		//$tpl->set_var("DisplayDeligationBlock","");
		//$tpl->set_var("FillDelegateBlock","");

		if($MovementInfo)
		{
			$tpl->SetAllValues($MovementInfo);
		
			$MovementFor = $MovementInfo["userid"];
			$MovementUserInfo = $objEmployee->fnGetEmployeeDetailById($MovementFor);
			
			if(isset($MovementInfo["isadminadded"]) && trim($MovementInfo["isadminadded"]) == "1")
			{
				$tpl->parse("AdminNoteBlock",false);
			}
			else
			{
				if(isset($_SESSION["usertype"]))
				{
					if($_SESSION["usertype"] == "admin")
					{
						if($MovementInfo["reportinghead1"] != "" && $MovementInfo["reportinghead1"] != "0")
						{
							$tpl->parse("TLStatusViewBlock",false);
						}

						if($MovementInfo["reportinghead2"] != "" && $MovementInfo["reportinghead2"] != "0")
						{
							$tpl->parse("ManagersStatusViewBlock",false);
						}

						if($MovementInfo["delegatedtl_id"] != "" && $MovementInfo["delegatedtl_id"] != "0")
						{
							$tpl->parse("DelegatedTLStatusViewBlock",false);
						}

						if($MovementInfo["delegatedmanager_id"] != "" && $MovementInfo["delegatedmanager_id"] != "0")
						{
							$tpl->parse("DelegatedManagersStatusViewBlock",false);
						}
					}

					if($_SESSION["usertype"] == "employee")
					{
						$tpl->set_var("ltype",$_SESSION["designation"]);

						$showCancel = false;

						if($MovementInfo["reportinghead1"] != "" && $MovementInfo["reportinghead1"] != 0)
						{
							if($MovementInfo["reportinghead1"] == $_SESSION["id"])
							{
								if($MovementInfo["approvedby_manager"] == "0" && $MovementInfo["delegatedmanager_status"] == "0")
								{
									$tpl->parse("TLStatusBlock",false);
									$showCancel = true;
								}
								else
								{
									$tpl->parse("TLStatusViewBlock",false);
								}
							}
							else
							{
								$tpl->parse("TLStatusViewBlock",false);
							}
						}

						if($MovementInfo["reportinghead2"] != "" && $MovementInfo["reportinghead2"] != 0)
						{
							if($MovementInfo["reportinghead2"] == $_SESSION["id"])
							{
								if($MovementInfo["approvedby_tl"] == "2" || ($MovementInfo["approvedby_tl"] == "0" && $MovementInfo["delegatedtl_status"] == "2" ))
								{
									$tpl->parse("ManagersStatusViewBlock",false);
								}
								else
								{
									$tpl->parse("ManagersStatusBlock",false);
									$showCancel = true;
								}
							}
							else
							{
								$tpl->parse("ManagersStatusViewBlock",false);
							}
						}

						if($MovementInfo["delegatedtl_id"] != "" && $MovementInfo["delegatedtl_id"] != 0)
						{
							if($MovementInfo["delegatedtl_id"] == $_SESSION["id"])
							{
								if($MovementInfo["approvedby_manager"] == "0" && $MovementInfo["delegatedmanager_status"] == "0")
								{
									$tpl->parse("DelegatedTLStatusBlock",false);
									$showCancel = true;
								}
								else
								{
									$tpl->parse("DelegatedTLStatusViewBlock",false);
								}
							}
							else
							{
								$tpl->parse("DelegatedTLStatusViewBlock",false);
							}
						}
						else
						{
							$considerCurDelegate = 0;
							$checkDeligateId = $objLeaves->fnCheckDeligate($MovementInfo['reportinghead1']);
							if(isset($checkDeligateId) && $checkDeligateId != "")
								$considerCurDelegate = $checkDeligateId;
							
							if(in_array($MovementInfo["userid"],$arrAllDelegatedEmployees) && $considerCurDelegate == $_SESSION["id"] && $MovementInfo["approvedby_manager"] == "0" && $MovementInfo["delegatedmanager_status"] == "0")
							{
								$tpl->set_var("curdelegatedtl_id",$_SESSION["id"]);
								$tpl->parse("DisplayDelegatedTeamLeaderBlock",false);
								
								$tpl->parse("DelegatedTLStatusBlock",false);
								$showCancel = true;
							}
						}

						if($MovementInfo["delegatedmanager_id"] != "" && $MovementInfo["delegatedmanager_id"] != 0)
						{
							if($MovementInfo["delegatedmanager_id"] == $_SESSION["id"])
							{
								if($MovementInfo["approvedby_tl"] == "2" || ($MovementInfo["approvedby_tl"] == "0" && $MovementInfo["delegatedtl_status"] == "2" ))
								{
									$tpl->parse("DelegatedManagersStatusViewBlock",false);
								}
								else
								{
									$tpl->parse("DelegatedManagersStatusBlock",false);
									$showCancel = true;
								}
							}
							else
							{
								$tpl->parse("DelegatedManagersStatusViewBlock",false);
							}
						}
						else
						{
							$considerCurDelegate = 0;
							$checkDeligateId = $objLeaves->fnCheckDeligate($MovementInfo['reportinghead2']);
							if(isset($checkDeligateId) && $checkDeligateId != "")
								$considerCurDelegate = $checkDeligateId;

							if(in_array($MovementInfo["userid"],$arrAllDelegatedEmployees) && $considerCurDelegate == $_SESSION["id"])
							{
								if(!($MovementInfo["approvedby_tl"] == "2" || ($MovementInfo["approvedby_tl"] == "0" && $MovementInfo["delegatedtl_status"] == "2" )))
								{
									$tpl->set_var("curdelegatedmanager_id",$_SESSION["id"]);
									$tpl->parse("DisplayDelegatedManagerBlock",false);

									$tpl->parse("DelegatedManagersStatusBlock",false);
									$showCancel = true;
								}
							}
						}

						if($MovementInfo['isCancel'] == 0 && $showCancel == true)
						{
							$tpl->parse("CancelButtonBlock",true);
						}
					}
				}
			}
			$tpl->parse("DisplayMovementInformationBlock",false);
		}
		else
		{
			$tpl->parse("DisplayNoMovementBlock",false);
		}
	}
	else
	{
		$tpl->parse("DisplayNoMovementBlock",false);
	}

	$tpl->pparse('main',false);
?>
