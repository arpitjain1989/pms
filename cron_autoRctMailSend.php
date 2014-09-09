<?php
	include('common.php');
	$tpl = new Template($app_path);

	//Initialize output buffer
	ob_start();

	//$tpl->load_file('template.html','main');
	$tpl->load_file('cron_autoRctMailSend.html','main');
	
	$curDate = Date('Y-m-d');
	$curYear = Date('Y');
	$curMonth = Date('m');

	include_once('includes/class.candidate_list.php');
	
	$objCandidateList = new candidate_list();

	$rct_division = $objCandidateList->fnGetAllRctDivision();

	$getAllRctReciepients = $objCandidateList->fnGetAllEmployeeForRctMail();
	
	$reciever = '';
	if(count($getAllRctReciepients))
	{
		$pre= '';
		foreach($getAllRctReciepients as $recievers)
		{
			$reciever .= $pre . '' .$recievers["email"]. '';
			$pre = ' ,';
		}
	}
	
	$tpl->set_var("FillRctDivision",'');
	$tpl->set_var("division_title",'');
	$tpl->set_var("divisionCount",'');
	$tpl->set_var("rctShortlistedCount",'');
	$tpl->set_var("rctHiredCount",'');
	$tpl->set_var("rctRejectedCount",'');
	$tpl->set_var("rctDeclinedCount",'');
	$tpl->set_var("rctHoldCount",'');
	$tpl->set_var("rctTestCount",'');
	$tpl->set_var("rctFRTCount",'');
	$tpl->set_var("rctFutureProspectsCount",'');
	$_POST['final_hr_status'] = '';
	
	foreach($rct_division as $rctdivision)
	{
		//echo '<pre>'; print_r($rctdivision);
		
		$recordFromRctDivision = $objCandidateList->fnGetAllRctDivisionRecordsCount($rctdivision['id'],$curMonth,$curYear,$_POST['final_hr_status']);
		$getShortlistedCandidates = $objCandidateList->fnGetAllShortlistedCandidatesCount($rctdivision['id'],$curMonth,$curYear,$_POST['final_hr_status']);
		$getHiredCandidates = $objCandidateList->fnGetAllHiredCandidatesCount($rctdivision['id'],$curMonth,$curYear,$_POST['final_hr_status']);
		$getRejectedCandidates = $objCandidateList->fnGetAllRejectedCandidatesCount($rctdivision['id'],$curMonth,$curYear,$_POST['final_hr_status']);
		$getDeclinedCandidates = $objCandidateList->fnGetAllDeclinedCandidatesCount($rctdivision['id'],$curMonth,$curYear,$_POST['final_hr_status']);
		$getHoldCandidates = $objCandidateList->fnGetAllHoldCandidatesCount($rctdivision['id'],$curMonth,$curYear,$_POST['final_hr_status']);
		$getTestCandidates = $objCandidateList->fnGetAllTestCandidatesCount($rctdivision['id'],$curMonth,$curYear,$_POST['final_hr_status']);
		$getFRTCandidates = $objCandidateList->fnGetAllFRTCandidatesCount($rctdivision['id'],$curMonth,$curYear,$_POST['final_hr_status']);
		$getFutureProspectsCandidates = $objCandidateList->fnGetAllFutureCandidatesCount($rctdivision['id'],$curMonth,$curYear,$_POST['final_hr_status']);
		
		$tpl->set_var("division_title",$rctdivision['title']);
		$tpl->set_var("divisionCount",$recordFromRctDivision);
		$tpl->set_var("rctShortlistedCount",$getShortlistedCandidates);
		$tpl->set_var("rctHiredCount",$getHiredCandidates);
		$tpl->set_var("rctRejectedCount",$getRejectedCandidates);
		$tpl->set_var("rctDeclinedCount",$getDeclinedCandidates);
		$tpl->set_var("rctHoldCount",$getHoldCandidates);
		$tpl->set_var("rctTestCount",$getTestCandidates);
		$tpl->set_var("rctFRTCount",$getFRTCandidates);
		$tpl->set_var("rctFutureProspectsCount",$getFutureProspectsCandidates);
		
		$tpl->parse("FillRctDivision",true);
		$tpl->parse("FillRctDivision1",true);
		$tpl->parse("FillRctDivision2",true);
		$tpl->parse("FillRctDivision3",true);
		$tpl->parse("FillRctDivision4",true);
		$tpl->parse("FillRctDivision5",true);
		$tpl->parse("FillRctDivision6",true);
		$tpl->parse("FillRctDivision7",true);
		$tpl->parse("FillRctDivision8",true);
		$tpl->parse("FillRctDivision9",true);
	}
	$tpl->pparse('main',false);

	// Get contents from output buffer
	$mailContent = ob_get_contents();

	ob_end_clean();

	$date = date('d/m/Y');

	$Subject = 'RCT Sheet';
	//$contacts = "gaurang@transformsolution.net,mukesh.tiwari@transformsolution.net,parvez.shiliwala@transformsolution.net,himanshu.barai@transformsolution.net,asfaq@transformsolution.com,adil.kodia@transformsolution.net,mahesh@transformsolution.net";

	
	$content = "Dear All, <br /><br />";
	$content .= "RCT Sheet of ".$date."<br /><br />";
	$content .= $mailContent;
	$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;

	//echo $reciever;
	if($reciever !='')
	{
		sendmail($reciever,$Subject,$content);
	}
	
	// Remove the contents from the output buffer
	

	//echo $mailContent;
	
?>
