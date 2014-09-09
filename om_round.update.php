<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('om_round.update.html','main_container');

	$PageIdentifier = "OmRound";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Update Candidate HR round Info");
	$breadcrumb = '<li><a href="candidate_list.php">Manage Applicant Evaluation Form</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Edit Candidate Hr Round Info</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.candidate_list.php');
	include_once('includes/class.employee.php');
	include_once('includes/class.emp_test.php');
	include_once('includes/class.shifts.php');
	include_once('includes/class.salary_offered.php');
	
	$objCandidateList = new candidate_list();
	$objEmployee = new employee();
	$objEmpTest = new emp_test();
	$objShifts = new shifts();
	$objSalaryOffered = new salary_offered();

	//$getAllTeamLeader = $objEmployee->fnGetAllTeamleads();

	$tpl->set_var("FillCancelButton","");

	//$getAllShifts = $objShifts->fnGetAllShiftTimes();

	//echo '<pre>'; print_r($getAllShifts);
	
	if(isset($_REQUEST['id']) && $_REQUEST['id'] != '')
	{
		
		$arrCandidateDetail = $objCandidateList->fnGetCandidateById($_REQUEST['id']);
		

		$getAllTeamLeader = $objEmployee->fnGetReportingHeadByDes($arrCandidateDetail['des_id']);

		//echo '<pre>'; print_r($arrReportingHeads);

		$checkFinalRoundConduct = $objCandidateList->fnCheckFinalRoundConduct($_REQUEST['id']);
		//echo '<pre>'; print_r($checkFinalRoundConduct); die;
		/*if($checkFinalRoundConduct == '')
		{
			$tpl->set_var("FillSubmitButton","");
			$tpl->parse('FillCancelButton',false);
		}*/

		$getSalaryOffered = $objSalaryOffered->fnGetSalaryOfferedByDesId($arrCandidateDetail['designation_id']);
		if(count($getSalaryOffered) > 0)
		{
			$tpl->set_var("highest",$getSalaryOffered['highest_amount']);
			$tpl->set_var("lowest",$getSalaryOffered['lowest_amount']);
			$tpl->parse('FillExpectecSalary',false);
		}

		$hrDetails = $objEmployee->fnGetEmployeeDetailById($arrCandidateDetail['interviewer']);

		$arrOperationManagerComments = $objCandidateList->fnGetOpsCommentsById($_REQUEST['id'],$_SESSION['id']);
		//echo '<pre>'; print_r($arrOperationManagerComments);
		if(isset($arrOperationManagerComments['ops_comments']))
		{
			$tpl->set_var("operation_comments",$arrOperationManagerComments['ops_comments']);
		}
		

		$arrGetAllOperationManagersComments = $objCandidateList->fnGetAllOperationsComments($_REQUEST['id']);
	}
	//echo '<pre>'; print_r($arrCandidateDetail);
	
	if(isset($_REQUEST['id']))
	{
		$tpl->set_var('candidate_id',"$_REQUEST[id]");
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] =='update')
	{
		//echo '<pre>'; print_r($_POST); die;
		if($_POST['status'] != '1')
		{
			$_POST['shift_timning_by_manager'] = '';
			$_POST['salary_offered'] = '';
			$_POST['exp_date_of_joining'] = '';
			$_POST['teamleader_by_manager'] = '';
		}
		
		$updateOpsComments = $objCandidateList->fnUpdateOPMComments($_POST,$_SESSION['id']);

		if($_POST['status'] == '1')
		{
			$status = '4';
		}
		else if($_POST['status'] == '2')
		{
			$status = '6';
		}
		else if($_POST['status'] == '3')
		{
			$status = '5';
		}
		else if($_POST['status'] == '4')
		{
			$status = '1';
		}
		$updateStatus = $objCandidateList->fnUpdateStatusUpdate($status,$_POST['id']);
		
		if($updateOpsComments)
		{
			header("Location: om_round.php?info=update");
			exit;
		}
	}
	
	
	if($arrCandidateDetail)
	{
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

	//echo '<pre>'; print_r($arrOperationManagerComments);
	if(count($arrOperationManagerComments) > 0 )
	{	
		$tpl->SetAllValues($arrOperationManagerComments);
	}

	$tpl->set_var('FillManagerCommnetsBlock','');
	if(count($arrGetAllOperationManagersComments) > 0 )
	{
		//echo '<pre>'; print_r($arrGetAllOperationManagersComments);
		foreach($arrGetAllOperationManagersComments as $ManagersComments)
		{
			print_r($ManagersComments);
			if($ManagersComments['ops_status'] == '1')
			{
				$tpl->set_var('oper_stat','Yes');
			}
			else if($ManagersComments['ops_status'] == '2')
			{
				$tpl->set_var('oper_stat','Yes');
			}
			else if($ManagersComments['ops_status'] == '4')
			{
				$tpl->set_var('oper_stat','Declined');
			}
			if(isset($ManagersComments['ops_comments']))
			{
				$tpl->set_var('oper_comments',$ManagersComments['ops_comments']);
			}
			if(isset($ManagersComments['ops_name']))
			{
				$tpl->set_var('oper_man_name',$ManagersComments['ops_name']);
			}

			$tpl->set_var('oper_comments',$ManagersComments['ops_comments']);
			$tpl->set_var('oper_man_name',$ManagersComments['ops_name']);
			
			$tpl->parse('FillManagerCommnetsBlock',true);
		}
	}

	$arrEmpTestDetails = $objEmpTest->fnGetAllEmpTest($_REQUEST['id']);

	//echo '<pre>'; print_r($arrEmpTestDetails);
	/*$tpl->set_var('FillTestEvaluationBlock','');	
	if(count($arrEmpTestDetails) > 0)
	{
		foreach($arrEmpTestDetails as $arrEmpTest)
		{
		//echo '<pre>'; print_r($arrEmpTest);
		$getMarks = $objEmpTest->fnGetTestMarks($_REQUEST['id'],$arrEmpTest['id']);
		//echo '<br>'.$getMarks.'<br>';
		$tpl->set_var('exam_title',$arrEmpTest['title']);
		$tpl->set_var('exam_id',$arrEmpTest['id']);
		$tpl->set_var('emp_marks',$arrEmpTest['marks']);
		$tpl->set_var('emp_get_marks',$getMarks);
		$tpl->set_var('cand_recommend_om_round',$arrEmp['cand_recommend_om_round']);
		
		$tpl->parse('FillTestEvaluationBlock',true);
		}
	}*/
	
	$arrEmpTestDetails = $objEmpTest->fnGetAllRootEmpTest($_REQUEST['id']);
	//echo '<pre>'; print_r($arrEmpTestDetails);
	if(count($arrEmpTestDetails) > 0)
	{
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
		}
		//echo '<br>'.$getMarks.'<br>';
		//echo '<pre>';print_r($arrEmp);
		
		$tpl->set_var('exam_title',$arrEmpTest['test_title']);
		if(isset($arrEmp['recommend_om']))
		{
			$tpl->set_var('cand_recommend_om',$arrEmp['recommend_om']);
		}
		if(isset($arrEmp['cand_recommend_om_round']))
		{
			$tpl->set_var('cand_recommend_om_round',$arrEmp['cand_recommend_om_round']);
		}
		
		$tpl->parse('FillMarksBlock',true);
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
				$tpl->set_var('shift_id',$shifts['id']);
				$tpl->set_var('shift_name',$shifts['title']);
				$tpl->parse('FillShifts',true);
			}
		}
				
	}

	
	
		
	$tpl->pparse('main',false);
?>
