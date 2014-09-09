<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('test_round.add.html','main_container');

	$PageIdentifier = "TestRound";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Edit Test Round Details");
	$breadcrumb = '<li><a href="test_round.php">Manage Test Round Details</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add / Edit Test Round Details</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.candidate_list.php');
	include_once('includes/class.emp_test.php');
	include_once('includes/class.employee.php');
	
	$objCandidateList = new candidate_list();
	$objEmpTest = new emp_test();
	$objEmployee = new employee();

	$tpl->set_var("FillCancelButton","");
	
	if(isset($_REQUEST['id']))
	{
		$tpl->set_var('EmpTestid',"$_REQUEST[id]");
	}

	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] =='update')
	{
		//echo '<pre>'; print_r($_POST);die;
		$updateEmpTest = $objEmpTest->fnUpdateEmpTestDetailsById($_POST);

		$status_om = $_POST['recommend_om_round'];
		if($status_om == '1')
		{
			$status = '2';
		}
		else if($status_om == '3')
		{
			$status = '1';
		}
		else
		{
			$status = '6';
		}

		$updateStatus = $objCandidateList->fnUpdateStatusUpdate($status,$_POST['id']);
		
		

		if($updateEmpTest)
		{
			header("Location: test_round.php?info=update");
			exit;
		}
	}

	$tpl->set_var("FillMarksBlock","");
	$tpl->set_var("FillEmployeeManager","");
	if(isset($_REQUEST['action']) && $_REQUEST['action']='update')
	{
		$arrEmp = $objCandidateList->fnGetCandidateById($_REQUEST['id']);
		$arrAllManagers = $objEmployee->fnGetAllManagersForInterview();
		//echo '<pre>'; print_r($arrEmp);

		if(isset($arrEmp['mobile']) && $arrEmp['mobile'] != '')
		{
			$tpl->set_var('emp_mobile',$arrEmp['mobile']);
		}
		
		if($_REQUEST['id'] != '')
		{
			$checkFinalRoundConduct = $objCandidateList->fnCheckFinalRoundConduct($_REQUEST['id']);
			if($checkFinalRoundConduct == '')
			{
				$tpl->set_var("FillSubmitButton","");
				$tpl->parse('FillCancelButton',false);
			}
		}
		if(count($arrEmp) > 0 )
		{
			$tpl->set_var('emp_name',$arrEmp['name']);
			$tpl->set_var('hr_remark_for_test',$arrEmp['test_hr_remarks']);
			$tpl->set_var('recommond_for_om_round',$arrEmp['cand_recommend_om_round']);
		}

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
						$arrGetTestMarks  = $objEmpTest->fnGetTestMarksByChildParent($_REQUEST['id'],$arrEmpTest['test_id'],$marks['test_id']);
						
						if(count($arrTestCriteria) > 0)
						{
							$tpl->set_var("FillCriteriaOptionBox","");
							foreach($arrTestCriteria as $criteria)
							{
								//echo '<pre>'; print_r($criteria);
								$tpl->set_var("title_crite",$criteria['criteria_title']);
								$tpl->set_var("title_desc",$criteria['title_criteria']);
								$tpl->set_var("criet_id",$criteria['criteria_id']);
								$tpl->parse('FillCriteriaOptionBox',true);
							}
						}
						else
						{
							$tpl->set_var("FillCriteriaOptionBox",'');
						}
						$tpl->set_var('getMarks',$arrGetTestMarks);
					}
			
					$tpl->parse('FillSubCategoryBlock',true);
					//echo '<pre>'; print_r($marks);
				}
			}
			//echo '<br>'.$getMarks.'<br>';
			//echo '<pre>';print_r($arrEmp);
			$tpl->set_var('exam_title',$arrEmpTest['test_title']);
			$tpl->set_var('exam_iq_score',$arrEmp['iq_score']);
			$tpl->set_var('cand_recommend_om',$arrEmp['recommend_om']);
			$tpl->set_var('cand_recommend_om_round',$arrEmp['cand_recommend_om_round']);
			
			$tpl->parse('FillMarksBlock',true);
		}
	}

	
	
	$tpl->pparse('main',false);
?>
