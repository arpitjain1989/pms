<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('document_details.view.html','main_container');

	$PageIdentifier = "DocumentDetails";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Document Details");
	$breadcrumb = '<li><a href="document_details.php">Manage Documents</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View View Document Details</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.document_details.php');
	
	$objDocumentDetails = new document_details();
	
	$arrDocumentDetails = $objDocumentDetails->fnGetDocumentDetailsById($_REQUEST['id']);

	//echo '<pre>'; print_r($arrDocumentDetails); die;
	
	if($arrDocumentDetails)
	{
		$status = array("1"=>"Received", "2"=>"Not Received","3"=>"N/R", "4"=>"N/A" , "5"=>"Pursuing Degree College");

		$status1 = array("1"=>"Pen Card", "2"=>"Driving License","3"=>"Passport", "4"=>"Election Card" , "5"=>"UID");

		$status2 = array("1"=>"Electricity Bill", "2"=>"Phone Bill","3"=>"Rent Bill", "4"=>"Ration Card" , "5"=>"Gas Bill", "6"=>"Tax Bill");
		if(isset($arrDocumentDetails['photos']))
		{
			$tpl->set_var("photo_status",$status[$arrDocumentDetails['photos']]);
		}
		if(isset($arrDocumentDetails['ssc']))
		{
			$tpl->set_var("ssc_status",$status[$arrDocumentDetails['ssc']]);
		}
		if(isset($arrDocumentDetails['hsc']))
		{
			$tpl->set_var("hsc_status",$status[$arrDocumentDetails['hsc']]);
		}
		if(isset($arrDocumentDetails['lc']))
		{
			$tpl->set_var("lc_status",$status[$arrDocumentDetails['lc']]);
		}
		if(isset($arrDocumentDetails['degree']))
		{
			$tpl->set_var("degree_status",$status[$arrDocumentDetails['degree']]);
		}
		if(isset($arrDocumentDetails['degree_name']))
		{
			$DegreeName = $objDocumentDetails->fnGetDegreeName($arrDocumentDetails['degree_name']);
		}
		
		if(isset($DegreeName) && $DegreeName !='')
		{
			$tpl->set_var("d_name",$DegreeName);
		}
		else
		{
			$tpl->set_var("d_name",'Not Received');
		}
		if(isset($status[$arrDocumentDetails['pg']]))
		{
			$tpl->set_var("pg_status",$status[$arrDocumentDetails['pg']]);
		}
		
		if(isset($arrDocumentDetails['pg_name']))
		{
			$pgName = $objDocumentDetails->fnGetPGDegreeName($arrDocumentDetails['pg_name']);
			$tpl->set_var("pg_name1",$pgName);
		}
		else
		{
			$tpl->set_var("pg_name1",'Not Received');
		}
		if(isset($arrDocumentDetails['additional_cert']))
			$tpl->set_var("additional_cert_status",$status[$arrDocumentDetails['additional_cert']]);
		if(isset($arrDocumentDetails['id_proof']))
		{
			$tpl->set_var("id_proof_status",$status[$arrDocumentDetails['id_proof']]);
		}
		
		if(isset($arrDocumentDetails['given_id_proof']))
		{
			$IdName = $objDocumentDetails->fnGetIdProofName($arrDocumentDetails['given_id_proof']);
			$tpl->set_var("id_name",$IdName);
		}
		else
		{
			$tpl->set_var("id_name",'Not Received');
		}
		//echo 'hello'.$arrDocumentDetails['given_extra_id_proof'];
		if(isset($arrDocumentDetails['given_extra_id_proof']))
		{
			$ExtraIdName = $objDocumentDetails->fnGetIdProofName($arrDocumentDetails['given_extra_id_proof']);
			$tpl->set_var("extra_id_name",$ExtraIdName);
		}
		else
		{
			$tpl->set_var("extra_id_name",'Not Received');
		}

		if(isset($arrDocumentDetails['address_proof']))
		{
			$tpl->set_var("address_proof_status",$status[$arrDocumentDetails['address_proof']]);
		}
		if(isset($arrDocumentDetails['given_address_proof']))
		{
			$AddressName = $objDocumentDetails->fnGetIdProofName($arrDocumentDetails['given_address_proof']);
			$tpl->set_var("address_name",$AddressName);
		}
		else
		{
			$tpl->set_var("address_name",'Not Received');
		}

		if(isset($arrDocumentDetails['bgr']))
		{
			$tpl->set_var("bgr_status",$arrDocumentDetails['bgr']);
		}
		if(isset($status[$arrDocumentDetails['prv_comp_doc']]))
		{
			$tpl->set_var("prv_comp_doc_status",$status[$arrDocumentDetails['prv_comp_doc']]);
		}
		if(isset($status[$arrDocumentDetails['mou_notary']]))
		{
			$tpl->set_var("mou_notary_status",$status[$arrDocumentDetails['mou_notary']]);
		}
		if(isset($status[$arrDocumentDetails['pvf']]))
		{
			$tpl->set_var("pvf_status",$status[$arrDocumentDetails['pvf']]);
		}
		if(isset($status[$arrDocumentDetails['pf_no']]))
		{
			$tpl->set_var("pf_no_status",$status[$arrDocumentDetails['pf_no']]);
		}
		if(isset($status[$arrDocumentDetails['esic_no']]))
		{
			$tpl->set_var("esic_no_status",$status[$arrDocumentDetails['esic_no']]);
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
