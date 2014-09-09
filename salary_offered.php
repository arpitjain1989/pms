<?php 
	include('common.php');
	$tpl = new Template($app_path);
	//echo '<pre>'; print_r($_SESSION); die;

	$tpl->load_file('template.html','main');
	$tpl->load_file('salary_offered.html','main_container');

	$PageIdentifier = "SalaryOffered";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Salary Offered");
	$breadcrumb = '<li class="active">Manage Salary Offered</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.salary_offered.php');
	
	$objSalaryOffered = new salary_offered();
	$arrRCTSalaryOffered = $objSalaryOffered->fnGetAllSalaryOffered();
	//echo '<pre>'; print_r($arrRCTSalaryOffered);

	$message = "";
	$messageClass = "";
	$tpl->set_var("DisplayMessageBlock","");
	//echo 'hello';
	if(isset($_REQUEST['info']))
	{

		switch($_REQUEST["info"])
		{
			case 'succ':
				$messageClass = "alert-success";
				$message = "Salary Offered inserted successfully.";
				break;
			case 'update':
				$messageClass = "alert-success";
				$message = "Salary Offered updated successfully.";
				break;
			case 'delete':
				$messageClass = "alert-success";
				$message = "No records found.";
				break;
			case 'exist':
				$messageClass = "alert-success";
				$message = "Salary Offered deleted successfully.";
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
		$delteRCTDivision = $objSalaryOffered->fnDeleteRCTDivision($_POST);
		if($delteRCTDivision)
		{
			header("Location: salary_offered.php?info=delete");
		}
		else
		{
			
		}
	}*/
	
	$tpl->set_var("FillRctSheetValues","");
	foreach($arrRCTSalaryOffered as $arrSalaryOffered)
	{
		$tpl->SetAllValues($arrSalaryOffered);
		$tpl->parse("FillRctSheetValues",true);
	}

	$tpl->pparse('main',false);
?>
