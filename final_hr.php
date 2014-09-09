<?php 
	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('final_hr.html','main_container');

	$PageIdentifier = "FinalHr";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Final HR Evalutions");
	$breadcrumb = '<li class="active">Final HR Evalutions</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.candidate_list.php');
	
	$objCandidateList = new candidate_list();
	$arrCandidateList = $objCandidateList->fnGetAllCandidateListByManagerStatus();
	//echo '<pre>'; print_r($arrCandidateList);
	$message = "";
	$messageClass = "";
	$tpl->set_var("DisplayMessageBlock","");

	if(isset($_REQUEST['info']) && $_REQUEST['info'] == 'all')
	{
		$arrCandidateList = $objCandidateList->fnGetAllCandidateListByManagerStatus();
		$tpl->parse("PendingView",false);
	}
	else
	{
		$arrCandidateList = $objCandidateList->fnGetPendingCandidateListByManagerStatus();
		$tpl->parse("AllView",false);
	}
	
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
	
	
	
	$tpl->set_var("FillRctSheetValues","");
	foreach($arrCandidateList as $arrCandidateListvalue)
	{
		$tpl->SetAllValues($arrCandidateListvalue);
		$tpl->parse("FillRctSheetValues",true);
	}

	$tpl->pparse('main',false);
?>
