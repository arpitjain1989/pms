<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('admin_half_leave_form_add.html','main_container');

	$PageIdentifier = "AdminHalfLeaveForm";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Add / Edit Half Leave Form");
	$breadcrumb = '<li><a href="admin_half_leave_form.php">Manage Admin Half Leave Form</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add Half Leave Form</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.leave.php');
	include_once('includes/class.employee.php');
	include_once('includes/class.attendance.php');

	$objLeave = new leave();
	$objEmployee = new employee();
	$objAttendance = new attendance();
	
	if(isset($_POST["hdnaction"]) && $_POST["hdnaction"] == "AdminHalfLeaveAdd")
	{
		$insertdata = $objLeave->fnInserAdminHalfLeaveForm($_POST);
		
		if($insertdata == -1)
		{
			header("Location: admin_half_leave_form.php?info=earlyerr");
			exit;
		}
		else if($insertdata == -2)
		{
			header("Location: admin_half_leave_form.php?info=admexist");
			exit;
		}
		else if($insertdata == -3)
		{
			header("Location: admin_half_leave_form.php?info=aerr");
			exit;
		}
		else if($insertdata == -5)
		{
			header("Location: admin_half_leave_form.php?info=leave");
			exit;
		}
		else if($insertdata != '')
		{
			$leaveDetails = $objLeave->fnGetHalfLeaveDetailsById($insertdata);
			$curEmployee = $objEmployee->fnGetEmployeeDetailById($leaveDetails['employee_id']);
			$Subject = 'Admin Halfday Leave application';

			$addedby = "";
			if($_SESSION["usertype"] == "admin")
				$addedby = "LMS Admin";
			else if($_SESSION["usertype"] == "employee")
				$addedby = $_SESSION["displayname"];

			$arrHalfDayFor = array("1"=>"First Half", "2"=>"Second Half");

			$userMailContent = "Dear Admin, <br /><br />".$addedby." has added a half day leave for ".$curEmployee["name"]." for ".$leaveDetails['start_date']." for ".$arrHalfDayFor[$leaveDetails['halfdayfor']].".<br/>Reason for adding leave is : ".$leaveDetails['reason']."<br><br>Regards,<br>".SITEADMINISTRATOR;

			sendmail("admin@transformsolution.net",$Subject,$userMailContent);
			
			header("Location: admin_half_leave_form.php?info=succ");
			exit;
		}
		else
		{
			header("Location: admin_half_leave_form.php?info=shift");
			exit;
		}
	}

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

	$tpl->pparse('main',false);
?>
