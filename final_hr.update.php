<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('final_hr.update.html','main_container');

	$PageIdentifier = "FinalHr";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Update Candidate HR round Info");
	$breadcrumb = '<li><a href="candidate_list.php">Manage Applicant Evaluation Form</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Edit Candidate Hr Round Info</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.candidate_list.php');
	include_once('includes/class.employee.php');
	include_once('includes/class.emp_test.php');
	include_once('includes/class.shifts.php');
	
	$objCandidateList = new candidate_list();
	$objEmployee = new employee();
	$objEmpTest = new emp_test();
	$objShifts = new shifts();


	$getAllHrs = $objCandidateList->fnGetTotalInterviewer();

	//$getAllTeamLeader = $objEmployee->fnGetAllTeamleads();
	
	//$getAllShifts = $objShifts->fnGetAllShiftTimes();

	$tpl->set_var("DisplayBackButtons","");
	
	if(isset($_REQUEST['id']) && $_REQUEST['id'] != '')
	{
		$arrCandidateDetail = $objCandidateList->fnGetCandidateById($_REQUEST['id']);
		//echo '<pre>'; print_r($arrCandidateDetail);
		
		$getAllTeamLeader = $objEmployee->fnGetReportingHeadByDes($arrCandidateDetail['des_id']);

		if($arrCandidateDetail['recommend_om'] != '')
		{
			if($arrCandidateDetail['recommend_om'] == '0')
			{
				$tpl->set_var('om_reasign','0');
				$tpl->set_var('om_asigned','0');
			}
			else
			{
				$tpl->set_var('om_reasign','1');
				$tpl->set_var('om_asigned',$arrCandidateDetail['recommend_om']);
			}
		}
		
		if($arrCandidateDetail['om_status'] != '' && $arrCandidateDetail['om_reasign_flag'] == '1' )
		{
			$tpl->set_var('om_reasign_round','1');
		}
		else if($arrCandidateDetail['om_status'] != '' && $arrCandidateDetail['om_reasign_flag'] == '2' )
		{
			$tpl->set_var('om_reasign_round','2');
			$tpl->set_var('om_asigned','');
		}
		else
		{
			$tpl->set_var('om_reasign_round','');
		}
		$hrDetails = $objEmployee->fnGetEmployeeDetailById($arrCandidateDetail['interviewer']);
		$arrOpsCommentsById = $objCandidateList->fnGetOpsAllCommentsById($_REQUEST['id']);$arrOperationManagerComments = $objCandidateList->fnGetOpsCommentsById($_REQUEST['id'],$arrCandidateDetail['recommend_om']);
	}
	
	if(isset($_REQUEST['id']))
	{
		$tpl->set_var('candidate_id',"$_REQUEST[id]");
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] =='update')
	{
		if($_POST['om_reasign'] == '1')
		{
			$_POST['final_hr_status'] = '0';
			$_POST['final_hr_remarks'] = '';
			$_POST['salary_offered'] = '';
			$_POST['exp_date_of_joining'] = '';
			$_POST['teamleader_by_manager'] = '';
			$_POST['shift_timning_by_manager'] = '';
			$_POST['om_reasign_flag'] = $_POST['om_reasign']; 
			$status = '2';
		}
		else
		{
			$status = $_POST['final_hr_status'];
		}
		if($_POST['final_hr_status'] != '4')
		{
			$_POST['salary_offered'] = '';
			$_POST['exp_date_of_joining'] = '';
			$_POST['teamleader_by_manager'] = '';
			$_POST['shift_timning_by_manager'] = '';
		}
		//echo '<pre>'; print_r($_POST);  echo '<br>status'.$status;
		$updateCandidate = $objCandidateList->fnUpdateFinalHrStatus($_POST);

		//echo '<br>status:'.$status; die;
		$updateStatus = $objCandidateList->fnUpdateStatusUpdate($status,$_POST['id']);
		//echo 'here'.$updateCandidate; die;
		if($updateStatus)
		{
			//echo 'here'.$updateStatus; die;
			header("Location: final_hr.php?info=update");
			exit;
		}
	}
	
	
	if($arrCandidateDetail)
	{
		/*if($arrCandidateDetail['isjoin'] == '1' )
		{
			$tpl->set_var('DisplayActionButtons',"");
			$tpl->parse('DisplayBackButtons',false);
		}*/
		//echo '<pre>'; print_r($arrCandidateDetail);
		$getReferencename = $objCandidateList->fnGetEmployeeNameById($arrCandidateDetail['reference_trans']);

		if(isset($getReferencename) && $getReferencename != '')
		{
			$tpl->set_var('refer',$getReferencename);
		}
		else
		{
			$getActualReferenceName = $objCandidateList->fnGetReferenceSourceName($arrCandidateDetail['rctsource']);
			$tpl->set_var('refer',$getActualReferenceName);
		}
		$tpl->SetAllValues($arrCandidateDetail);
	}

	if($hrDetails)
	{
		$tpl->set_var('interviewerName',"$hrDetails[name]");
	}

	

	$arrEmpTestDetails = $objEmpTest->fnGetAllRootEmpTest($_REQUEST['id']);
	//echo '<pre>'; print_r($arrEmpTestDetails);
	foreach($arrEmpTestDetails as $arrEmpTest)
	{
		//echo '<pre>'; print_r($arrEmpTest);
		$tpl->set_var("test_main_id",$arrEmpTest['test_id']);
		//$getMarks = $objEmpTest->fnGetTestMarks($_REQUEST['id'],$arrEmpTest['id']);
		$arrEmpTestMarksDetails = $objEmpTest->fnGetTestSubCategory($arrEmpTest['test_id']);
		//print_r($arrEmpTestMarksDetails);
		$tpl->set_var("FillSubCategoryBlock","");
		$tpl->set_var("FillCriteriaDropdown","");
		if(count($arrEmpTest) > 0)
		{
			foreach($arrEmpTestMarksDetails as $marks)
			{
				$tpl->set_var("title_sub",$marks['test_title']);
				$tpl->set_var("FillCriteriaDropdown","");
				if(count($marks) > 0)
				{
					//echo 'hello<pre>'; print_r($marks);
					$tpl->set_var("test_ids",$marks['test_id']);
					$arrTestCriteria = $objEmpTest->fnGetTestCriteria($marks['test_id']);
					//echo '<pre>'; print_r($arrTestCriteria);
					$arrGetTestMarks  = $objEmpTest->fnGetTestMarksTitleByChildParent($_REQUEST['id'],$arrEmpTest['test_id'],$marks['test_id']);
					
					
					$tpl->set_var('getMarks',$arrGetTestMarks);
				}
		
				$tpl->parse('FillSubCategoryBlock',true);
				//echo '<pre>'; print_r($marks);
			}
		}
		//echo '<br>'.$getMarks.'<br>';
		//echo '<pre>';print_r($arrEmp);
		$tpl->set_var('exam_title',$arrEmpTest['test_title']);
		/*$tpl->set_var('cand_recommend_om',$arrEmp['recommend_om']);
		$tpl->set_var('cand_recommend_om_round',$arrEmp['cand_recommend_om_round']);
		if(isset($arrEmp))
		{
			$tpl->set_var('cand_recommend_om',$arrEmp['recommend_om']);
			$tpl->set_var('cand_recommend_om_round',$arrEmp['cand_recommend_om_round']);
		}*/
		$tpl->parse('FillMarksBlock',true);
	}

	$tpl->set_var("FillOpsComments","");	
	
	if(count($arrOpsCommentsById) > 0 )
	{
		foreach($arrOpsCommentsById as $ArrOpsComment)
		{
			//echo '<pre>'; print_r($arrCandidateDetail);
			//echo '<pre>'; print_r($ArrOpsComment);
			if($ArrOpsComment['ops_status'] == '1')
			{
				$tpl->set_var("operations_status","Selected");
			}
			else if($ArrOpsComment['ops_status'] == '2')
			{
				$tpl->set_var("operations_status","Rejected");
			}
			else if($ArrOpsComment['ops_status'] == '3')
			{
				$tpl->set_var("operations_status","Hold");
			}
			else if($ArrOpsComment['ops_status'] == '4')
			{
				$tpl->set_var("operations_status","Declined");
			}
			else
			{
				$tpl->set_var("operations_status","Pending");
			}
			
			if($ArrOpsComment['exp_join'] == '00-00-0000')
			{
				$tpl->set_var("operations_exp_d_j","");
			}
			else
			{
				$tpl->set_var("operations_exp_d_j",$ArrOpsComment['exp_join']);
			}

			if($ArrOpsComment['om_date'] != '0000-00-00 00:00:00')
			{
				$tpl->set_var('operation_manager_date',$ArrOpsComment['om_decision_date']);
			}
			else
			{
				$tpl->set_var('operation_manager_date','');
			}
			
			if($arrCandidateDetail['final_hr_remark_date'] != '0000-00-00 00:00:00')
			{
				$tpl->set_var('final_hr_sal_off',$arrCandidateDetail['final_hr_salary_offered']);
			}
			else
			{
				$tpl->set_var('final_hr_sal_off',$ArrOpsComment['sal_offer']);
			}
			
			if($arrCandidateDetail['final_hr_remark_date'] != '0000-00-00 00:00:00')
			{
				$tpl->set_var('final_hr_exp_remark',$arrCandidateDetail['final_hr_remarks']);
			}
			else if($arrCandidateDetail['om_status'] == '1'  )
			{
				$tpl->set_var('final_hr_exp_remark','DOJ '.$ArrOpsComment['exp_date_of_joining']);
			}

			if($arrCandidateDetail['final_hr_remark_date'] != '0000-00-00 00:00:00')
			{
				$tpl->set_var('final_hr_exp_date',$arrCandidateDetail['final_hr_date']);
			}
			else if($arrCandidateDetail['om_status'] == '1'  )
			{
				$tpl->set_var('final_hr_exp_date',$ArrOpsComment['expect_join']);
			}
			
			if($arrCandidateDetail['final_hr_remark_date'] != '0000-00-00 00:00:00')
			{
				$tpl->set_var('final_hr_tl_by_man',$arrCandidateDetail['final_hr_teamleader_by_manager']);
			}
			else
			{
				$tpl->set_var('final_hr_tl_by_man',$arrOperationManagerComments['teamleader_by_manager']);
			}
			//echo $arrCandidateDetail['final_hr_shift_timning_by_manager'];
			if($arrCandidateDetail['final_hr_remark_date'] != '0000-00-00 00:00:00')
			{
				$tpl->set_var('final_hr_shift_time',$arrCandidateDetail['final_hr_shift_timning_by_manager']);
			}
			else
			{
				$tpl->set_var('final_hr_shift_time',$arrOperationManagerComments['shift_timning_by_manager']);
			}
			
			$tpl->SetAllValues($ArrOpsComment);
			$tpl->parse("FillOpsComments",true);
		}
	}

	if(count($getAllTeamLeader) > 0)
	{
		foreach($getAllTeamLeader as $teamLeaders)
		{
			$tpl->set_var('team_lead_id',$teamLeaders['id']);
			$tpl->set_var('team_lead_name',$teamLeaders['name']);
			$tpl->parse('FillEmployeeTeamleader',true);
		}
	}

	if(count($arrOpsCommentsById) > 0)
	{
		$getAllShifts = array();
		//echo '<pre>'; echo $arrOpsCommentsById[0]['shift_time'];
		if(isset($arrOpsCommentsById['0']['teamleader_by_manager']))
		{
			$getAllShifts = $objShifts->fnAllowedShiftsByHeadId($arrOpsCommentsById['0']['teamleader_by_manager']);
		}
		
		$tpl->set_var('FillShifts','');
		if(count($getAllShifts) > 0)
		{
			foreach($getAllShifts as $shifts)
			{
				$shifts = $objShifts->fnGetShiftById($shifts);
				$tpl->set_var('shift_id',$shifts['id']);
				$tpl->set_var('shift_name',$shifts['title']);
				$tpl->parse('FillShifts',true);
						
			}
		}
	}
	//echo '<pre>'; print_r($arrOperationManagerComments);
	if(isset($arrOperationManagerComments['teamleader_by_manager']) && ($arrOperationManagerComments['teamleader_by_manager'] != '' && $arrOperationManagerComments['teamleader_by_manager'] != '0'))
	{
		
		$getAllShifts = $objShifts->fnAllowedShiftsByHeadId($arrOperationManagerComments['teamleader_by_manager']);
		//echo '<pre>'; print_r($getAllShifts);
		
		$tpl->set_var('FillShifts','');
		if(count($getAllShifts) > 0)
		{
			foreach($getAllShifts as $shifts)
			{
				
				$shifts = $objShifts->fnGetShiftById($shifts);
				//echo '<pre>'; print_r($shifts);
				$tpl->set_var('shift_id',$shifts['id']);
				$tpl->set_var('shift_name',$shifts['title']);
				$tpl->parse('FillShifts',true);
						
			}
		}		
	}

	if(count($arrOperationManagerComments) > 0 )
	{
		$tpl->set_var("cur_mana_status",$arrOperationManagerComments['ops_status']);
		$tpl->set_var("cur_mana_comments",$arrOperationManagerComments['ops_comments']);
		$tpl->set_var("cur_mana_sal_off",$arrOperationManagerComments['salary_offered']);
		$tpl->set_var("cur_exp_joining",$arrOperationManagerComments['exp_date_of_joining']);
		$tpl->set_var("cur_team_leader",$arrOperationManagerComments['teamleader_by_manager']);
		if(isset($arrOperationManagerComments['shift_timning_by_manage']))
		{
			$tpl->set_var("cur_mana_shift",$arrOperationManagerComments['shift_timning_by_manage']);
		}
	}

	$arrAllManagers = $objEmployee->fnGetAllManagersForInterview();

	if(count($arrAllManagers) > 0)
	{
		foreach($arrAllManagers as $managers)
		{
			//echo '<pre>';print_r($managers);
			$tpl->set_var('man_id',$managers['id']);
			$tpl->set_var('man_name',$managers['name']);
			$tpl->parse('FillEmployeeManager',true);
		}
	}
	
	$tpl->pparse('main',false);
?>
