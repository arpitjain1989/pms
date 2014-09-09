<?php 
	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('test_round.html','main_container');

	$PageIdentifier = "TestRound";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Test Round Marks");
	$breadcrumb = '<li class="active">Manage Test Round Marks</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.candidate_list.php');
	
	$objCandidateList = new candidate_list();

	$tpl->set_var("AllView",'');
	$tpl->set_var("PendingView",'');

	if(isset($_REQUEST['info']) && $_REQUEST['info'] == 'all')
	{
		$arrEmpTest = $objCandidateList->fnGetAllEmpForTestRound();
		$tpl->parse("PendingView",false);
	}
	else
	{
		$arrEmpTest = $objCandidateList->fnGetAllEmpForTestRoundPending();
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
				$message = "Test type inserted successfully.";
				break;
			case 'update':
				$messageClass = "alert-success";
				$message = "Test type updated successfully.";
				break;
			case 'delete':
				$messageClass = "alert-success";
				$message = "No records found.";
				break;
			case 'exist':
				$messageClass = "alert-success";
				$message = "Test type deleted successfully.";
				break;
		}
		
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);
			$tpl->parse("DisplayMessageBlock",false);	
		}
	}
	
	
	$tpl->set_var("FillEmpTestValues","");
	if(count($arrEmpTest) > 0 )
	{
		foreach($arrEmpTest as $arrEmpTestvalue)
		{
			//echo '<pre>'; print_r($arrEmpTestvalue);
			if($arrEmpTestvalue['recommend_om_round'] == '0' )
			{
				$tpl->set_var('final_test_status','n/a');
			}
			else
			{
				$tpl->set_var('final_test_status','Y');
			}
			if($arrEmpTestvalue['recommend_om_round'] == '0' )
			{
				$tpl->set_var('om_round_status','Pending');
			}
			else if($arrEmpTestvalue['recommend_om_round'] == '1' )
			{
				$tpl->set_var('om_round_status','Yes');
			}
			else if($arrEmpTestvalue['recommend_om_round'] == '3' )
			{
				$tpl->set_var('om_round_status','Declined');
			}
			else
			{
				$tpl->set_var('om_round_status','No');
			}
			$tpl->SetAllValues($arrEmpTestvalue);
			$tpl->parse("FillEmpTestValues",true);
		}
	}
	$tpl->pparse('main',false);
?>
