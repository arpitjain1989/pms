<?php
	include('common.php');

	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('report_head_history.html','main_container');

	$PageIdentifier = "ReportingHeadHistory";
	include_once('userrights.php');

	$curdate = date('Y-m-d');
	$tpl->set_var("currentdate",$curdate);

	$tpl->set_var("mainheading","Add / Edit Reporting Head History");
	$breadcrumb = '<li><a href="attendance.php">Manage Reporting Head History</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add / Edit Reporting Head History</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.employee.php');

	$objEmployee = new employee();

	$tpl->set_var("FillEmployeeValues","");
	$tpl->set_var("DisplayMessageBlock","");
	$tpl->set_var("InnerBlock","");

	//echo '<pre>'; print_r($_REQUEST); die;
	if(isset($_REQUEST['employee']) && $_REQUEST['employee'] != '')
	{
		$tpl->set_var("employee",$_REQUEST['employee']);
		$emp_id = $_REQUEST['employee'];
	}
	else
	{
		if(isset($_REQUEST['id']) && $_REQUEST['id'] !='')
		{
			$emp_id = $_REQUEST['id'];
			$tpl->set_var("employee",$_REQUEST['id']);
		}
	}
	
	if((isset($_REQUEST['employee']) && $_REQUEST['employee'] != '') || (isset($_REQUEST['id']) && $_REQUEST['id'] != ''))
	{
		$arrEmployeeData1 = $objEmployee->fnGetReportingHeadHistory($emp_id);
		//echo '<pre>'; print_r($arrEmployeeData1);
		if(count($arrEmployeeData1) > 0)
		{
			foreach($arrEmployeeData1 as $empl)
			{
				$tpl->set_var("emHistoryId",$empl['his_id']);
				$tpl->set_var("employeeName",$empl['employeeName']);
				$tpl->set_var("reportingHead",$empl['reportingHead']);
				$tpl->set_var("effectiveDate",$empl['effective_date']);
				$tpl->set_var("designation",$empl['emp_designation']);
				$tpl->parse("FillEmployeeValues","true");
			}
		}
		$tpl->parse("InnerBlock",true);
	}
	
	if(isset($_REQUEST['id']))
	{
		$tpl->set_var('userid',"$_REQUEST[id]");
	}
	
	/*if(isset($_POST['hdnaction']) && $_POST['hdnaction'] == 'add')
	{
		$insertdata = $objEmployee->fnInsertAttendance($_POST);
		if($insertdata)
		{
			header("Location: report_head_history.php?info=succ");
			exit;
		}
		else
		{
			header("Location: report_head_history.php?info=err");
			exit;
		}
	}*/

	//print_r($_REQUEST);

	/*if(isset($_REQUEST['action']) && $_REQUEST['action']=='update')
	{
		$attendanceDate = $curdate = $_REQUEST['date'];
		$tpl->set_var('currentdate',$attendanceDate);
		if(isset($_REQUEST['id']))
		{
			$arrAttendances = $objEmployee->fnGetAttendanceById($_REQUEST['id']);
			if(count($arrAttendances) > 0)
			{
				$tpl->SetAllValues($arrAttendances);
			}
		}
		$tpl->set_var('action','update');
	}*/

	$arrEmployees = $objEmployee->fnGetAllEmployee();
	$tpl->set_var('EmployeeValues','');
	if(count($arrEmployees)> 0)
	{
		foreach($arrEmployees as $Employees)
		{
			$tpl->set_var("empl_id",$Employees['id']);
			$tpl->set_var("empl_name",$Employees['name']);
			$tpl->parse('EmployeeValues',true);
		}
	}
	
	$tpl->pparse('main',false);
?>
