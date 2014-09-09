<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	//$tpl->load_file('template.html','main');
	$tpl->load_file('joinees_print.html','main');

	//$PageIdentifier = "Joinees";
	//include_once('userrights.php');

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

	$tpl->set_var("id_proof_class", '');
	$tpl->set_var("add_cert_class", '');
	$tpl->set_var('showNa5','');
	$tpl->set_var('showNa4','');
	$tpl->set_var("degree_class", '');
	$tpl->set_var("lc_class", '');
	$tpl->set_var("hsc_class", '');
	$tpl->set_var("ssc_class", '');
	$tpl->set_var("photoes_class", '');
	$tpl->set_var("pg_class", '');
	$tpl->set_var('showNa8','');
	$tpl->set_var("address_proof_class", '');
	$tpl->set_var("bgr_class", '');
	$tpl->set_var("prv_comp_doc_class", '');

	


	if(isset($_POST['hdnAction']) && $_POST['hdnAction'] == 'complete_joining')
	{
		//echo 'hello'; print_r($_POST);
		$addJoiner = $objCandidateList->fnGetCandidateAndSaveInEmployee($_POST['hdnId']);
		
		if($addJoiner)
		{
			header("Location: joinees_list.php?info=update");
			exit;
		}
	}
	
	if(isset($_REQUEST['val']) && trim($_REQUEST['val']) != "")
	{
		$pieces = explode("/", $_REQUEST['val']);
		//print_r($pieces);
		$tpl->set_var("DisplayJoineesInformationBlock","");
		$tpl->set_var("showNa4","");
		if(count($pieces) > 0)
		{
			foreach($pieces as $p)
			{
				
				if($p != '')
					{
					//echo $p; die;
					//$checkJoiner = $objJoinees->fnCheckJoinee($p);
					//echo '<pre>'; print_r($checkJoiner);
					
					$Joiners = $objJoinees->fnGetJoineesById($p);

					//echo '<pre>'; print_r($Joiners);
					$tpl->set_var("empid",$Joiners["cand_id"]);
					$tpl->set_var("emptitle",$Joiners["cand_name"]);

					if(isset($Joiners['hr_exp_joining_date']) && $Joiners['hr_exp_joining_date'] != '')
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

					$docuements_Details = $objJoinees->fnGetDocumentsDetails($p);
					//echo '<pre>'; print_r($docuements_Details);

					if(isset($docuements_Details['photos']) && $docuements_Details['photos'] == '1')
					{
						$tpl->set_var("photoes_class", 'Y');
					}
					else if(isset($docuements_Details['photos']) && ($docuements_Details['photos'] == '2' || $docuements_Details['photos'] == '0'))
					{
						$tpl->set_var("photoes_class", 'N');
					}
					else
					{
						$tpl->set_var('photoes_class','n/a');
					}
					
					if(isset($docuements_Details['ssc']) && $docuements_Details['ssc'] == '1')
					{
						$tpl->set_var("ssc_class", 'Y');
					}
					else if(isset($docuements_Details['ssc']) && ($docuements_Details['ssc'] == '2' || $docuements_Details['ssc'] == '0'))
					{
						$tpl->set_var("ssc_class", 'N');
					}
					else
					{
						$tpl->set_var("ssc_class", 'n/a');
					}

					if(isset($docuements_Details['hsc']) && $docuements_Details['hsc'] == '1')
					{
						$tpl->set_var("hsc_class", 'Y');
					}
					else if(isset($docuements_Details['hsc']) && ($docuements_Details['hsc'] == '2' || $docuements_Details['hsc'] == '0'))
					{
						$tpl->set_var("hsc_class", 'N');
					}
					else
					{
						$tpl->set_var("hsc_class", 'n/a');
					}

					if(isset($docuements_Details['lc']) && $docuements_Details['lc'] == '1')
					{
						$tpl->set_var("lc_class", 'Y');
					}
					else if(isset($docuements_Details['lc']) && ($docuements_Details['lc'] == '2' || $docuements_Details['lc'] == '0'))
					{
						$tpl->set_var("lc_class", 'N');
					}
					else
					{
						$tpl->set_var("lc_class", 'n/a');
					}

					if(isset($docuements_Details['degree']) && $docuements_Details['degree'] == '1')
					{
						$tpl->set_var("degree_class", 'Y');
						$getDegreeValue = $objDocumentDetails->fnGetDegreeName($docuements_Details['degree_name']);
						$tpl->set_var('showNa4','('.$getDegreeValue.')');
					}
					else if(isset($docuements_Details['degree']) && ($docuements_Details['degree'] == '2' || $docuements_Details['degree'] == '0'))
					{
						$tpl->set_var("degree_class", 'N');
						$tpl->set_var('showNa4','');
					}
					else
					{
						$tpl->set_var("degree_class", 'n/a');
						$tpl->set_var('showNa4','');
					}

					if(isset($docuements_Details['pg']) && $docuements_Details['pg'] == '1')
					{
						$tpl->set_var("pg_class", 'Y');
						$getPGValue = $objDocumentDetails->fnGetPGDegreeName($docuements_Details['pg_name']);
						$tpl->set_var('showNa5','('.$getPGValue.')');
					}
					else if(isset($docuements_Details['pg']) && ($docuements_Details['pg'] == '2' || $docuements_Details['pg'] == '0'))
					{
						
						$tpl->set_var("pg_class", 'N');
						$tpl->set_var('showNa5','');
					}
					else
					{
						$tpl->set_var("pg_class", 'n/a');
						$tpl->set_var('showNa5','');
					}
					
					
					if(isset($docuements_Details['additional_cert']) && $docuements_Details['additional_cert'] == '1')
					{
						$tpl->set_var("add_cert_class", 'Y');
					}
					else if(isset($docuements_Details['additional_cert']) && ($docuements_Details['additional_cert'] == '2' || $docuements_Details['additional_cert'] == '0'))
					{
						
						$tpl->set_var("add_cert_class", 'N');
					}
					else
					{
						$tpl->set_var("add_cert_class", 'n/a');
					}
					
					
					if(isset($docuements_Details['id_proof']) && $docuements_Details['id_proof'] == '1')
					{
						$tpl->set_var("id_proof_class", 'Y');
						$getIdName = $objDocumentDetails->fnGetIdProofName($docuements_Details['given_id_proof']);
						$tpl->set_var('showNa7','('.$getIdName.')');
					}
					else if(isset($docuements_Details['id_proof']) && ($docuements_Details['id_proof'] == '2' || $docuements_Details['id_proof'] == '0'))
					{
						
						$tpl->set_var("id_proof_class", 'N');
						$tpl->set_var('showNa7','');
					}
					else
					{
						$tpl->set_var("id_proof_class", 'n/a');
						$tpl->set_var('showNa7','');
					}


					if(isset($docuements_Details['address_proof']) && $docuements_Details['address_proof'] == '1')
					{
						$tpl->set_var("address_proof_class", 'Y');
						$getAddressName = $objDocumentDetails->fnGetIdProofName($docuements_Details['given_address_proof']);
						$tpl->set_var('showNa8','('.$getAddressName.')');
					}
					else if(isset($docuements_Details['address_proof']) && ($docuements_Details['address_proof'] == '2' || $docuements_Details['address_proof'] == '0'))
					{
						
						$tpl->set_var("address_proof_class", 'N');
						$tpl->set_var('showNa8','');
					}
					else
					{
						$tpl->set_var("address_proof_class", 'n/a');
						$tpl->set_var('showNa8','');
					}
					
					if(isset($docuements_Details['bgr']) && $docuements_Details['bgr'] == '1')
					{
						$tpl->set_var("bgr_class", 'Y');
					}
					else if(isset($docuements_Details['bgr']) && ($docuements_Details['bgr'] == '2' || $docuements_Details['bgr'] == '0'))
					{
						$tpl->set_var("bgr_class", 'N');
					}
					else
					{
						$tpl->set_var("bgr_class", 'n/a');
					}
					
					if(isset($docuements_Details['prv_comp_doc']) && $docuements_Details['prv_comp_doc'] == '1')
					{
						$tpl->set_var("prv_comp_doc_class", 'Y');
					}
					else if(isset($docuements_Details['prv_comp_doc']) && ($docuements_Details['prv_comp_doc'] == '2' || $docuements_Details['prv_comp_doc'] == '0'))
					{
						$tpl->set_var("prv_comp_doc_class", 'N');
					}
					else
					{
						$tpl->set_var("prv_comp_doc_class", 'n/a');
					}
					
					$tpl->parse("DisplayJoineesInformationBlock",true);
				}
			}
		}
		
	}
	else
	{
		$tpl->parse("DisplayNoJoineesddsddBlock",false);
	}
	
	$tpl->pparse('main',false);
?>

