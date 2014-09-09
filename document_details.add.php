<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('document_details.add.html','main_container');

	$PageIdentifier = "DocumentDetails";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Edit document details");
	$breadcrumb = '<li><a href="document_details.php">Manage Document Details</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Edit Document Details</li>';
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
			$updateDocumnetDetails = $objDocumentDetails->fnUpdateDocumentDetails($_POST,$_FILES);
			if($updateDocumnetDetails)
			{
				header("Location: document_details.php?info=update");
				exit;
			}
	}
	
	if(isset($_REQUEST['action']) && $_REQUEST['action']='update')
	{
		$DocumentDetails = $objDocumentDetails->fnGetDocumentDetailsById($_REQUEST['id']);
		//echo '<pre>'; print_r($DocumentDetails);
		if($DocumentDetails['photo_extension'] != '')
		{
			$photoname = 'media/photos/'.$_REQUEST['id'].'.'.$DocumentDetails['photo_extension'];
			if(file_exists($photoname))
			{
				$tpl->set_var('photopath',$_REQUEST['id'].'.'.$DocumentDetails['photo_extension']);
			}

			$sscPhotoName = 'media/ssc/'.$_REQUEST['id'].'.'.$DocumentDetails['ssc_image_extension'];
			if(file_exists($sscPhotoName))
			{
				$tpl->set_var('sscpath',$_REQUEST['id'].'.'.$DocumentDetails['ssc_image_extension']);
			}

			$hscPhotoName = 'media/hsc/'.$_REQUEST['id'].'.'.$DocumentDetails['hsc_image_extension'];
			if(file_exists($hscPhotoName))
			{
				$tpl->set_var('hscpath',$_REQUEST['id'].'.'.$DocumentDetails['hsc_image_extension']);
			}

			$lcPhotoName = 'media/lc/'.$_REQUEST['id'].'.'.$DocumentDetails['lc_image_extension'];
			if(file_exists($lcPhotoName))
			{
				$tpl->set_var('lcpath',$_REQUEST['id'].'.'.$DocumentDetails['lc_image_extension']);
			}

			$degreePhotoName = 'media/degree/'.$_REQUEST['id'].'.'.$DocumentDetails['degree_image_extension'];
			if(file_exists($degreePhotoName))
			{
				$tpl->set_var('degreepath',$_REQUEST['id'].'.'.$DocumentDetails['degree_image_extension']);
			}
			
			$pgPhotoName = 'media/pg/'.$_REQUEST['id'].'.'.$DocumentDetails['pg_image_extension'];
			if(file_exists($pgPhotoName))
			{
				$tpl->set_var('pgpath',$_REQUEST['id'].'.'.$DocumentDetails['pg_image_extension']);
			}
			
			$certPhotoName = 'media/cert/'.$_REQUEST['id'].'.'.$DocumentDetails['add_cert_image_extension'];
			if(file_exists($certPhotoName))
			{
				$tpl->set_var('certpath',$_REQUEST['id'].'.'.$DocumentDetails['add_cert_image_extension']);
			}
			
			$proofPhotoName = 'media/idProof/'.$_REQUEST['id'].'.'.$DocumentDetails['id_proof_image_extension'];
			if(file_exists($proofPhotoName))
			{
				$tpl->set_var('proofpath',$_REQUEST['id'].'.'.$DocumentDetails['id_proof_image_extension']);
			}
			
			$AddProofPhotoName = 'media/address/'.$_REQUEST['id'].'.'.$DocumentDetails['address_proof_image_extension'];
			if(file_exists($AddProofPhotoName))
			{
				$tpl->set_var('addproofpath',$_REQUEST['id'].'.'.$DocumentDetails['address_proof_image_extension']);
			}
			
			$bgrPhotoName = 'media/bgr/'.$_REQUEST['id'].'.'.$DocumentDetails['bgr_image_extension'];
			if(file_exists($bgrPhotoName))
			{
				$tpl->set_var('bgrpath',$_REQUEST['id'].'.'.$DocumentDetails['bgr_image_extension']);
			}
			
			$pvr_com_doc_PhotoName = 'media/pvr_com_doc/'.$_REQUEST['id'].'.'.$DocumentDetails['prv_comp_doc_image_extension'];
			if(file_exists($pvr_com_doc_PhotoName))
			{
				$tpl->set_var('docspath',$_REQUEST['id'].'.'.$DocumentDetails['prv_comp_doc_image_extension']);
			}
		}
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
