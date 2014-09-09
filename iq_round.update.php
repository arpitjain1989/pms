<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('iq_round.update.html','main_container');

	$PageIdentifier = "IQRound";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Update Candidate IQ Info");
	$breadcrumb = '<li><a href="iq_round.php">Manage Candidate IQ Form</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Edit Candidate IQ Form</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	$tpl->set_var("FillCancelButton","");
	
	include_once('includes/class.candidate_list.php');
	
	$objCandidateList = new candidate_list();


	$getAllHrs = $objCandidateList->fnGetTotalInterviewer();
	
	if(isset($_REQUEST['id']) && $_REQUEST['id'] != '')
	{
		$arrCandidateDetail = $objCandidateList->fnGetCandidateById($_REQUEST['id']);
		$checkFutureRoundConduct = $objCandidateList->fnCheckFutureRoundConductIQ($_REQUEST['id']);
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
		//echo '<pre>'; print_r($_POST);die;
		$updateCandidate = $objCandidateList->fnUpdateCandidateIQ($_POST);
		if($_POST['recommend_hr_round'] == '1')
		{
			$status = '8';
		}
		else if($_POST['recommend_hr_round'] == '1')
		{
			$status = '8';
		}
		else
		{
			$status = '6';
		}
		$updateStatus = $objCandidateList->fnUpdateStatusUpdate($status,$_POST['id']);
		if($updateCandidate)
		{
			header("Location: iq_round.php?info=update");
			exit;
		}
	}
	
	//echo '<pre>'; print_r($arrCandidateDetail);
	if($arrCandidateDetail)
	{
		if($arrCandidateDetail['iq_score'] == '0.00')
		{
			$tpl->set_var('IQScore','0');
		}
		else
		{
			$tpl->set_var('IQScore',$arrCandidateDetail['iq_score']);
		}
		if($arrCandidateDetail['iq_score'] >= '0' && $arrCandidateDetail['iq_score'] < '40')
		{
			$tpl->set_var('IQStatus','Poor');
		}
		else if($arrCandidateDetail['iq_score'] >= '40' && $arrCandidateDetail['iq_score'] <= '55')
		{
			$tpl->set_var('IQStatus','Fair');
		}
		else if($arrCandidateDetail['iq_score'] > '55' && $arrCandidateDetail['iq_score'] <= '70')
		{
			$tpl->set_var('IQStatus','Satisfactory');
		}
		else
		{
			$tpl->set_var('IQStatus','Good');
		}
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

	/*$tpl->set_var('FillInterviewer','');
	if(count($getAllHrs) > 0 )
	{	
		foreach($getAllHrs as $arrHrs)
		{
			$tpl->SetAllValues($arrHrs);
			$tpl->parse('FillInterviewer',true);
		}
	}*/
	
	
	
	$tpl->pparse('main',false);
?>
