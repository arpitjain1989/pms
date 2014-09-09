<?php
	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('om_round.html','main_container');

	$PageIdentifier = "OmRound";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Operation Manager Evaluation Reviews");
	$breadcrumb = '<li class="active">Operation Manager Evaluation Reviews</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.candidate_list.php');
	
	$objCandidateList = new candidate_list();
	$arrCandidateList = $objCandidateList->fnGetAllCandidateListByStatus();
	//echo '<pre>'; print_r($arrCandidateList);
	$message = "";
	$messageClass = "";
	$tpl->set_var("DisplayMessageBlock","");
	
	if(isset($_REQUEST['info']))
	{

		switch($_REQUEST["info"])
		{
			case 'succ':
				$messageClass = "alert-success";
				$message = "Your comments updated successfully.";
				break;
			case 'update':
				$messageClass = "alert-success";
				$message = "Your comments updated successfully.";
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
