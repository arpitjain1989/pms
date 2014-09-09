<?php
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('candidate_document_details.view.html','main_container');

	$PageIdentifier = "CandidateDocumentDetails";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Candidate Document Details");
	$breadcrumb = '<li><a href="candidate_document_details.php">Manage Documents</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View View Document Details</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.document_details.php');
	
	$objDocumentDetails = new document_details();
	
	$arrDocumentDetails = $objDocumentDetails->fnGetCandidateDocumentDetailsById($_REQUEST['id']);
	//echo '<pre>'; print_r($arrDocumentDetails); die;

	if($arrDocumentDetails)
	{
		//echo $arrDocumentDetails['degree'];
		$status = array("0"=>"Not Received", "1"=>"Received", "2"=>"Not Received","3"=>"N/A" , "4"=>"Pursuing Degree College");

		$status2 = array("1"=>"Completed", "2"=>"Pending","3"=>"N/A");

		$tpl->set_var("photo_status",$status[$arrDocumentDetails['photos']]);
		$tpl->set_var("ssc_status",$status[$arrDocumentDetails['ssc']]);
		$tpl->set_var("hsc_status",$status[$arrDocumentDetails['hsc']]);
		$tpl->set_var("lc_status",$status[$arrDocumentDetails['lc']]);
		//$tpl->set_var("degree_status",$status[$arrDocumentDetails['degree']]);
		if($arrDocumentDetails['degree'])
		{
			$getDegreeName = $objDocumentDetails->fnGetDegreeName($arrDocumentDetails['degree_name']);
			$tpl->set_var("degree_Name",$getDegreeName);
		}
		if(isset($status[$arrDocumentDetails['diploma']]))
		{
			$tpl->set_var("diploma_status",$status[$arrDocumentDetails['diploma']]);
		}
		if(isset($status[$arrDocumentDetails['pg']]))
		{
			$tpl->set_var("pg_status",$status[$arrDocumentDetails['pg']]);
		}
		if($arrDocumentDetails['pg'])
		{
			$getPgName = $objDocumentDetails->fnGetPGDegreeName($arrDocumentDetails['pg']);
			$tpl->set_var("pg_Name",$getPgName);
		}
		$tpl->set_var("additional_cert_status",$status[$arrDocumentDetails['additional_cert']]);
		$tpl->set_var("id_proof_status",$status[$arrDocumentDetails['id_proof']]);
		//echo '<pre>'; print_r($arrDocumentDetails);
		if($arrDocumentDetails['id_proof'])
		{
			$getIdName = $objDocumentDetails->fnGetIdProofName($arrDocumentDetails['given_id_proof']);
			$tpl->set_var("id_Name",$getIdName);
		}
		
		$tpl->set_var("address_proof_status",$status[$arrDocumentDetails['address_proof']]);
		if($arrDocumentDetails['address_proof'])
		{
			$getAddressName = $objDocumentDetails->fnGetIdProofName($arrDocumentDetails['given_address_proof']);
			$tpl->set_var("address_Name",$getAddressName);
		}

		if(isset($status[$arrDocumentDetails['bgr']]))
		{
			$tpl->set_var("bgr_status",$status[$arrDocumentDetails['bgr']]);
		}
		if(isset($status[$arrDocumentDetails['prv_comp_doc']]))
		{
			$tpl->set_var("prv_comp_doc_status",$status[$arrDocumentDetails['prv_comp_doc']]);
		}
		if(isset($status2[$arrDocumentDetails['mou_notary']]))
		{
			$tpl->set_var("mou_notary_status",$status2[$arrDocumentDetails['mou_notary']]);
		}
		if(isset($status2[$arrDocumentDetails['pvf']]))
		{
			$tpl->set_var("pvf_status",$status2[$arrDocumentDetails['pvf']]);
		}
		if(isset($status2[$arrDocumentDetails['pf_no']]))
		{
			$tpl->set_var("pf_no_status",$status2[$arrDocumentDetails['pf_no']]);
		}
		if(isset($status2[$arrDocumentDetails['esic_no']]))
		{
			$tpl->set_var("esic_no_status",$status2[$arrDocumentDetails['esic_no']]);
		}

		if(isset($status1[$arrDocumentDetails['given_id_proof']]))
		{
			$tpl->set_var("given_id_proof_status",$status1[$arrDocumentDetails['given_id_proof']]);
		}
		if(isset($status2[$arrDocumentDetails['given_address_proof']]))
		{
			$tpl->set_var("given_address_proof_status",$status2[$arrDocumentDetails['given_address_proof']]);
		}

		$tpl->SetAllValues($arrDocumentDetails);
	}

	$tpl->pparse('main',false);
?>
