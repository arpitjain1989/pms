<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('admin_approved_lwp_form_add.html','main_container');

	$PageIdentifier = "AdminApprovedLWPForm";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Add Admin Approved LWP Form");
	$breadcrumb = '<li><a href="admin_approved_lwp_form.php">Manage Admin Approved LWP Form</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add Admin Approved LWP Form</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.leave.php');
	include_once('includes/class.employee.php');
	include_once('includes/class.attendance.php');

	$objLeave = new leave();
	$objEmployee = new employee();
	$objAttendance = new attendance();

	if(isset($_POST['hdnaction']) && $_POST['hdnaction'] == 'AdminApprovedLWPAdd')
	{
		$insertdata = $objLeave->fnInserAdminApprovedLwpForm($_POST);

		if($insertdata == -1)
		{
			header("Location: admin_approved_lwp_form.php?info=earlyerr");
			exit;
		}
		else if($insertdata == -2)
		{
			header("Location: admin_approved_lwp_form.php?info=errsm");
			exit;
		}
		else if($insertdata == -3)
		{
			header("Location: admin_approved_lwp_form.php?info=aerr");
			exit;
		}
		else if($insertdata == -4)
		{
			header("Location: admin_approved_lwp_form.php?info=existerr");
			exit;
		}
		else if($insertdata == -5)
		{
			header("Location: admin_approved_lwp_form.php?info=existerr1");
			exit;
		}
		else if($insertdata != '')
		{
			$leaveDetails = $objLeave->fnGetAdminApprovedLwpById($insertdata);

			$curEmployee = $objEmployee->fnGetEmployeeDetailById($leaveDetails['employee_id']);
			$Subject = 'Admin Approved LWP application';

			$addedby = "";
			if($_SESSION["usertype"] == "admin")
				$addedby = "LMS Admin";
			else if($_SESSION["usertype"] == "employee")
				$addedby = $_SESSION["displayname"];

			$userMailContent = "Dear Admin, <br /><br />".$addedby." has added a ".$leaveDetails["leave_title"]." for ".$leaveDetails["employee_name"]." for Start Date ".$leaveDetails['lwp_date'].",End Date".$leaveDetails['lwp_date_to'].".<br/>Reason for adding leave is : ".$leaveDetails['reason']."<br><br>Regards,<br>".SITEADMINISTRATOR;

			sendmail("admin@transformsolution.net",$Subject,$userMailContent);

			header("Location: admin_approved_lwp_form.php?info=succ");
			exit;
		}
	}

	/* Fill users */
	$tpl->set_var("FillLeaveForBlock","");
	$arrEmployees = $objEmployee->fnGetAllEmployeesDetails(0);
	if(count($arrEmployees) > 0)
	{
		foreach($arrEmployees as $curEmployees)
		{
			$tpl->set_var("leave_form_id", $curEmployees["id"]);
			$tpl->set_var("leave_form_name", $curEmployees["name"]);

			$tpl->parse("FillLeaveForBlock", true);
		}
	}

	/* Fill leave types */
	$tpl->set_var("FillLWPTYPESBlock","");
	$arrLwp = $objLeave->fnGetLwpAndSpecialLeaveType();
	if(count($arrLwp) > 0)
	{
		foreach($arrLwp as $curLwp)
		{
			$tpl->set_var("lwp_type_id",$curLwp["id"]);
			$tpl->set_var("lwp_type_title",$curLwp["title"]);

			$tpl->parse("FillLWPTYPESBlock",true);
		}
	}

	$tpl->pparse('main',false);
?>
