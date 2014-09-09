<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('admin_approved_lwp_requests.html','main_container');

	$PageIdentifier = "AdminApprovedLWPRequest";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Admin Approved LWP Request");
	$breadcrumb = '<li class="active">Manage Admin Approved LWP Request</li>';
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
			case 'err':
				$messageClass = "alert-error";
				$message = "Leave not found. Error updating leave information.";
				break;
			case 'succa':
				$messageClass = "alert-success";
				$message = "Leave Approved successfully.";
				break;
			case 'succu':
				$messageClass = "alert-success";
				$message = "Leave Unapproved successfully.";
				break;
		}
		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	$arrAdminApprovedLwp = $objLeave->fnGetAllAdminApprovedLwpRequests();

	$tpl->set_var("FillAdminApprovedLwpValues","");
	if(count($arrAdminApprovedLwp) > 0)
	{
		foreach($arrAdminApprovedLwp as $curAdminApprovedLwp)
		{
			if(!empty($curAdminApprovedLwp['lwp_date_to']))
			{
			$dt = new DateTime($curAdminApprovedLwp['lwp_date_to']);
			$dateTo = $dt->format('d-m-Y');
			$tpl->set_var("date_to",$dateTo);
			}
			else
			{
			$tpl->set_var("date_to","");
			}
			$tpl->SetAllValues($curAdminApprovedLwp);
			$tpl->parse("FillAdminApprovedLwpValues",true);
		}
	}

	$tpl->pparse('main',false);

?>
