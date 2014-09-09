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

	$objLeaves = new leave();
	$objEmployee = new employee();

	$arrLeaves = $objLeaves->fnGetLeaveDetailsById($_REQUEST['id']);
	
	$reportingHead = $objEmployee->fnGetReportingHead($arrLeaves['employee_id']);
	$lastThreeLeaves = $objLeaves->fnGetLastThreeLeaves($arrLeaves['employee_id'],$arrLeaves['leave_id']);

	/* Get All Team Leader for Team Leader delegation dropdown */
	$getAllTeamLeaders = $objEmployee->fnGetAllTeamLeaders($arrLeaves['id'],$arrLeaves['designation'],$_SESSION['designation']);

	/* Get All managers for Manager delegation dropdown */
	$getAllManagers = $objEmployee->fnGetAllManagers($arrLeaves['id'],$arrLeaves['designation'],$_SESSION['designation']);
	
	$tpl->set_var("FillDeligation","");
	$tpl->set_var("FillTeamLeaders","");
	$tpl->set_var("session_designation",$_SESSION['designation']);
	$tpl->set_var("session_id",$_SESSION['id']);
	$tpl->set_var("session_delegateTeamleader",$arrLeaves['deligateTeamLeaderId']);
	$tpl->set_var("session_delegateManager",$arrLeaves['deligateManagerId']);
	$tpl->set_var("actual_Teamleader",$arrLeaves['teamleader_id']);
	$tpl->set_var("actual_Designation",$arrLeaves['designation']);
	

	//if(($_SESSION['designation'] == 6 || $_SESSION['designation'] == 8 || $_SESSION['designation'] == 17) &&  ($arrLeaves['designation'] == 7 || $arrLeaves['designation'] == 13 || $arrLeaves['designation'] == 6))

	/* Filling values in the team leader delegation dropdown */
	if(($_SESSION['designation'] == 8 && $arrLeaves['designation'] == 6) || $_SESSION['designation'] == 6 && $arrLeaves['designation'] == 7 || $arrLeaves['designation'] == 13)
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
	}

	/* Filling values in the manager delegation dropdown */
	
	if($_SESSION['designation'] == '17' && ($arrLeaves['designation'] == '6' || $arrLeaves['designation'] == '18' || $arrLeaves['designation'] == '19'))
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
	}
		
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
		$arrEmployee = $objEmployee->fnGetAllemployees($_SESSION['id']);
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
	$tpl->set_var("FillDeligation","");
	$tpl->set_var("FillManagerDeligation","");
	$tpl->set_var("FillManagerReadable","");
	

	if($_SESSION['usertype'] == 'admin')
	{
		$tpl->parse("DisplayBackButtons",false);
	}
	else
	{
		$tpl->parse("DisplayActionButtons",false);
	}

	//$tpl->set_var("FillDeligationEditable",'');
	
	
	
	if(isset($arrLeaves))
	{
		$tpl->SetAllValues($arrLeaves);
		if($arrLeaves['status_manager'] == '0')
		{
			$tpl->set_var("status_manager1","pending");
			$tpl->set_var("comment_manager1","pending");
			$tpl->set_var("comment_manager_readable",$arrLeaves['comment_manager']);
			$tpl->set_var("status_manager_readable",$arrLeaves['status_manager']);
		}
		else if($arrLeaves['status_manager'] == '1')
		{
			$tpl->set_var("status_manager1",'Approved');
			$tpl->set_var("comment_manager1",$arrLeaves['comment_manager']);
			$tpl->set_var("comment_manager_readable",$arrLeaves['comment_manager']);
			$tpl->set_var("status_manager_readable",$arrLeaves['status_manager']);
		}
		else if($arrLeaves['status_manager'] == '2')
		{
			$tpl->set_var("status_manager1","Reject");
			$tpl->set_var("comment_manager1",$arrLeaves['comment_manager']);
			$tpl->set_var("comment_manager_readable",$arrLeaves['comment_manager']);
			$tpl->set_var("status_manager_readable",$arrLeaves['status_manager']);
		}

		

		if($arrLeaves['leave_status'] == '0')
		{
			$tpl->set_var("status1","pending");
			$tpl->set_var("comment1","pending");
		}
		else if($arrLeaves['leave_status'] == '1')
		{
			$tpl->set_var("status1",'Approved');
			$tpl->set_var("comment1",$arrLeaves['comment']);
		}
		else if($arrLeaves['leave_status'] == '2')
		{
			$tpl->set_var("status1","Reject");
			$tpl->set_var("comment1",$arrLeaves['comment']);
		}

		if($arrLeaves['delegate_status'] == '0')
		{
			$tpl->set_var("status2","pending");
			$tpl->set_var("comment2","pending");
		}
		else if($arrLeaves['delegate_status'] == '1')
		{
			$tpl->set_var("status2",'Approved');
			$tpl->set_var("comment2",$arrLeaves['delegate_comment']);
		}
		else if($arrLeaves['delegate_status'] == '2')
		{
			$tpl->set_var("status2","Reject");
			$tpl->set_var("comment2",$arrLeaves['delegate_comment']);
		}

		if($arrLeaves['manager_delegate_status'] == '0')
		{
			$tpl->set_var("status3","pending");
			$tpl->set_var("comment3",$arrLeaves['manager_delegate_comment']);
		}
		else if($arrLeaves['manager_delegate_status'] == '1')
		{
			$tpl->set_var("status3","Approved");
			$tpl->set_var("comment3",$arrLeaves['manager_delegate_comment']);
		}
		else if($arrLeaves['manager_delegate_status'] == '2')
		{
			$tpl->set_var("status3","Reject");
			$tpl->set_var("comment3",$arrLeaves['manager_delegate_comment']);
		}

		if($_SESSION['id'] == '1')
		{
			$tpl->set_var("FillTemaLeaderEditable","");
			$tpl->set_var("FillManagerEditable","");
			if($arrLeaves['designation'] == '7' || $arrLeaves['designation'] == '13')
			{
				$tpl->set_var("FillTemaLeaderReadable","");
			}
		}

		/* Conditioning when admin login */
		if($_SESSION['designation'] == '0')
		{
			/*  Leave applyer is manager */
			if($arrLeaves['designation'] == '6' || $arrLeaves['designation'] == '18' || $arrLeaves['designation'] == '19')
			{
				$tpl->parse("FillManagerReadable",true);
			}
			/*  Leave applyer is Team Leader */
			else if($arrLeaves['designation'] == '7' || $arrLeaves['designation'] == '13')
			{
				$tpl->parse("FillManagerReadable",true);
			}
			/*  Leave applyer is Agent */
				else if($arrLeaves['designation'] == '5' || $arrLeaves['designation'] == '9' || $arrLeaves['designation'] == '10' || $arrLeaves['designation'] == '11' || $arrLeaves['designation'] == '12' ||$arrLeaves['designation'] == '14' || $arrLeaves['designation'] == '15' || $arrLeaves['designation'] == '16' || $arrLeaves['designation'] == '20' || $arrLeaves['designation'] == '21' || $arrLeaves['designation'] == '22' || $arrLeaves['designation'] == '23' || $arrLeaves['designation'] == '24' ||$arrLeaves['designation'] == '25' || $arrLeaves['designation'] == '26' || $arrLeaves['designation'] == '27' || $arrLeaves['designation'] == '28')
			{
				$tpl->parse("FillManagerReadable",true);
				$tpl->parse("FillTemaLeaderReadable",true);
				/* Deligation team leader and manager both available */
				if($arrLeaves['deligateTeamLeaderId'] != '0' && $arrLeaves['deligateManagerId'] != '0')
				{
					$tpl->parse("FillManagerDelegationReadable",true);
					$tpl->parse("FillDelegationReadable",true);
				}
				/* Deligation team leader not available but delegated manager available */
				else if($arrLeaves['deligateTeamLeaderId'] == '0' && $arrLeaves['deligateManagerId'] != '0')
				{
					$tpl->parse("FillManagerDelegationReadable",true);
				}
				/* Deligation team leader available but delegated manager not available */
				else if($arrLeaves['deligateTeamLeaderId'] != '0' && $arrLeaves['deligateManagerId'] == '0')
				{
					$tpl->parse("FillDelegationReadable",true);
				}
				
			}
		}
		
		/* Conditioning when ceo login */
		if($_SESSION['designation'] == '17')
		{
			/*  Leave applyer is manager */
			if($arrLeaves['designation'] == '6' || $arrLeaves['designation'] == '18' || $arrLeaves['designation'] == '19')
			{
				$tpl->parse("FillManagerEditable",true);
				$tpl->parse("FillManagerDeligation",true);
			}
			/*  Leave applyer is Team Leader */
			else if($arrLeaves['designation'] == '7' || $arrLeaves['designation'] == '13')
			{
				$tpl->parse("FillManagerReadable",true);
			}
			/*  Leave applyer is Agent */
			else if($arrLeaves['designation'] == '5' || $arrLeaves['designation'] == '9' || $arrLeaves['designation'] == '10' || $arrLeaves['designation'] == '11' || $arrLeaves['designation'] == '12' ||$arrLeaves['designation'] == '14' || $arrLeaves['designation'] == '15' || $arrLeaves['designation'] == '16' || $arrLeaves['designation'] == '20' || $arrLeaves['designation'] == '21' || $arrLeaves['designation'] == '22' || $arrLeaves['designation'] == '23' || $arrLeaves['designation'] == '24' ||$arrLeaves['designation'] == '25' || $arrLeaves['designation'] == '26' || $arrLeaves['designation'] == '27' || $arrLeaves['designation'] == '28')
			{
				$tpl->parse("FillManagerReadable",true);
				$tpl->parse("FillTemaLeaderReadable",true);
				
				/* Deligation team leader and manager both available */
				if($arrLeaves['deligateTeamLeaderId'] != '0' && $arrLeaves['deligateManagerId'] != '0')
				{
					$tpl->parse("FillManagerDelegationReadable",true);
					$tpl->parse("FillDelegationReadable",true);
				}
				/* Deligation team leader not available but delegated manager available */
				else if($arrLeaves['deligateTeamLeaderId'] == '0' && $arrLeaves['deligateManagerId'] != '0')
				{
					$tpl->parse("FillManagerDelegationReadable",true);
				}
				/* Deligation team leader available but delegated manager not available */
				else if($arrLeaves['deligateTeamLeaderId'] != '0' && $arrLeaves['deligateManagerId'] == '0')
				{
					$tpl->parse("FillDelegationReadable",true);
				}
			}
		}
		
		/* Conditioning when Manager login */
		if($_SESSION['designation'] == '6' || $_SESSION['designation'] == '18' || $_SESSION['designation'] == '19')
		{
			/*  Leave applyer is Team Leader */
			if($arrLeaves['designation'] == '7' || $arrLeaves['designation'] == '13')
			{
				/* Deligation manager available */
				if($arrLeaves['deligateManagerId'] != '0')
				{
					/* Leave applyer teamleader and login user is same */
					if($_SESSION['id'] == $arrLeaves['teamleader_id'])
					{
						$tpl->parse("FillManagerEditable",true);
						$tpl->parse("FillManagerDelegationReadable",true);
					}
					/* Leave applyer teamleader and login user is not same */
					else
					{
						$tpl->parse("FillManagerDeligationEditable",true);
						$tpl->parse("FillManagerReadable",true);
					}
				}
				/* Deligation manager not available */
				else
				{
					$tpl->parse("FillManagerEditable",true);
				}
				
				$tpl->parse("FillDeligation",true);
			}
			/*  Leave applyer is Agent */
			else if($arrLeaves['designation'] == '5' || $arrLeaves['designation'] == '9' || $arrLeaves['designation'] == '10' || $arrLeaves['designation'] == '11' || $arrLeaves['designation'] == '12' ||$arrLeaves['designation'] == '14' || $arrLeaves['designation'] == '15' || $arrLeaves['designation'] == '16' || $arrLeaves['designation'] == '20' || $arrLeaves['designation'] == '21' || $arrLeaves['designation'] == '22' || $arrLeaves['designation'] == '23' || $arrLeaves['designation'] == '24' ||$arrLeaves['designation'] == '25' || $arrLeaves['designation'] == '26' || $arrLeaves['designation'] == '27' || $arrLeaves['designation'] == '28')
			{
				$tpl->parse("FillTemaLeaderReadable",true);
				
				/* Deligation team leader not available but Deligation manager  available */
				if($arrLeaves['deligateTeamLeaderId'] == '0' && $arrLeaves['deligateManagerId'] != '0')
				{
					/* Leave Applyer manager id and login in user id same */
					if($_SESSION['id'] == $arrLeaves['manager_id'])
					{
						$tpl->parse("FillManagerEditable",true);
						$tpl->parse("FillManagerDelegationReadable",true);
					}
					/* Leave Applyer manager id and login in user id not same */
					else
					{
						$tpl->parse("FillManagerDeligationEditable",true);
						$tpl->parse("FillManagerReadable",true);
					}
				}
				/* Deligation team leader available but Deligation manager not available */
				else if($arrLeaves['deligateTeamLeaderId'] != '0' && $arrLeaves['deligateManagerId'] == '0')
				{
					$tpl->parse("FillDelegationReadable",true);
					$tpl->parse("FillManagerEditable",true);
				}
				/* Deligation team leader and Deligation manager both available */
				else if($arrLeaves['deligateTeamLeaderId'] != '0' && $arrLeaves['deligateManagerId'] != '0')
				{
					$tpl->parse("FillDelegationReadable",true);
					/* Leave Applyer manager id and login in user id same */
					if($_SESSION['id'] == $arrLeaves['manager_id'])
					{
						$tpl->parse("FillManagerEditable",true);
						$tpl->parse("FillManagerDelegationReadable",true);
					}
					/* Leave Applyer manager id and login in user id not same */
					else
					{
						$tpl->parse("FillManagerDeligationEditable",true);
						$tpl->parse("FillManagerReadable",true);
					}
				}
				/* Deligation team leader and Deligation manager both not available */
				else if($arrLeaves['deligateTeamLeaderId'] == '0' && $arrLeaves['deligateManagerId'] == '0')
				{
					$tpl->parse("FillManagerEditable",true);
				}
				/* If teamleader reject the leave manager not give his view */
				if($arrLeaves['team_leader_status'] == '2')
				{
					$tpl->set_var("FillManagerEditable","");
					$tpl->parse("FillManagerReadable",true);
					$tpl->set_var("DisplayActionButtons","");
					$tpl->parse("DisplayBackButtons",false);
				}
			}
		}
		/*  Leave applyer is Team Leader */
		if($_SESSION['designation'] == '7' || $_SESSION['designation'] == '13')
		{
			$tpl->parse("FillManagerReadable",true);
			/* Deligation team leader not available and Deligation manager available */
			if($arrLeaves['deligateTeamLeaderId'] == '0' && $arrLeaves['deligateManagerId'] != '0')
			{
				$tpl->parse("FillTemaLeaderEditable",true);
				$tpl->parse("FillManagerDelegationReadable",true);
			}
			/* Deligation team leader available and Deligation manager not available */
			else if($arrLeaves['deligateTeamLeaderId'] != '0' && $arrLeaves['deligateManagerId'] == '0')
			{
				/* Leave Applyer Deligated Team Leader id and login in user id same */
				if($_SESSION['id'] == $arrLeaves['deligateTeamLeaderId'])
				{
					$tpl->parse("FillDeligationEditable",true);
					$tpl->parse("FillTemaLeaderReadable",true);
				}
				/* Leave Applyer Deligated Team Leader id and login in user id not same */
				else
				{
					$tpl->parse("FillTemaLeaderEditable",true);
					$tpl->parse("FillDelegationReadable",true);
				}
			}
			/* Deligation team leader and Deligation manager both available */
			else if($arrLeaves['deligateTeamLeaderId'] != '0' && $arrLeaves['deligateManagerId'] != '0')
			{
				$tpl->parse("FillManagerDelegationReadable",true);
				/* Leave Applyer Deligated Team Leader id and login in user id same */
				if($_SESSION['id'] == $arrLeaves['deligateTeamLeaderId'])
				{
					$tpl->parse("FillDeligationEditable",true);
					$tpl->parse("FillTemaLeaderReadable",true);
				}
				/* Leave Applyer Deligated Team Leader id and login in user id not same */
				else
				{
					$tpl->parse("FillTemaLeaderEditable",true);
					$tpl->parse("FillDelegationReadable",true);
				}
			}
			/* Deligation team leader and Deligation manager both not available */
			else if($arrLeaves['deligateTeamLeaderId'] == '0' && $arrLeaves['deligateManagerId'] == '0')
			{
				$tpl->parse("FillTemaLeaderEditable",true);
			}
			
		}
		
		
		
		if($arrLeaves['ph'] == '1')
		{
			$tpl->parse('PhLabel',true);
		}
		else
		{
			$tpl->set_var('PhLabel','');
		}
		
		$tpl->set_var("leave_taker_designation",$arrLeaves['designation']);
	}


	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] == 'update')
	{
		/* Update Leave status */
		$arrLeave = $objLeaves->fnUpdateLeaveStatus($_POST);
		if($arrLeave)
		{
			header("Location: leave_request.php?info=update");
		}
		else
		{

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
			$tpl->setAllValues($threeLeaves);
			$tpl->parse("FillLastThreeLeaves","true");
		}
	}
	else
	{
		$tpl->parse("FillNoLastThreeLeaves","true");
	}


	$tpl->pparse('main',false);
?>
