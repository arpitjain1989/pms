<?php

	include('common.php');

	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('joinings.html','main_container');

	$PageIdentifier = "Joinees";
	include_once('userrights.php');

	include_once('includes/class.candidate_list.php');
	
	$objCandidateList = new candidate_list();

	$tpl->set_var("mainheading","Joinees");
	$breadcrumb = '<li class="active">Joinees</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	$tpl->set_var("DisplayJoinings","");
	$tpl->set_var("DisplayNoJoinings","");

	if(isset($_POST["hdnaction"]) && trim($_POST["hdnaction"]) == "addOpenings")
	{
		//print_r($_POST);die;
		$addJoiner = $objCandidateList->fnGetCandidateAndSaveInEmployee($_POST);
		if($addJoiner)
		{
			header("Location: joinings.php?info=succ");
			exit;
		}
		else
		{
			header("Location: joinings.php?info=err");
			exit;
		}
	}

	$AllJoiners = $objCandidateList->fnGetAllJoinersForJoinings();
	//echo '<pre>'; print_r($AllJoiners); die;
	//$currentOpenings = $objDesignation->fnGetCurrentDesignations();
	//echo '<pre>'; print_r($currentOpenings);
	
	
	if(count($AllJoiners) > 0)
	{
		foreach($AllJoiners as $Joiners)
		{
			//echo '<pre>'; print_r($Joiners);
			$tpl->set_var("empid",$Joiners["cand_id"]);
			$tpl->set_var("emptitle",$Joiners["cand_name"]);
			if($Joiners['hr_exp_joining_date'] != '')
			{
				$tpl->set_var("exp_joining_date",$Joiners['hr_exp_joining_date']);
			}
			else
			{
				$ExpDateOfJoiningByOperations = $objCandidateList->fnGetExpDateOfJoinByManager($Joiners['rec_om'],$Joiners["cand_id"]);
				$tpl->set_var("exp_joining_date",$ExpDateOfJoiningByOperations);
			}
			if($Joiners['teamLeader_name_hr'] != '')
			{
				$tpl->set_var("teamLeaderName",$Joiners["teamLeader_name_hr"]);
			}
			else
			{
				$teamleaderByOperations = $objCandidateList->fnGetTemLeaderByManager($Joiners['rec_om'],$Joiners["cand_id"]);
				$tpl->set_var("teamLeaderName",$teamleaderByOperations);
			}
			if($Joiners['shift_title'] != '')
			{
				$tpl->set_var("recommendedShift",$Joiners["shift_title"]);
			}
			else
			{
				$ShiftByOperations = $objCandidateList->fnGetShiftByManager($Joiners['rec_om'],$Joiners["cand_id"]);
				$tpl->set_var("recommendedShift",$ShiftByOperations);
			}
			
			$tpl->parse("FillDisplayJoinings",true);
		}
	}
	else
	{
		$tpl->set_var("FillJoinings","");
		$tpl->parse("DisplayNoJoinings",false);
	}
	
	
	$tpl->pparse('main',false);
?>
