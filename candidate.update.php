<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('candidate.update.html','main_container');

	$PageIdentifier = "HrRound";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Update Candidate HR round Info");
	$breadcrumb = '<li><a href="candidate_list.php">Manage Applicant Evaluation Form</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Edit Candidate Hr Round Info</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	$tpl->set_var("FillCancelButton","");
	
	include_once('includes/class.candidate_list.php');
	
	$objCandidateList = new candidate_list();


	$getAllHrs = $objCandidateList->fnGetTotalInterviewer();
	
	if(isset($_REQUEST['id']) && $_REQUEST['id'] != '')
	{
		$arrCandidateDetail = $objCandidateList->fnGetCandidateById($_REQUEST['id']);
		$checkFutureRoundConduct = $objCandidateList->fnCheckFutureRoundConduct($_REQUEST['id']);
		//echo '<pre>'; print_r($arrCandidateDetail);
		if($checkFutureRoundConduct == '')
		{
			$tpl->set_var("FillSubmitButton","");
			$tpl->parse('FillCancelButton',false);
		}
	}
	//echo '<pre>'; print_r($arrCandidateDetail);
	
	if(isset($_REQUEST['id']))
	{
		$tpl->set_var('candidate_id',"$_REQUEST[id]");
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] =='update')
	{
		//$date = Date('Y-m-d H:i:s');
		//echo '<pre>'; print_r($_POST); die;
		$updateCandidate = $objCandidateList->fnUpdateCandidate($_POST);
		if($_POST['recommend_test'] == '1')
		{
			$status = '7';
		}
		else if($_POST['recommend_test'] == '3')
		{
			$status = '5';
		}
		else if($_POST['recommend_test'] == '4')
		{
			$status = '1';
		}
		else if($_POST['recommend_test'] == '5')
		{
			$status = '3';
		}
		else
		{
			$status = '6';
		}
		$updateStatus = $objCandidateList->fnUpdateStatusUpdate($status,$_POST['id']);
		if($updateCandidate)
		{
			header("Location: candidate_list.php?info=update");
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

	$tpl->set_var('FillInterviewer','');
	if(count($getAllHrs) > 0 )
	{	
		foreach($getAllHrs as $arrHrs)
		{
			$tpl->SetAllValues($arrHrs);
			$tpl->parse('FillInterviewer',true);
		}
	}
	
	
	
	$tpl->pparse('main',false);
?>
