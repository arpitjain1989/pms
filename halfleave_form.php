<?php 
	include('common.php');
	$tpl = new Template($app_path);
	$tpl->load_file('template.html','main');
	$tpl->load_file('halfleave_form.html','main_container');

	$PageIdentifier = "HalfLeaveForm";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Leave Form");
	$breadcrumb = '<li class="active">Manage Leave Form</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	if(isset($_SESSION["usertype"]) && trim($_SESSION["usertype"]) == "admin")
	{
		/* If logged in as admin, do not show add leave form option */
		$tpl->set_var("LeaveFormAdd","");
	}
	
	include_once('includes/class.leave.php');
	
	$objLeave = new leave();
	$arrLeaveForm = $objLeave->fnGetAllHalfLeaveForm($_SESSION['id']);

	$message = "";
	$messageClass = "";
	$tpl->set_var("DisplayMessageBlock","");
	
	if(isset($_REQUEST['info']))
	{
		switch($_REQUEST["info"])
		{
			case 'succ':
				$messageClass = "alert-success";
				$message = "Half leave form inserted successfully.";
				break;
			case 'update':
				$messageClass = "alert-success";
				$message = "Half leave form updated successfully.";
				break;
			case 'delete':
				$messageClass = "alert-success";
				$message = "Half leave form deleted successfully.";
				break;
			case 'exist':
				$messageClass = "alert-error";
				$message = "Leave For the date already exists.";
				break;
			case 'admexist':
				$messageClass = "alert-error";
				$message = "Approved LWP added by admin.";
				break;
			case 'shift':
				$messageClass = "alert-error";
				$message = "Error while adding leave, shift movement added.";
				break;
			case 'earlyerr':
				$messageClass = "alert-error";
				$message = "Cannot add leave so much in advance.";
				break;
		}
		
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);
			
			$tpl->parse("DisplayMessageBlock",false);	
		}

	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] = 'delete')
	{
		$deleteLeaveForm = $objLeave->fnDeleteLeaveForm($_POST);
		if($deleteLeaveForm)
		{
			header("Location: halfleave_form.php?info=delete");
		}
	}
	
	$tpl->set_var("FillLeaveFormValues","");
	foreach($arrLeaveForm as $arrLeaveFormvalue)
	{
		$tpl->SetAllValues($arrLeaveFormvalue);
		$newStartDate = date("d-m-Y", strtotime($arrLeaveFormvalue['start_date']));
		//$newEndDate = date("d-m-Y", strtotime($arrLeaveFormvalue['end_date']));
		$tpl->set_var('startdate',$newStartDate);
		//$tpl->set_var('enddate',$newEndDate);
		$tpl->parse("FillLeaveFormValues",true);
	}

	$tpl->pparse('main',false);
?>
