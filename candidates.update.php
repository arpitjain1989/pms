<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('candidates.update.html','main_container');

	$PageIdentifier = "Candidates";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Update candidates Information");
	$breadcrumb = '<li><a href="candidates.php">Manage Candidate Info</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Edit Candidate Info</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.candidate_list.php');
	include_once('includes/class.user_registration.php');
	include_once('includes/class.employee.php');
	
	$objCandidateList = new candidate_list();
	$objUserRegistration = new user_registration();
	$objEmployee = new employee();

	
	$getAllDivision = $objUserRegistration->fnGetAllDivisions();
	$getAllDesignations = $objUserRegistration->fnGetAllDesignations();
	$getAllRctSource = $objUserRegistration->fnGetAllRctSource();
	$getAllEmployees = $objEmployee->fnGetAllEmployeeForReference();

	if(isset($_REQUEST['id']) && $_REQUEST['id'] != '')
	{
		$arrCandidateDetail = $objCandidateList->fnGetCandidateById($_REQUEST['id']);
	}
	
	//echo '<pre>'; print_r($arrCandidateDetail);
	
	if(isset($_REQUEST['id']))
	{
		$tpl->set_var('candidate_id',"$_REQUEST[id]");
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] =='update')
	{
		
 		$updateCandidate = $objCandidateList->fnUpdateCandidatesById($_POST,$_FILES);
		if($updateCandidate)
		{
			header("Location: candidates.php?info=update");
			exit;
		}
	}
	
	
	if($arrCandidateDetail)
	{
		//echo '<pre>'; print_r($arrCandidateDetail);
		$tpl->SetAllValues($arrCandidateDetail);
	}
	
	$tpl->set_var("FillAllDivision",'');
	if(count($getAllDivision) > 0 )
	{
		foreach($getAllDivision as $AllDivision) 
		{
			$tpl->set_var('rct_div_id',$AllDivision['dev_id']);
			$tpl->set_var('rct_div_title',$AllDivision['dev_title']);
			$tpl->parse('FillAllDivision',true);
		}
	}

	
	$tpl->set_var("FillRCTSourceValues",'');
	if(count($getAllRctSource) > 0 )
	{
		foreach($getAllRctSource as $AllRctSource) 
		{
			$tpl->setAllValues($AllRctSource);
			$tpl->parse('FillRCTSourceValues',true);
		}
	}

	$tpl->set_var("FillAllDesignation",'');
	if(count($getAllDesignations) > 0 )
	{
		foreach($getAllDesignations as $AllDesignation) 
		{
			$tpl->setAllValues($AllDesignation);
			$tpl->parse('FillAllDesignation',true);
		}
	}

	$tpl->set_var("FillEmployeeReference",'');
	if(count($getAllEmployees) > 0)
	{
		foreach($getAllEmployees as $employees)
		{
			$tpl->setAllValues($employees);
			$tpl->parse('FillEmployeeReference',true);
		}
	}
	
	$tpl->pparse('main',false);
?>
