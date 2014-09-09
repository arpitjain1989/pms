<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('candidate_document_details.add.html','main_container');

	$PageIdentifier = "CandidateDocumentDetails";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Edit candidate document details");
	$breadcrumb = '<li><a href="candidate_document_details.php">Manage Document Details</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Edit Document Details</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.document_details.php');
	
	$objDocumentDetails = new document_details();

	$getAllGraduation = $objDocumentDetails->fnGetAllGraduation();

	$getAllPostGraduation = $objDocumentDetails->fnGetAllPostGraduation();

	$getAllAddressProof = $objDocumentDetails->fnGetAllAddressProof();
	
	$getAllIdProof = $objDocumentDetails->fnGetAllIdProof();
	
	if(isset($_REQUEST['id']))
	{
		//echo $_REQUEST[id];
		$tpl->set_var('hdnid',"$_REQUEST[id]");
	}
	
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] =='update')
	{
			$updateDocumnetDetails = $objDocumentDetails->fnUpdateCadidateDocumentDetails($_POST);
			if($updateDocumnetDetails)
			{
				header("Location: candidate_document_details.php?info=update");
				exit;
			}
	}
	
	if(isset($_REQUEST['action']) && $_REQUEST['action']='update')
	{
		$DocumentDetails = $objDocumentDetails->fnGetCandidateDocumentDetailsById($_REQUEST['id']);
		//echo '<pre>'; print_r($DocumentDetails);
		if($DocumentDetails)
		{
			$tpl->SetAllValues($DocumentDetails);
		}
		$tpl->set_var('action','update');
	}

	$tpl->set_var('FillGraduaction','');
	if(count($getAllGraduation) > 0)
	{
		foreach($getAllGraduation as $allGraduaction)
		{
			$tpl->set_var('grad_id',$allGraduaction['id']);
			$tpl->set_var('grad_name',$allGraduaction['title']);
			$tpl->parse("FillGraduaction",true);
		}
	}

	
	$tpl->set_var('FillPostGraduaction','');
	if(count($getAllPostGraduation) > 0)
	{
		foreach($getAllPostGraduation as $allPostGraduaction)
		{
			$tpl->set_var('post_grad_id',$allPostGraduaction['id']);
			$tpl->set_var('post_grad_name',$allPostGraduaction['title']);
			$tpl->parse("FillPostGraduaction",true);
		}
	}

	
	$tpl->set_var('FillAddressProof','');
	if(count($getAllAddressProof) > 0)
	{
		foreach($getAllAddressProof as $allAddressProof)
		{
			$tpl->set_var('address_id',$allAddressProof['id']);
			$tpl->set_var('address_name',$allAddressProof['title']);
			$tpl->parse("FillAddressProof",true);
		}
	}

	$tpl->set_var('FillIdProof','');
	if(count($getAllIdProof) > 0)
	{
		foreach($getAllIdProof as $allIdProof)
		{
			$tpl->set_var('idProof_id',$allIdProof['id']);
			$tpl->set_var('idProof_name',$allIdProof['title']);
			$tpl->parse("FillIdProof",true);
		}
	}
	
	
	$tpl->pparse('main',false);
?>
