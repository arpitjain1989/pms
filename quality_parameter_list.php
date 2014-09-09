<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('quality_parameter_list.html','main_container');

	$PageIdentifier = "QualityParameter";
	include_once('userrights.php');
	
	$tpl->set_var("mainheading","Manage Quality Parameter");
	$breadcrumb = '<li class="active">Manage Quality Parameter</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.quality_form.php');

	$tpl->set_var("DisplayMessageBlock","");
	
	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'suc':
				$messageClass = "alert-success";
				$message = "Quality parameter added successfully";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Quality parameter already added. Cannot add again.";
				break;
			case 'dele':
				$messageClass = "alert-error";
				$message = "Error deleting quality parameter, record(s) not selected.";
				break;
			case 'delerr':
				$msg = "";
				if(isset($_REQUEST["norec"]) && trim($_REQUEST["norec"]))
					$msg .= " ".$_REQUEST["norec"]." record(s) not found.";
				if(isset($_REQUEST["err"]) && trim($_REQUEST["err"]))
					$msg .= " ".$_REQUEST["err"]." record(s) have afd's defined.";
			
				$messageClass = "alert-error";
				$message = "Quality parameter(s) not deleted.".$msg;
				break;
			case 'dels':
				$messageClass = "alert-success";
				$message = "Quality parameter(s) deleted successfully.";
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
	if(isset($_POST["action"]) && trim($_POST["action"]) == "DeleteQualityParameter")
	{
		if(isset($_POST["chk"]) && count($_POST["chk"]))
		{
			//("noRecErr"=>$noRecErr, "err"=>$err)
			$result = $objQualityForm->fnDeleteQualityParameter($_POST["chk"]);
			if(isset($result["noRecErr"]) && $result["noRecErr"] == 0 && isset($result["err"]) && $result["err"] == 0)
			{
				header("Location: quality_parameter_list.php?info=dels");
				exit;
			}
			else
			{
				$queryStr = "";
				if(isset($result["noRecErr"]) && $result["noRecErr"] != 0)
					$queryStr .= "&norec=".$result["noRecErr"];
				if(isset($result["err"]) && $result["err"] != 0)
					$queryStr .= "&err=".$result["err"];
					
				header("Location: quality_parameter_list.php?info=delerr".$queryStr);
				exit;					
			}
		}
		else
		{
			header("Location: quality_parameter_list.php?info=dele");
			exit;
		}
	}
	
	$arrQualityParameters = $objQualityForm->fnGetAllQualityParameters();

	$tpl->set_var("FillQualityParametersList","");
	if(count($arrQualityParameters) > 0)
	{
		$arrActive = array("0"=>"Active", "1"=>"Inactive");
		foreach($arrQualityParameters as $curQualityParameters)
		{
			$tpl->SetAllValues($curQualityParameters);
			$tpl->set_var("active_text", "");
			if(isset($curQualityParameters["isactive"]) && trim($curQualityParameters["isactive"]) != "")
				$tpl->set_var("active_text", $arrActive[$curQualityParameters["isactive"]]);
			$tpl->parse("FillQualityParametersList",true);
		}
	}

	$tpl->pparse('main',false);

?>
