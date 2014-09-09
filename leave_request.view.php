<?php
	include('common.php');
	$tpl = new Template($app_path);
	//print_r($_SESSION);
	$tpl->load_file('template.html','main');
	$tpl->load_file('leave_request.view.html','main_container');

	$PageIdentifier = "LeaveRequest";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Leave Request");
	$breadcrumb = '<li><a href="employee.php">Manage Leave Request</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Leave Request</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.leave.php');
	include_once('includes/class.employee.php');
	include_once('includes/class.attendance.php');
	include_once('includes/class.designation.php');

	$objLeaves = new leave();
	$objEmployee = new employee();
	$objAttendance = new attendance();
	$objDesignation = new designations();

	$arrLeaves = $objLeaves->fnGetLeaveDetailsById($_REQUEST['id']);

	$reportingHead = $objEmployee->fnGetReportingHeadById($arrLeaves['employee_id']);
	//echo '<pre>'; print_r($reportingHead); die;
	$lastThreeLeaves = $objLeaves->fnGetLastThreeLeaves($arrLeaves['employee_id'],$arrLeaves['leave_id']);


	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] == 'update')
	{
		//echo '<pre>'; print_r($_POST);
		/* Update Leave status */
		$LeaveInfo = $objLeaves->fnLeaveInfoById($_POST["hdnid"]);
		//print_r($LeaveInfo); die;
		
		/* do not save again if the same status. */
		
		if((isset($_POST["status_manager"]) && $_POST["status_manager"] != $LeaveInfo["status_manager"]) || (isset($_POST["status"]) && $_POST["status"] != $LeaveInfo["status"]) || (isset($_POST["manager_delegate_status"]) && $_POST["manager_delegate_status"] != $LeaveInfo["manager_delegate_status"]) || (isset($_POST["delegate_status"]) && $_POST["delegate_status"] != $LeaveInfo["delegate_status"]))
		{
			//echo 'hello'; die;
			$arrLeave = $objLeaves->fnUpdateLeaveStatus($_POST);
			if($arrLeave)
			{
				//echo 'hello'; die;
				header("Location: leave_request.php?info=update");
				exit;
			}
			else
			{
				header("Location: leave_request.php?info=upderr");
				exit;
			}
		}
		else
		{
			header("Location: leave_request.php?info=upderr");
			exit;
		}
	}

	/* Get All Team Leader for Team Leader delegation dropdown */
	/*$getAllTeamLeaders = $objEmployee->fnGetAllTeamLeaders($arrLeaves['id'],$arrLeaves['designation'],$_SESSION['designation'],$arrLeaves['starting_date'],$arrLeaves['ending_date']);*/

	/* Get All managers for Manager delegation dropdown */
	/*$getAllManagers = $objEmployee->fnGetAllManagers($arrLeaves['id'],$arrLeaves['designation'],$_SESSION['designation']);*/
	
	$tpl->set_var("FillDeligation","");
	$tpl->set_var("FillDelegateEmployees","");
	//$tpl->set_var("session_designation",$_SESSION['designation']);
	//$tpl->set_var("session_id",$_SESSION['id']);
	//$tpl->set_var("session_delegateTeamleader",$arrLeaves['deligateTeamLeaderId']);
	//$tpl->set_var("session_delegateManager",$arrLeaves['deligateManagerId']);
	//$tpl->set_var("actual_Teamleader",$arrLeaves['teamleader_id']);
	//$tpl->set_var("actual_Designation",$arrLeaves['designation']);

	/* Fill delegation */

	/* Fetch reporting head hierarchy */
	$arrDesignation = $objDesignation->fnGetDesignationById($arrLeaves["designation"]);

	//if(($_SESSION['designation'] == 6 || $_SESSION['designation'] == 8 || $_SESSION['designation'] == 17) &&  ($arrLeaves['designation'] == 7 || $arrLeaves['designation'] == 13 || $arrLeaves['designation'] == 6))

	/* Filling values in the team leader delegation dropdown */
	/*if(($_SESSION['designation'] == 8 && $arrLeaves['designation'] == 6) || $_SESSION['designation'] == 6 && $arrLeaves['designation'] == 7 || $arrLeaves['designation'] == 13)
	{
		if(count($getAllTeamLeaders)> 0)
		{
			foreach($getAllTeamLeaders as $AllTeamLeaders)
			{
				$tpl->setAllValues($AllTeamLeaders);
				$tpl->parse('FillTeamLeaders',true);
			}
		}
		$tpl->SetAllValues($arrLeaves);
		$tpl->parse("FillDeligation",false);
	}*/

	/* Filling values in the manager delegation dropdown */
	
	/*if($_SESSION['designation'] == '17' && ($arrLeaves['designation'] == '6' || $arrLeaves['designation'] == '18' || $arrLeaves['designation'] == '19'))
	{
		if(count($getAllManagers)> 0)
		{
			foreach($getAllManagers as $AllManagers)
			{
				$tpl->setAllValues($AllManagers);
				$tpl->parse('FillManagers',true);
			}
		}
		$tpl->SetAllValues($arrLeaves);
		$tpl->parse("FillManagerDeligation",false);
	}*/

	$tpl->set_var('id',$_REQUEST['id']);
	if($reportingHead != '')
	{
		if($reportingHead == '0')
		{
			$tpl->set_var('reportingHead',"Admin");
		}
		else
		{
			$tpl->set_var('reportingHead',$reportingHead);
		}
	}
	else
	{
		$tpl->set_var('reportingHead',"Admin");
	}

	$arrAllDelegatedEmployees = array();

	if($_SESSION['usertype'] == 'admin')
	{
		$arrEmployee = $objEmployee->fnGetAllemployees(0);
	}
	else
	{
		//$arrEmployee = $objEmployee->fnGetAllemployees($_SESSION['id']);
		
		//echo $_SESSION['id'];
		/* Get delegated teamleader id */
		$arrDelegatedTeamLeaderId = $objEmployee->fnGetDelegateEmployeeId($_SESSION['id']);

		/* Get Delegated Manager id */
		$arrDelegatedManagerId = $objEmployee->fnGetDelegateManagerId($_SESSION['id']);
		
		/*echo 'hello';

		print_r($arrDelegatedManagerId);

		echo 'hello1';*/
		$arrDelegatedEmployee = array();
		$arrtemp = array();
		if(count($arrDelegatedTeamLeaderId) > 0 )
		{
			foreach($arrDelegatedTeamLeaderId as $delegatesIds)
			{
				//echo $delegatesIds;
				$arrtemp = $objEmployee->fnGetAllemployees($delegatesIds);
				$arrDelegatedEmployee =$arrDelegatedEmployee + $arrtemp ;
			}
		}
		if(count($arrDelegatedManagerId) > 0 )
		{
			foreach($arrDelegatedManagerId as $delegatesManagerIds)
			{
				//echo $delegatesIds;
				$arrtemp = $objEmployee->fnGetAllemployees($delegatesManagerIds);
				$arrDelegatedEmployee =$arrDelegatedEmployee + $arrtemp ;
			}
		}
		//print_r($arrDelegatedEmployee);
		$temp1 = $objEmployee->fnGetAllemployees($_SESSION['id']);
		$arrEmployee = $temp1 + $arrDelegatedEmployee;
		$arrAllDelegatedEmployees = $arrDelegatedEmployee;
		//$arrEmployee = $objEmployee->fnGetAllemployees($_SESSION['id']);
		
	}

	$arrEmployee[] = "";

	if(count($arrEmployee) > 0)
	{
		$arrEmployee = array_filter($arrEmployee,'strlen');
	}

	$ids = "0";
	if(count($arrEmployee) > 0)
	{
		$ids = implode(',',$arrEmployee);
	}

	$arrLeaveRecords = $objLeaves->fnGetLeaveDetailByHead($arrLeaves['leave_id'],$ids,$arrLeaves['starting_date'],$arrLeaves['ending_date']);

	$tpl->set_var("DisplayActionButtons","");
	$tpl->set_var("DisplayBackButtons","");

	/* Null all blocks for status and comments */
	$tpl->set_var("FillTemaLeaderEditable","");
	$tpl->set_var("FillTemaLeaderReadable","");
	$tpl->set_var("FillDeligationEditable","");
	$tpl->set_var("FillDelegationReadable","");
	$tpl->set_var("FillManagerDeligationEditable","");
	$tpl->set_var("FillManagerDelegationReadable","");
	$tpl->set_var("FillManagerEditable","");
	//$tpl->set_var("FillDeligation","");
	//$tpl->set_var("FillManagerDeligation","");
	$tpl->set_var("FillManagerReadable","");
	$tpl->set_var("DisplayDelegatedTeamLeaderBlock","");
	$tpl->set_var("DisplayDelegatedManagerBlock","");
	$tpl->set_var("DisplayAdminAddedBlock","");
	$tpl->set_var('PhLabel','');

	/*if($_SESSION['usertype'] == 'admin')
	{
		$tpl->parse("DisplayBackButtons",false);
	}
	else
	{
		$tpl->parse("DisplayActionButtons",false);
	}*/

	//$tpl->set_var("FillDeligationEditable",'');

	if(isset($arrLeaves))
	{
		$tpl->SetAllValues($arrLeaves);

		/* Set leave balance */
		$pendingLeaves = $objAttendance->fnGetLastLeaveBalance($arrLeaves['employee_id']);

		$unpaid_leaves = $objAttendance->fnGetUserLeavesWithoutPayByMonthAndYear($arrLeaves['id'], Date('m'), Date('Y'));

		//$pendingLeaves = $pendingLeaves - $unpaid_leaves;

		$pendingLeaveBalance = 0;
		if($pendingLeaves > 0)
			$pendingLeaveBalance = $pendingLeaves;
		
		$tpl->set_var("pending_leave",$pendingLeaveBalance);
		
		$arrApprovalStatus = array("0"=>"Pending", "1"=>"Approved", "2"=>"Rejected");
		
		/* Approval status and comment by manager */
		$tpl->set_var("status_manager1",$arrApprovalStatus[$arrLeaves['status_manager']]);
		$tpl->set_var("comment_manager1",$arrLeaves['comment_manager']);
		$tpl->set_var("comment_manager_readable",$arrLeaves['comment_manager']);
		$tpl->set_var("status_manager_readable",$arrLeaves['status_manager']);

		/* Approval status and comment by team leader */
		$tpl->set_var("status1",$arrApprovalStatus[$arrLeaves['leave_status']]);
		$tpl->set_var("comment1",$arrLeaves['comment']);

		/* Approval status and comment by delegated team leader */
		$tpl->set_var("status2",$arrApprovalStatus[$arrLeaves['delegate_status']]);
		$tpl->set_var("comment2",$arrLeaves['delegate_comment']);

		/* Approval status and comment by delegated manager */
		$tpl->set_var("status3",$arrApprovalStatus[$arrLeaves['manager_delegate_status']]);
		$tpl->set_var("comment3",$arrLeaves['manager_delegate_comment']);
		
		$displayAction = false;
		$displayDelegate = false;
		
		if($arrLeaves["isadminadded"] == "1")
		{
			$tpl->parse("DisplayAdminAddedBlock",false);
			$tpl->parse("DisplayBackButtons",false);
		}
		else
		{
			/* check with session if login user is manager then show manager editable block */
			if($arrLeaves['manager_id'] != "" && $arrLeaves['manager_id'] != "0")
			{
				if($_SESSION["id"] == $arrLeaves['manager_id'])
				{
					if($arrLeaves['team_leader_status'] == '2' || ($arrLeaves['team_leader_status'] == '0' && $arrLeaves['delegate_status'] == '2'))
					{
						$tpl->parse("FillManagerReadable",false);
					}
					else
					{
						$tpl->parse("FillManagerEditable",false);
						$displayAction = true;
					}
				}
				else
				{
					$tpl->parse("FillManagerReadable",false);
				}
			}
			/* check with session if login user is teamleader then show teamleader editable block */
			if($arrLeaves['teamleader_id'] != "" && $arrLeaves['teamleader_id'] != "0")
			{
				if($_SESSION["id"] == $arrLeaves['teamleader_id'])
				{
					if($arrLeaves['status_manager'] == '0' && $arrLeaves['manager_delegate_status'] == '0')
					{
						$tpl->parse("FillTemaLeaderEditable",false);
						$displayAction = true;
					}
					else
					{
						$tpl->parse("FillTemaLeaderReadable",false);
					}
				}
				else
				{
					$tpl->parse("FillTemaLeaderReadable",false);
				}
			}

			/* check with session if login user is deligated manager then show deligated manager editable block */
			if($arrLeaves['deligateManagerId'] != "" && $arrLeaves['deligateManagerId'] != "0")
			{
				if($_SESSION["id"] == $arrLeaves['deligateManagerId'])
				{
					if($arrLeaves['team_leader_status'] == '2' || ($arrLeaves['team_leader_status'] == '0' && $arrLeaves['delegate_status'] == '2'))
					{
						$tpl->parse("FillManagerDelegationReadable",false);
					}
					else
					{
						$tpl->parse("FillManagerDeligationEditable",false);
						$displayAction = true;
					}
				}
				else
				{
					$tpl->parse("FillManagerDelegationReadable",false);
				}
			}
			else
			{
				$considerCurDelegate = 0;
				$checkDeligateId = $objLeaves->fnCheckDeligate($arrLeaves['manager_id']);
				if(isset($checkDeligateId) && $checkDeligateId != "")
					$considerCurDelegate = $checkDeligateId;
				
				/* If user delegated, but leave form added before delegation */
				if(in_array($arrLeaves['employee_id'],$arrAllDelegatedEmployees) && $considerCurDelegate == $_SESSION["id"])
				{
					if(!($arrLeaves['team_leader_status'] == '2' || ($arrLeaves['team_leader_status'] == '0' && $arrLeaves['delegate_status'] == '2')))
					{
						$tpl->set_var("curdeligateManagerId",$_SESSION["id"]);
						$tpl->parse("DisplayDelegatedManagerBlock",false);
					
						$tpl->parse("FillManagerDeligationEditable",false);
						$displayAction = true;
						$displayDelegate = true;
					}
				}
			}

			/* check with session if login user is deligated teamleader then show deligated teamleader editable block */
			if($arrLeaves['deligateTeamLeaderId'] != "" && $arrLeaves['deligateTeamLeaderId'] != "0")
			{
				if($_SESSION["id"] == $arrLeaves['deligateTeamLeaderId'] && $arrLeaves['team_leader_status'] != '2' && $arrLeaves['status_manager'] == '0' && $arrLeaves['manager_delegate_status'] == '0')
				{
					$tpl->parse("FillDeligationEditable",false);
					$displayAction = true;
				}
				else
				{
					$tpl->parse("FillDelegationReadable",false);
				}
			}
			else
			{
				$considerCurDelegate = 0;
				$checkDeligateId = $objLeaves->fnCheckDeligate($arrLeaves['teamleader_id']);
				if(isset($checkDeligateId) && $checkDeligateId != "")
					$considerCurDelegate = $checkDeligateId;
				
				/* If user delegated, but leave form added before delegation */
				if(in_array($arrLeaves['employee_id'],$arrAllDelegatedEmployees) && $considerCurDelegate == $_SESSION["id"] && $arrLeaves['team_leader_status'] != '2' && $arrLeaves['status_manager'] == '0' && $arrLeaves['manager_delegate_status'] == '0')
				{
					$tpl->set_var("curdeligateTeamLeaderId",$_SESSION["id"]);
					$tpl->parse("DisplayDelegatedTeamLeaderBlock",false);
					$tpl->parse("FillDeligationEditable",false);
					$displayAction = true;
					$displayDelegate = true;
				}
			}

			if($arrLeaves['ph'] == '1')
			{
				$tpl->parse('PhLabel',true);
			}
			
			$tpl->set_var("leave_taker_designation",$arrLeaves['designation']);
			
			if($displayAction)
			{
				if(isset($arrDesignation["allow_delegation"]) && trim($arrDesignation["allow_delegation"]) == "1" && isset($arrDesignation["delegation_designation"]) && trim($arrDesignation["delegation_designation"]) != "" && ($_SESSION["id"] == $arrLeaves['manager_id'] || $_SESSION["id"] == $arrLeaves['deligateManagerId'] || $displayDelegate == true))
				{
					/* Fetch all the employees as per the delegation designation */
					$arrEmployees = $objEmployee->fnGetEmployeesByDesignation(trim($arrDesignation["delegation_designation"]));

					if(count($arrEmployees) > 0)
					{
						/* Fill dropdown for delegates */
						foreach($arrEmployees as $curEmployee)
						{
							/* Do not display employee whose leave is  */
							if($curEmployee["id"] != $arrLeaves["employee_id"])
							{
								$tpl->set_var("delegated_employee_id",$curEmployee["id"]);
								$tpl->set_var("delegated_employee_name",$curEmployee["name"]);

								$tpl->parse("FillDelegateEmployees",true);
							}
						}
						$tpl->parse("FillDeligation",false);
					}
				}

				$tpl->parse("DisplayActionButtons",false);
			}
			else
			{
				$tpl->parse("DisplayBackButtons",false);
			}
		}
	}

	$tpl->set_var("FillLeaveHistory","");
	$tpl->set_var("FillNoLeaveHistory","");
	if(count($arrLeaveRecords) > 0)
	{
		foreach($arrLeaveRecords as $leavesRecord)
		{
			$tpl->set_var("empName",$leavesRecord["empName"]);
			$tpl->set_var("noofdays",$leavesRecord["noofdays"]);
			$tpl->set_var("hstart_date",$leavesRecord["hstart_date"]);
			$tpl->set_var("hend_date",$leavesRecord["hend_date"]);
			$tpl->parse("FillLeaveHistory","true");
		}
	}
	else
	{
		$tpl->parse("FillNoLeaveHistory","true");
	}

	$tpl->set_var("FillLastThreeLeaves","");
	$tpl->set_var("FillNoLastThreeLeaves","");
	if(count($lastThreeLeaves) > 0)
	{
		foreach($lastThreeLeaves as $threeLeaves)
		{
			if($threeLeaves['status_m'] == 1)
			{
				$status_manager = 'Approved';
			}
			else if($threeLeaves['status_m'] == 2)
			{
				$status_manager = 'Reject';
			}
			else
			{
				$status_manager = 'Pending';
			}

			if($threeLeaves['status_t'] == 1)
			{
				$status_tl = 'Approved';
			}
			else if($threeLeaves['status_t'] == 2)
			{
				$status_tl = 'Reject';
			}
			else
			{
				$status_tl = 'Pending';
			}
			
			$tpl->set_var("Teamleader_status",$status_tl);
			$tpl->set_var("manager_status",$status_manager);
			$tpl->set_var("number_d", $threeLeaves["number_d"]);
			$tpl->set_var("start_d", $threeLeaves["start_d"]);
			$tpl->set_var("end_d", $threeLeaves["end_d"]);
			$tpl->parse("FillLastThreeLeaves","true");
		}
	}
	else
	{
		$tpl->parse("FillNoLastThreeLeaves","true");
	}

	$tpl->pparse('main',false);
?>
