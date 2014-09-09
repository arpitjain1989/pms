<?php 
	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('document_details.html','main_container');

	$PageIdentifier = "DocumentDetails";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Document Details");
	$breadcrumb = '<li class="active">Manage Document Details</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.document_details.php');
	
	$objDocumentDetails = new document_details();
	
	$tpl->set_var("FillPendingDocument","");
	$tpl->set_var("FillAllDocument","");
	
	if(isset($_REQUEST['id']) &&  $_REQUEST['id'] == 'pending')
	{
		$arrDocumentDetails = $objDocumentDetails->fnGetAllPendingDocumentsDetails();
		$tpl->parse("FillAllDocument",false);	
	}
	else
	{
		$arrDocumentDetails = $objDocumentDetails->fnGetAllDocumentsDetails();
		$tpl->parse("FillPendingDocument",false);	
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
				$message = "Document details inserted successfully.";
				break;
			case 'update':
				$messageClass = "alert-success";
				$message = "Document details updated successfully.";
				break;
			case 'delete':
				$messageClass = "alert-success";
				$message = "No records found.";
				break;
			case 'exist':
				$messageClass = "alert-success";
				$message = "Document details deleted successfully.";
				break;
		}
		
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);
			
			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	
	
	/*if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] = 'delete')
	{
		$delteDocuemtnDetails = $objDocumentDetails->fnDeleteDocumentsDetails($_POST);
		if($delteDocuemtnDetails)
		{
			header("Location: document_details.php?info=delete");
		}
		else
		{
			
		}
	}*/
	
	$tpl->set_var("FillDocumentDetails","");
	foreach($arrDocumentDetails as $arrDocuments)
	{
		$tpl->SetAllValues($arrDocuments);
		$tpl->parse("FillDocumentDetails",true);
	}

	$tpl->pparse('main',false);
?>
