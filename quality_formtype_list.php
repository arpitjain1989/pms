<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('quality_formtype_list.html','main_container');

	$PageIdentifier = "QualityFormType";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Quality Form Type");
	$breadcrumb = '<li class="active">Manage Quality Form Type</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.quality_form.php');

	$tpl->set_var("DisplayMessageBlock","");

	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'suc':
				$messageClass = "alert-success";
				$message = "Quality form type added successfully";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Quality form type already added. Cannot add again.";
				break;
			case 'dele':
				$messageClass = "alert-error";
				$message = "Error deleting quality form, record(s) not selected.";
				break;
			case 'delerr':
				$msg = "";
				if(isset($_REQUEST["norec"]) && trim($_REQUEST["norec"]))
					$msg .= " ".$_REQUEST["norec"]." record(s) not found.";
				if(isset($_REQUEST["err"]) && trim($_REQUEST["err"]))
					$msg .= " ".$_REQUEST["err"]." record(s) have parameters defined.";
			
				$messageClass = "alert-error";
				$message = "Quality form(s) not deleted.".$msg;
				break;
			case 'dels':
				$messageClass = "alert-success";
				$message = "Quality form(s) deleted successfully.";
				break;
		}

		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	$objQualityForm = new quality_form();
	
	/* Delete the records - not deleted physically, bt marked as deleted */
	if(isset($_POST["action"]) && trim($_POST["action"]) == "DeleteQualityFormType")
	{
		if(isset($_POST["chk"]) && count($_POST["chk"]))
		{
			//("noRecErr"=>$noRecErr, "err"=>$err)
			$result = $objQualityForm->fnDeleteQualityFormType($_POST["chk"]);
			if(isset($result["noRecErr"]) && $result["noRecErr"] == 0 && isset($result["err"]) && $result["err"] == 0)
			{
				header("Location: quality_formtype_list.php?info=dels");
				exit;
			}
			else
			{
				$queryStr = "";
				if(isset($result["noRecErr"]) && $result["noRecErr"] != 0)
					$queryStr .= "&norec=".$result["noRecErr"];
				if(isset($result["err"]) && $result["err"] != 0)
					$queryStr .= "&err=".$result["err"];
					
				header("Location: quality_formtype_list.php?info=delerr".$queryStr);
				exit;					
			}
		}
		else
		{
			header("Location: quality_formtype_list.php?info=dele");
			exit;
		}
	}

	$arrQualityFormType = $objQualityForm->getForm();

	$tpl->set_var("FillQualityFormTypesList","");
	if(count($arrQualityFormType) > 0)
	{
		foreach($arrQualityFormType as $curQualityFormType)
		{
			$tpl->SetAllValues($curQualityFormType);
			$tpl->parse("FillQualityFormTypesList",true);
		}
	}

	$tpl->pparse('main',false);

?>
