<?php 
	include('common.php');
	$tpl = new Template($app_path);
	$tpl->load_file('template.html','main');
	$tpl->load_file('emergency_leave_list.html','main_container');

	$PageIdentifier = "EmergencyLeaveForm";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Emergency Leave Form");
	$breadcrumb = '<li class="active">Manage Emergency Leave Form</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	if(isset($_SESSION["usertype"]) && trim($_SESSION["usertype"]) == "admin")
	{
		/* If logged in as admin, do not show add leave form option */
		$tpl->set_var("EmergencyLeaveBlock","");
	}
	
	include_once('includes/class.leave.php');
	
	$objLeave = new leave();
	$arrLeaveForm = $objLeave->fnGetAllEmergencyLeaveForm($_SESSION['id']);

	$message = "";
	$messageClass = "";
	$tpl->set_var("DisplayMessageBlock","");
	if(isset($_REQUEST['info']))
	{
		switch($_REQUEST["info"])
		{
			case 'succ':
				$messageClass = "alert-success";
				$message = "Emergency leave form added successfully.";
				break;
			case 'statsucc':
				$messageClass = "alert-success";
				$message = "Emergency leave status changed.";
				break;
			case 'norec':
				$messageClass = "alert-success";
				$message = "No records found.";
				break;
			case 'exist':
				$messageClass = "alert-error";
				$message = "Emergency leave already exist.";
				break;
			case 'admexist':
				$messageClass = "alert-error";
				$message = "Approved LWP added by admin.";
				break;
			case 'shift':
				$messageClass = "alert-error";
				$message = "Error while adding leave, shift movement added.";
				break;
			case 'half':
				$messageClass = "alert-error";
				$message = "Half day leave already exist.";
				break;
		}
		
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);
			
			$tpl->parse("DisplayMessageBlock",false);	
		}
		

	}

	$tpl->set_var("FillLeaveFormValues","");
	foreach($arrLeaveForm as $arrLeaveFormvalue)
	{
		$tpl->SetAllValues($arrLeaveFormvalue);
		$newStartDate = date("d-m-Y", strtotime($arrLeaveFormvalue['start_date']));
		$newEndDate = date("d-m-Y", strtotime($arrLeaveFormvalue['end_date']));
		$tpl->set_var('startdate',$newStartDate);
		$tpl->set_var('enddate',$newEndDate);
		$tpl->parse("FillLeaveFormValues",true);
	}

	$tpl->pparse('main',false);
?>
