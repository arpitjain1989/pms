<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('joinees_list.html','main_container');

	$PageIdentifier = "Joinees";
	include_once('userrights.php');
	
	$tpl->set_var("mainheading","Manage Hired");
	$breadcrumb = '<li class="active">Manage Hired</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.joinees.php');
	include_once('includes/class.candidate_list.php');

	$objCandidateList = new candidate_list();

	$tpl->set_var("DisplayMessageBlock","");
	
	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'success':
				$messageClass = "alert-success";
				$message = "Joinees added successfully";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Joinees already added. Cannot add again.";
				break;
		}

		if(isset($message) && $message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	$objJoinees = new joinees();
	$arrJoinees = $objJoinees->fnGetAllJoinersForJoinings();

	//echo '<pre>'; print_r($arrJoinees);
	$tpl->set_var("FillJoineesList","");
	if(count($arrJoinees) >0)
	{
		foreach($arrJoinees as $Joiners)
		{
			$tpl->set_var("empid",$Joiners["cand_id"]);
			$tpl->set_var("emptitle",$Joiners["cand_name"]);
			//echo 'final_hr_status'.$arrJoinees['final_hr_status'];
			if(isset($arrJoinees['final_hr_status']))
			{
				if($arrJoinees['final_hr_status'] == '0' || $arrJoinees['final_hr_status'] == '')
				{
					$getExpJoiningByOm = $objCandidateList->fnGetOpsComments1($Joiners['cand_id'],$Joiners['rec_om']);
					$tpl->set_var("exp_joining_date",$getExpJoiningByOm);
				}
				else
				{
					if($Joiners['hr_exp_joining_date'] != '')
					{
						$tpl->set_var("exp_joining_date",$Joiners['hr_exp_joining_date']);
					}
					else
					{
						$ExpDateOfJoiningByOperations = $objJoinees->fnGetExpDateOfJoinByManager($Joiners['rec_om'],$Joiners["cand_id"]);
						$tpl->set_var("exp_joining_date",$ExpDateOfJoiningByOperations);
					}
				}
			}
			else
			{
				$getExpJoiningByOm = $objCandidateList->fnGetOpsComments1($Joiners['cand_id'],$Joiners['rec_om']);
				$tpl->set_var("exp_joining_date",$getExpJoiningByOm);
			}
			
			if($Joiners['teamLeader_name_hr'] != '')
			{
				$tpl->set_var("teamLeaderName",$Joiners["teamLeader_name_hr"]);
			}
			else
			{
				$teamleaderByOperations = $objJoinees->fnGetTemLeaderByManager($Joiners['rec_om'],$Joiners["cand_id"]);
				$tpl->set_var("teamLeaderName",$teamleaderByOperations);
			}
			
			if($Joiners['shift_title'] != '')
			{
				$tpl->set_var("recommendedShift",$Joiners["shift_title"]);
			}
			else
			{
				$ShiftByOperations = $objJoinees->fnGetShiftByManager($Joiners['rec_om'],$Joiners["cand_id"]);
				$tpl->set_var("recommendedShift",$ShiftByOperations);
			}
			$tpl->parse("FillJoineesList",true);
		}
	}

	$tpl->pparse('main',false);

?>
