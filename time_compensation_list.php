<?php 

	include('common.php');
	$tpl = new Template($app_path);
	$tpl->load_file('template.html','main');
	$tpl->load_file('time_compensation_list.html','main_container');

	$PageIdentifier = "LateCommingCompensation";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Late comming Compensation");
	$breadcrumb = '<li class="active">Manage Compensation</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	if(isset($_SESSION["usertype"]) && trim($_SESSION["usertype"]) == "admin")
	{
		/* If logged in as admin, do not show add leave form option */
		$tpl->set_var("AddCompensation","");
	}
		
	$message = "";
	$messageClass = "";
	
	$tpl->set_var("DisplayMessageBlock","");
	
	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'success':
				$messageClass = "alert-success";
				$message = "Compensation added successfully";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Cannot compensate after 7 days.";
				break;
			case 'nopending':
				$messageClass = "alert-error";
				$message = "No Compensations are pending or Compensation request is pending";
				break;
		}
		
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);
			
			$tpl->parse("DisplayMessageBlock",false);	
		}
	}
	
	include_once('includes/class.attendance.php');
	
	$objAttendance = new attendance();
	
	$arrCompensation = $objAttendance->fnGetAllCompensationsByUser($_SESSION["id"]);
	
	$tpl->set_var("FillCompensationBlock","");
	if(count($arrCompensation) > 0)
	{
		foreach($arrCompensation as $CompensationInfo)
		{
			$tpl->SetAllValues($CompensationInfo);
			$tpl->parse("FillCompensationBlock",true);
		}
	}

	$tpl->pparse('main',false);
?>
