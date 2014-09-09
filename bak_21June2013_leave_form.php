<?php 
	include('common.php');
	$tpl = new Template($app_path);
	$tpl->load_file('template.html','main');
	$tpl->load_file('leave_form.html','main_container');

	$PageIdentifier = "LeaveForm";
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
	$arrLeaveForm = $objLeave->fnGetAllLeaveForm($_SESSION['id']);
	//echo '<pre>';print_r($arrLeaveForm);

	$message = "";
	$messageClass = "";
	
	$tpl->set_var("DisplayMessageBlock","");
	
	if(isset($_REQUEST['info']))
	{
		switch($_REQUEST["info"])
		{
			case 'succ':
				$messageClass = "alert-success";
				$message = "Leave Form inserted successfully.";
				break;
			case 'update':
				$messageClass = "alert-error";
				$message = "Leave Form updated successfully.";
				break;
			case 'delete':
				$messageClass = "alert-success";
				$message = "Leave Form deleted successfully.";
				break;
			case 'exist':
				$messageClass = "alert-error";
				$message = "Leave For the date already exists.";
				break;
			case 'shift':
				$messageClass = "alert-error";
				$message = "Error while adding leave, shift movement added.";
				break;
			case 'pendingerr':
				$messageClass = "alert-error";
				$message = "You have a shift movement pending. Cannot add another shift movement.";
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
			header("Location: leave_form.php?info=delete");
		}
	}

	$tpl->set_var("FillLeaveFormValues","");
	foreach($arrLeaveForm as $arrLeaveFormvalue)
	{
		if($arrLeaveFormvalue['isemergency'] == 0)
		{
			$tpl->set_var("emergency_leave",'No');
		}
		else
		{
			$tpl->set_var("emergency_leave",'Yes');
		}

		$arrStatus = array("0" => "Pending", "1" => "Approved", "2" => "Unapproved");

		$tpl->SetAllValues($arrLeaveFormvalue);
		$newStartDate = date("d-m-Y", strtotime($arrLeaveFormvalue['start_date']));
		$newEndDate = date("d-m-Y", strtotime($arrLeaveFormvalue['end_date']));

		$tpl->set_var('teamleader_status',$arrStatus[$arrLeaveFormvalue['status']]);
		$tpl->set_var('manager_status',$arrStatus[$arrLeaveFormvalue['status_manager']]);

		$tpl->set_var('startdate',$newStartDate);
		$tpl->set_var('enddate',$newEndDate);
		$tpl->parse("FillLeaveFormValues",true);
	}

	$tpl->pparse('main',false);
?>
