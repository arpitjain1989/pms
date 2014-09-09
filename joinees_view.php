<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('joinees_view.html','main_container');

	$PageIdentifier = "Joinees";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Joinees");
	$breadcrumb = '<li><a href="joinees_list.php">Manage Joinees</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Joinees</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.joinees.php');
	include_once('includes/class.document_details.php');
	include_once('includes/class.candidate_list.php');
	
	
	$objJoinees = new joinees();
	$objDocumentDetails = new document_details();
	$objCandidateList = new candidate_list();
	
	$tpl->set_var("DisplayJoineesInformationBlock","");
	$tpl->set_var("DisplayNoJoineesBlock","");
	$tpl->set_var("napplicable","");
	$tpl->set_var("FillSubmitButton","");


	if(isset($_POST['hdnAction']) && $_POST['hdnAction'] == 'complete_joining')
	{
		//echo 'hello'; print_r($_POST); die;
		$addJoiner = $objCandidateList->fnGetCandidateAndSaveInEmployee($_POST['hdnId']);
		
		if($addJoiner)
		{
			header("Location: joinees_list.php?info=update");
			exit;
		}
	}
	
	if(isset($_REQUEST['id']) && trim($_REQUEST['id']) != "")
	{
		$checkJoiner = $objJoinees->fnCheckJoinee($_REQUEST['id']);
		//echo '<pre>'; print_r($checkJoiner);
		if($checkJoiner > 0)
		{
			$tpl->parse("FillSubmitButton",false);
		}
		
		$Joiners = $objJoinees->fnGetJoineesById($_REQUEST['id']);

		//echo '<pre>'; print_r($Joiners);
		$tpl->set_var("empid",$Joiners["cand_id"]);
		$tpl->set_var("emptitle",$Joiners["cand_name"]);

		if(isset($Joiners['hr_exp_joining_date']) && $Joiners['hr_exp_joining_date'] != ''  && $Joiners['hr_exp_joining_date'] != '00-00-0000')
		{
			$tpl->set_var("exp_joining_date",$Joiners['hr_exp_joining_date']);
		}
		else
		{
			$ExpDateOfJoiningByOperations = $objJoinees->fnGetExpDateOfJoinByManager($Joiners['rec_om'],$Joiners["cand_id"]);
			$tpl->set_var("exp_joining_date",$ExpDateOfJoiningByOperations);
		}

		if(isset($Joiners['teamLeader_name_hr']) && $Joiners['teamLeader_name_hr'] != '')
		{
			$tpl->set_var("teamLeaderName",$Joiners["teamLeader_name_hr"]);
		}
		else
		{
			$teamleaderByOperations = $objJoinees->fnGetTemLeaderByManager($Joiners['rec_om'],$Joiners["cand_id"]);
			$tpl->set_var("teamLeaderName",$teamleaderByOperations);
		}
		if(isset($Joiners['shift_title']) && $Joiners['shift_title'] != '')
		{
			$tpl->set_var("recommendedShift",$Joiners["shift_title"]);
		}
		else
		{
			$ShiftByOperations = $objJoinees->fnGetShiftByManager($Joiners['rec_om'],$Joiners["cand_id"]);
			$tpl->set_var("recommendedShift",$ShiftByOperations);
		}

		$docuements_Details = $objJoinees->fnGetDocumentsDetails($_REQUEST['id']);
		//echo '<pre>'; print_r($docuements_Details);

		if(isset($docuements_Details['photos']) && $docuements_Details['photos'] == '1')
		{
			$tpl->set_var("photoes_class", 'iconic-icon-checkmark');
		}
		else if(isset($docuements_Details['photos']) && ($docuements_Details['photos'] == '2' || $docuements_Details['photos'] == '0'))
		{
			$tpl->set_var("photoes_class", 'iconic-icon-x');
		}
		else
		{
			$tpl->set_var("photoes_class", '');
			$tpl->set_var('showNa','n/a');
		}
		
		if(isset($docuements_Details['ssc']) && $docuements_Details['ssc'] == '1')
		{
			$tpl->set_var("ssc_class", 'iconic-icon-checkmark');
		}
		else if(isset($docuements_Details['ssc']) && ($docuements_Details['ssc'] == '2' || $docuements_Details['ssc'] == '0'))
		{
			$tpl->set_var("ssc_class", 'iconic-icon-x');
		}
		else
		{
			$tpl->set_var("ssc_class", '');
			$tpl->set_var('showNa1','n/a');
		}

		if(isset($docuements_Details['hsc']) && $docuements_Details['hsc'] == '1')
		{
			$tpl->set_var("hsc_class", 'iconic-icon-checkmark');
		}
		else if(isset($docuements_Details['hsc']) && ($docuements_Details['hsc'] == '2' || $docuements_Details['hsc'] == '0'))
		{
			$tpl->set_var("hsc_class", 'iconic-icon-x');
		}
		else
		{
			$tpl->set_var("hsc_class", '');
			$tpl->set_var('showNa2','n/a');
		}

		if(isset($docuements_Details['lc']) && $docuements_Details['lc'] == '1')
		{
			$tpl->set_var("lc_class", 'iconic-icon-checkmark');
		}
		else if(isset($docuements_Details['lc']) && ($docuements_Details['lc'] == '2' || $docuements_Details['lc'] == '0'))
		{
			$tpl->set_var("lc_class", 'iconic-icon-x');
		}
		else
		{
			$tpl->set_var("lc_class", '');
			$tpl->set_var('showNa3','n/a');
		}

		if(isset($docuements_Details['degree']) && $docuements_Details['degree'] == '1')
		{
			$tpl->set_var("degree_class", 'iconic-icon-checkmark');
			$getDegreeValue = $objDocumentDetails->fnGetDegreeName($docuements_Details['degree_name']);
			$tpl->set_var('showNa4','('.$getDegreeValue.')');
		}
		else if(isset($docuements_Details['degree']) && $docuements_Details['degree'] == '4')
		{
			$getDegreeValue = $objDocumentDetails->fnGetDegreeName($docuements_Details['degree_name']);
			$tpl->set_var('showNa4','Persuing ('.$getDegreeValue.')');
		}
		else if(isset($docuements_Details['degree']) && ($docuements_Details['degree'] == '2' || $docuements_Details['degree'] == '0'))
		{
			$tpl->set_var("degree_class", 'iconic-icon-x');
		}
		else
		{
			$tpl->set_var("degree_class", '');
			$tpl->set_var('showNa4','n/a');
		}

		if(isset($docuements_Details['pg']) && $docuements_Details['pg'] == '1')
		{
			$tpl->set_var("pg_class", 'iconic-icon-checkmark');
			$getPGValue = $objDocumentDetails->fnGetPGDegreeName($docuements_Details['pg_name']);
			$tpl->set_var('showNa5','('.$getPGValue.')');
		}
		else if(isset($docuements_Details['pg']) &&  ($docuements_Details['pg'] == '2' || $docuements_Details['pg'] == '0'))
		{
			
			$tpl->set_var("pg_class", 'iconic-icon-x');
		}
		else if(isset($docuements_Details['pg']) &&  ($docuements_Details['pg'] == '2' || $docuements_Details['pg'] == '4'))
		{
			$getPGValue = $objDocumentDetails->fnGetPGDegreeName($docuements_Details['pg_name']);
			$tpl->set_var('showNa5','Persuing ('.$getPGValue.')');
		}
		else
		{
			$tpl->set_var("pg_class", '');
			$tpl->set_var('showNa5','n/a');
		}
		
		
		if(isset($docuements_Details['additional_cert']) && $docuements_Details['additional_cert'] == '1')
		{
			$tpl->set_var("add_cert_class", 'iconic-icon-checkmark');
		}
		else if(isset($docuements_Details['additional_cert']) && ($docuements_Details['additional_cert'] == '2' || $docuements_Details['additional_cert'] == '0'))
		{
			
			$tpl->set_var("add_cert_class", 'iconic-icon-x');
		}
		else
		{
			$tpl->set_var("add_cert_class", '');
			$tpl->set_var('showNa6','n/a');
		}
		
		
		if(isset($docuements_Details['id_proof']) && $docuements_Details['id_proof'] == '1')
		{
			$tpl->set_var("id_proof_class", 'iconic-icon-checkmark');
			$getIdName = $objDocumentDetails->fnGetIdProofName($docuements_Details['given_id_proof']);
			$tpl->set_var('showNa7','('.$getIdName.')');
		}
		else if(isset($docuements_Details['id_proof']) && ($docuements_Details['id_proof'] == '2' || $docuements_Details['id_proof'] == '0'))
		{
			
			$tpl->set_var("id_proof_class", 'iconic-icon-x');
		}
		else
		{
			$tpl->set_var("id_proof_class", '');
			$tpl->set_var('showNa7','n/a');
		}
		
		if(isset($docuements_Details['extra_id_proof']) && $docuements_Details['extra_id_proof'] == '1')
		{
			$tpl->set_var("extra_id_proof_class", 'iconic-icon-checkmark');
			$getIdName = $objDocumentDetails->fnGetIdProofName($docuements_Details['given_extra_id_proof']);
			$tpl->set_var('showNa11','('.$getIdName.')');
		}
		else if(isset($docuements_Details['id_proof']) && ($docuements_Details['id_proof'] == '2' || $docuements_Details['id_proof'] == '0'))
		{
			$tpl->set_var("extra_id_proof_class", 'iconic-icon-x');
		}
		else
		{
			$tpl->set_var("extra_id_proof_class", '');
			$tpl->set_var('showNa11','n/a');
		}


		if(isset($docuements_Details['address_proof']) && $docuements_Details['address_proof'] == '1')
		{
			$tpl->set_var("address_proof_class", 'iconic-icon-checkmark');
			$getAddressName = $objDocumentDetails->fnGetIdProofName($docuements_Details['given_address_proof']);
			$tpl->set_var('showNa8','('.$getAddressName.')');
		}
		else if(isset($docuements_Details['address_proof']) && ($docuements_Details['address_proof'] == '2' || $docuements_Details['address_proof'] == '0'))
		{
			
			$tpl->set_var("address_proof_class", 'iconic-icon-x');
		}
		else
		{
			$tpl->set_var("address_proof_class", '');
			$tpl->set_var('showNa8','n/a');
		}
		
		if(isset($docuements_Details['bgr']) && $docuements_Details['bgr'] == '1')
		{
			$tpl->set_var("bgr_class", 'iconic-icon-checkmark');
		}
		else if(isset($docuements_Details['bgr']) && ($docuements_Details['bgr'] == '2' || $docuements_Details['bgr'] == '0'))
		{
			$tpl->set_var("bgr_class", 'iconic-icon-x');
		}
		else
		{
			$tpl->set_var("bgr_class", '');
			$tpl->set_var('showNa9','n/a');
		}
		
		if(isset($docuements_Details['prv_comp_doc']) && $docuements_Details['prv_comp_doc'] == '1')
		{
			$tpl->set_var("prv_comp_doc_class", 'iconic-icon-checkmark');
		}
		else if(isset($docuements_Details['prv_comp_doc']) && ($docuements_Details['prv_comp_doc'] == '2' || $docuements_Details['prv_comp_doc'] == '0'))
		{
			$tpl->set_var("prv_comp_doc_class", 'iconic-icon-x');
		}
		else
		{
			$tpl->set_var("prv_comp_doc_class", '');
			$tpl->set_var('showNa10','n/a');
		}
		
		$tpl->parse("DisplayJoineesInformationBlock",false);
	}
	else
	{
		$tpl->parse("DisplayNoJoineesBlock",false);
	}
	
	$tpl->pparse('main',false);
?>

