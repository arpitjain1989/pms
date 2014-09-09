<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('quality_afd_list.html','main_container');

	$PageIdentifier = "QualityAFD";
	include_once('userrights.php');
	
	$tpl->set_var("mainheading","Manage Quality AFD");
	$breadcrumb = '<li class="active">Manage Quality AFD</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.quality_form.php');

	$tpl->set_var("DisplayMessageBlock","");
	
	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'suc':
				$messageClass = "alert-success";
				$message = "Quality AFD added successfully";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Quality AFD already added. Cannot add again.";
				break;
			case 'dele':
				$messageClass = "alert-error";
				$message = "Error deleting quality afd, record(s) not selected.";
				break;
			case 'delerr':
				$msg = "";
				if(isset($_REQUEST["norec"]) && trim($_REQUEST["norec"]))
					$msg .= " ".$_REQUEST["norec"]." record(s) not found.";

				$messageClass = "alert-error";
				$message = "Quality afd(s) not deleted.".$msg;
				break;
			case 'dels':
				$messageClass = "alert-success";
				$message = "Quality afd(s) deleted successfully.";
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
	if(isset($_POST["action"]) && trim($_POST["action"]) == "DeleteQualityAfd")
	{
		if(isset($_POST["chk"]) && count($_POST["chk"]))
		{
			//("noRecErr"=>$noRecErr, "err"=>$err)
			$result = $objQualityForm->fnDeleteQualityAfd($_POST["chk"]);
			if($result == 0)
			{
				header("Location: quality_afd_list.php?info=dels");
				exit;
			}
			else
			{
				header("Location: quality_afd_list.php?info=delerr&norec=".$result);
				exit;					
			}
		}
		else
		{
			header("Location: quality_afd_list.php?info=dele");
			exit;
		}
	}

	$arrQualityAFD = $objQualityForm->fnGetAllQualityAFD();

	$tpl->set_var("FillQualityParametersList","");
	if(count($arrQualityAFD) > 0)
	{
		$arrActive = array("0"=>"Active", "1"=>"Inactive");
		foreach($arrQualityAFD as $curQualityAFD)
		{
			$tpl->SetAllValues($curQualityAFD);
			$tpl->set_var("active_text", "");
			if(isset($curQualityAFD["isactive"]) && trim($curQualityAFD["isactive"]) != "")
				$tpl->set_var("active_text", $arrActive[$curQualityAFD["isactive"]]);
			$tpl->parse("FillQualityParametersList",true);
		}
	}

	$tpl->pparse('main',false);

?>
