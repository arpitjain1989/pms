<?php 

	include('common.php');
	$tpl = new Template($app_path);

	ini_set("max_execution_time",300);

	$tpl->load_file('template.html','main');
	$tpl->load_file('report_document.html','main_container');

	$PageIdentifier = "DocumentReport";
	include_once('userrights.php');

	$curDate = Date('Y-m-d');
	$curYear = Date('Y');

	$arrYear = array($curYear, $curYear-1);

	$tpl->set_var("mainheading","Document Report");
	$breadcrumb = '<li class="active">Document Report</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.document_details.php');
	
	$objDocumentDetails = new document_details();

	$arrDocumentDetails = $objDocumentDetails->fnGetAllDocumentsDetails();

	$tpl->set_var("FillDocumentDetails","");
	foreach($arrDocumentDetails as $arrDocuments)
	{
		$status = array("0"=>"Not Received", "1"=>"Received", "2"=>"Not Received","3"=>"N/R", "4"=>"N/A" , "5"=>"Pursuing Degree College");

		$status1 = array("1"=>"Completed", "2"=>"Pending","3"=>"N/A");

		if(isset($arrDocuments['photos'])) $tpl->set_var("photo_status",$status[$arrDocuments['photos']]);
		if(isset($arrDocuments['ssc'])) $tpl->set_var("ssc_status",$status[$arrDocuments['ssc']]);
		if(isset($arrDocuments['hsc'])) $tpl->set_var("hsc_status",$status[$arrDocuments['hsc']]);
		if(isset($arrDocuments['lc'])) $tpl->set_var("lc_status",$status[$arrDocuments['lc']]);
		if(isset($arrDocuments['degree'])) $tpl->set_var("degree_status",$status[$arrDocuments['degree']]);
		if(isset($arrDocuments['diploma'])) $tpl->set_var("diploma_status",$status[$arrDocuments['diploma']]);
		if(isset($arrDocuments['pg'])) $tpl->set_var("pg_status",$status[$arrDocuments['pg']]);
		if(isset($arrDocuments['additional_cert'])) $tpl->set_var("additional_cert_status",$status[$arrDocuments['additional_cert']]);
		if(isset($arrDocuments['id_proof'])) $tpl->set_var("id_proof_status",$status[$arrDocuments['id_proof']]);
		if(isset($arrDocuments['address_proof'])) $tpl->set_var("address_proof_status",$status[$arrDocuments['address_proof']]);
		if(isset($arrDocuments['bgr'])) $tpl->set_var("bgr_status",$status[$arrDocuments['bgr']]);
		if(isset($arrDocuments['prv_comp_doc'])) $tpl->set_var("prv_comp_doc_status",$status[$arrDocuments['prv_comp_doc']]);
		if(isset($arrDocuments['mou_notary'])) $tpl->set_var("mou_notary_status",$status[$arrDocuments['mou_notary']]);
		if(isset($arrDocuments['pvf'])) $tpl->set_var("pvf_status",$status[$arrDocuments['pvf']]);
		if(isset($arrDocuments['pf_no'])) $tpl->set_var("pf_no_status",$status[$arrDocuments['pf_no']]);
		if(isset($arrDocuments['esic_no'])) $tpl->set_var("esic_no_status",$status[$arrDocuments['esic_no']]);
		
		//echo '<pre>'; print_r($arrDocuments); die;
		$tpl->SetAllValues($arrDocuments);
		$tpl->parse("FillDocumentDetails",true);
	}

	$tpl->pparse('main',false);
?>
