<?php 
	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('iq_round.html','main_container');

	$PageIdentifier = "IQRound";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage IQ Round");
	$breadcrumb = '<li class="active">Manage IQ Round</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.candidate_list.php');
	
	$objCandidateList = new candidate_list();

	$tpl->set_var("AllView",'');
	$tpl->set_var("PendingView",'');
	
	if(isset($_REQUEST['info']) && $_REQUEST['info'] == 'all')
	{
		$arrCandidateList = $objCandidateList->fnGetAllCandidateListIQ();
		$tpl->parse("PendingView",false);
	}
	else
	{
		$arrCandidateList = $objCandidateList->fnGetAllPendingCandidateListIQ();
		$tpl->parse("AllView",false);
	}

	$message = "";
	$messageClass = "";
	$tpl->set_var("DisplayMessageBlock","");
	
	if(isset($_REQUEST['info']))
	{

		switch($_REQUEST["info"])
		{
			case 'succ':
				$messageClass = "alert-success";
				$message = "Candidate List inserted successfully.";
				break;
			case 'update':
				$messageClass = "alert-success";
				$message = "Candidate List updated successfully.";
				break;
			case 'delete':
				$messageClass = "alert-success";
				$message = "No records found.";
				break;
			case 'exist':
				$messageClass = "alert-success";
				$message = "Candidate List deleted successfully.";
				break;
		}
		
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);
			
			$tpl->parse("DisplayMessageBlock",false);	
		}
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] = 'delete')
	{
		$delteCandidateList = $objCandidateList->fnDeleteCandidateList($_POST);
		if($delteCandidateList)
		{
			header("Location: candidate_list.php?info=delete");
		}
		else
		{
			
		}
	}
	
	$tpl->set_var("FillRctSheetValues","");
	//echo '<pre>'; print_r($arrCandidateList);
	foreach($arrCandidateList as $arrCandidateListvalue)
	{
		
		if($arrCandidateListvalue['recommend_hr_round'] == '1')
		{
			$tpl->set_var("recommend","yes");
		}
		else if($arrCandidateListvalue['recommend_hr_round'] == '2')
		{
			$tpl->set_var("recommend","no");
		}
		else if($arrCandidateListvalue['recommend_hr_round'] == '3')
		{
			$tpl->set_var("recommend","Hold");
		}
		else
		{
			$tpl->set_var("recommend","Pending");
		}
		$tpl->SetAllValues($arrCandidateListvalue);
		$tpl->parse("FillRctSheetValues",true);
	}

	$tpl->pparse('main',false);
?>
