<?php
	include('common.php');
	$tpl = new Template($app_path);
	//print_r($_SESSION);
	$tpl->load_file('template.html','main');
	$tpl->load_file('halfleave_request.view.html','main_container');

	$PageIdentifier = "HalfLeaveRequest";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Half Leave Request");
	$breadcrumb = '<li><a href="employee.php">Manage Leave Request</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Half Leave Request</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.leave.php');
	include_once('includes/class.employee.php');
	include_once('includes/class.attendance.php');
	include_once('includes/class.designation.php');

	$objLeaves = new leave();
	$objEmployee = new employee();
	$objAttendance = new attendance();
	$objDesignation = new designations();

	$arrLeaves = $objLeaves->fnGetHalfLeaveDetailsById($_REQUEST['id']);

	//echo '<pre>'; print_r($arrLeaves);

	$tpl->set_var('requested_leave_id',$_REQUEST['id']);
	$tpl->set_var('d_s',$arrLeaves['delegate_status']);
	$tpl->set_var('d_c',$arrLeaves['delegate_comment']);
	$tpl->set_var('d_m_s',$arrLeaves['manager_delegate_status']);
	$tpl->set_var('d_m_c',$arrLeaves['manager_delegate_comment']);
	$tpl->set_var('manager_status_id',$arrLeaves['manager_status_id']);
	$tpl->set_var('leave_status_id',$arrLeaves['leave_status']);
	$tpl->set_var('tl_com',$arrLeaves['team_leader_comment']);

	$reportingHead = $objEmployee->fnGetReportingHeadById($arrLeaves['employee_id']);
	$lastThreeLeaves = $objLeaves->fnGetLastThreeLeaves($arrLeaves['employee_id'],$arrLeaves['leave_id']);
	/*$getAllTeamLeaders = $objEmployee->fnGetAllTeamLeaders($arrLeaves['id'],$arrLeaves['designation'],$_SESSION['designation'],$arrLeaves['starting_date'],$arrLeaves['starting_date']);*/

	$tpl->set_var("session_designation",$_SESSION['designation']);
	
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

	$arrLeaveRecords = $objLeaves->fnGetLeaveDetailByHead($arrLeaves['leave_id'],$ids,$arrLeaves['starting_date'],$arrLeaves['starting_date']);
	
	$tpl->set_var("DisplayActionButtons","");
	$tpl->set_var("DisplayBackButtons","");
	
	/* Blank all blocks initially */
	$tpl->set_var("FillTemaLeaderReadable","");
	$tpl->set_var("FillTemaLeaderEditable","");
	$tpl->set_var("FillManagerReadable","");
	$tpl->set_var("FillManagerEditable","");
	$tpl->set_var("FillDelegationReadable","");
	$tpl->set_var("FillDeligationEditable","");
	$tpl->set_var("FillManagerDelegationReadable","");
	$tpl->set_var("FillManagerDeligationEditable","");
	$tpl->set_var("DisplayDelegatedTeamLeaderBlock","");
	$tpl->set_var("DisplayDelegatedManagerBlock","");
	$tpl->set_var("DisplayAdminAddedBlock","");
	$tpl->set_var('PhLabel','');
	
	if(isset($arrLeaves))
	{
		/* Set leave balance */
		$pendingLeaves = $objAttendance->fnGetLastLeaveBalance($arrLeaves['employee_id']);

		$unpaid_leaves = $objAttendance->fnGetUserLeavesWithoutPayByMonthAndYear($arrLeaves['id'], Date('m'), Date('Y'));

		//$pendingLeaves = $pendingLeaves - $unpaid_leaves;

		$pendingLeaveBalance = 0;
		if($pendingLeaves > 0)
			$pendingLeaveBalance = $pendingLeaves;
		$tpl->set_var("pending_leave",$pendingLeaveBalance);

		$arrApprovalStatus = array("0"=>"Pending", "1"=>"Approved", "2"=>"Rejected");
		$arrHalfDayFor = array("1"=>"First half", "2"=>"Second half");

		/* Approval status and comment by manager */
		$tpl->set_var("status_manager1",$arrApprovalStatus[$arrLeaves['status_manager']]);
		$tpl->set_var("comment_manager1",$arrLeaves['comment_manager']);
		$tpl->set_var("comment_manager_readable",$arrLeaves['comment_manager']);


		/* Approval status and comment by team leader */
		$tpl->set_var("status1",$arrApprovalStatus[$arrLeaves['leave_status']]);
		$tpl->set_var("comment1",$arrLeaves['comment']);

		/* Approval status and comment by delegated team leader */
		$tpl->set_var("status2",$arrApprovalStatus[$arrLeaves['delegate_status']]);
		$tpl->set_var("comment2",$arrLeaves['delegate_comment']);

		/* Approval status and comment by delegated manager */
		$tpl->set_var("status3",$arrApprovalStatus[$arrLeaves['manager_delegate_status']]);
		$tpl->set_var("comment3",$arrLeaves['manager_delegate_comment']);

		$tpl->set_var("leave_isactive",$arrLeaves['isactive']);

		$tpl->set_var("halfdayfor_text",$arrHalfDayFor[$arrLeaves['halfdayfor']]);

		$displayAction = false;

		$tpl->SetAllValues($arrLeaves);
		
		$tpl->set_var("leave_taker_designation",$arrLeaves['designation']);
		//echo $arrLeaves['leave_status'];  die;
		//$tpl->set_var("reporting_head_one_status",$arrLeaves['leave_status']);
		
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
				$tpl->parse('PhLabel',false);
			}
			
			if($displayAction)
			{
				$tpl->parse("DisplayActionButtons",false);
			}
			else
			{
				$tpl->parse("DisplayBackButtons",false);
			}
		}
	}


	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] == 'update')
	{
		$LeaveInfo = $objLeaves->fnHalfLeaveInfoById($_POST["hdnid"]);

		if((isset($_POST["status_manager"]) && $_POST["status_manager"] != $LeaveInfo["status_manager"]) || (isset($_POST["status"]) && $_POST["status"] != $LeaveInfo["status"]) || (isset($_POST["manager_delegate_status"]) && $_POST["manager_delegate_status"] != $LeaveInfo["manager_delegate_status"]) || (isset($_POST["delegate_status"]) && $_POST["delegate_status"] != $LeaveInfo["delegate_status"]))
		{
			$arrLeave = $objLeaves->fnUpdateHalfLeaveStatus($_POST);
			if($arrLeave)
			{
				header("Location: halfleave_request.php?info=update");
			}
		}
		else
		{
			header("Location: halfleave_request.php?info=update");
		}
	}

	$tpl->set_var("FillLeaveHistory","");
	$tpl->set_var("FillNoLeaveHistory","");
	if(count($arrLeaveRecords) > 0)
	{
		foreach($arrLeaveRecords as $leavesRecord)
		{
			//print_r($leavesRecord);
			$tpl->setAllValues($leavesRecord);
			$tpl->parse("FillLeaveHistory","false");
		}
	}
	else
	{
		$tpl->parse("FillNoLeaveHistory","false");
	}

	$tpl->set_var("FillLastThreeLeaves","");
	$tpl->set_var("FillNoLastThreeLeaves","");
	if(count($lastThreeLeaves) > 0)
	{
		$arrApprovalStatus = array("0"=>"Pending", "1"=>"Approved", "2"=>"Rejected");
		
		foreach($lastThreeLeaves as $threeLeaves)
		{
			$status_manager = $arrApprovalStatus[$threeLeaves['status_m']];
			$status_tl = $arrApprovalStatus[$threeLeaves['status_t']];

			$tpl->set_var("Teamleader_status",$status_tl);
			$tpl->set_var("manager_status",$status_manager);
			$tpl->setAllValues($threeLeaves);
			$tpl->parse("FillLastThreeLeaves","false");
		}
	}
	else
	{
		$tpl->parse("FillNoLastThreeLeaves","false");
	}

	$tpl->pparse('main',false);
?>
