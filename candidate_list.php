<?php 
	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('candidate_list.html','main_container');

	$PageIdentifier = "HrRound";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Applicant HR Round");
	$breadcrumb = '<li class="active">Manage Applicant HR Round</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.candidate_list.php');
	
	$objCandidateList = new candidate_list();

	$tpl->set_var("AllView",'');
	$tpl->set_var("PendingView",'');
	
	if(isset($_REQUEST['info']) && $_REQUEST['info'] == 'all')
	{
		$arrCandidateList = $objCandidateList->fnGetAllCandidateList();
		$tpl->parse("PendingView",false);
	}
	else
	{
		$arrCandidateList = $objCandidateList->fnGetAllPendingCandidateList();
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
	foreach($arrCandidateList as $arrCandidateListvalue)
	{
		if($arrCandidateListvalue['recommend_test'] == '1')
		{
			$tpl->set_var("recommend","Yes");
		}
		else if($arrCandidateListvalue['recommend_test'] == '2')
		{
			$tpl->set_var("recommend","No");
		}
		else if($arrCandidateListvalue['recommend_test'] == '3')
		{
			$tpl->set_var("recommend","Hold");
		}
		else if($arrCandidateListvalue['recommend_test'] == '4')
		{
			$tpl->set_var("recommend","N/A");
		}
		else if($arrCandidateListvalue['recommend_test'] == '5')
		{
			$tpl->set_var("recommend","Future Prospect");
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
