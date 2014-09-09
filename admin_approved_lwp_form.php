<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('admin_approved_lwp_form.html','main_container');

	$PageIdentifier = "AdminApprovedLWPForm";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Admin Approved LWP");
	$breadcrumb = '<li class="active">Manage Admin Approved LWP</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.leave.php');

	$objLeave = new leave();

	$message = "";
	$messageClass = "";

	$tpl->set_var("DisplayMessageBlock","");

	if(isset($_REQUEST['info']))
	{
		switch($_REQUEST["info"])
		{
			case 'earlyerr':
				$messageClass = "alert-error";
				$message = "Cannot add leave so much in advance.";
				break;
			case 'errsm':
				$messageClass = "alert-error";
				$message = "Error while adding leave, shift movement added.";
				break;
			case 'aerr':
				$messageClass = "alert-error";
				$message = "Error while adding leave, attendance already added.";
				break;
			case 'existerr':
				$messageClass = "alert-error";
				$message = "Error while adding leave, leave already added.";
				break;
			case 'succ':
				$messageClass = "alert-success";
				$message = "Leave inserted successfully.";
				break;
			case 'existerr1':
			$messageClass = "alert-error";
				$message = "Can't Add Lwp In Advance.";
				break;
		}
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	$arrAdminLeaveForm = $objLeave->fnGetAllAdminApprovedLWP();

	$tpl->set_var("FillAdminLeaveFormValues","");
	if(count($arrAdminLeaveForm) > 0)
	{
		foreach($arrAdminLeaveForm as $curAdminLeaveForm)
		{
			$tpl->SetAllValues($curAdminLeaveForm);
			if(!empty($curAdminLeaveForm['lwp_date_to']))
			{
			$dt = new DateTime($curAdminLeaveForm['lwp_date_to']);
			$dateTo = $dt->format('d-m-Y');
			$tpl->set_var("date_to",$dateTo);
			}
			$tpl->parse("FillAdminLeaveFormValues",true);
		}
	}

	$tpl->pparse('main',false);

?>
