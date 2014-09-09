<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('time_compensation_view.html','main_container');

	$PageIdentifier = "LateCommingCompensation";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Compensation");
	$breadcrumb = '<li><a href="time_compensation_list.php">Manage Compensation</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Compensation</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.attendance.php');
	
	$objAttendance = new attendance();
	
	$tpl->set_var("DisplayCompensationInformationBlock","");
	$tpl->set_var("DisplayNoCompensationtBlock","");
	$tpl->set_var("DisplayDelegatedReportingHead","");
	$tpl->set_var("DisplayReportingHead","");
	
	if(isset($_REQUEST["id"]))
	{
		$CompensationInfo = $objAttendance->fnUserCompensationById($_REQUEST["id"]);
		
		if(count($CompensationInfo) > 0)
		{
			$tpl->SetAllValues($CompensationInfo);
			
			$tpl->parse("DisplayReportingHead",false);
			if($CompensationInfo["delegatedtl_id"] != "0" && $CompensationInfo["delegatedtl_id"] != "")
			{
				$tpl->parse("DisplayDelegatedReportingHead",false);
			}
			
			$tpl->parse("DisplayCompensationInformationBlock",false);
		}
		else
		{
			$tpl->parse("DisplayNoCompensationtBlock",false);
		}
	}
	else
	{
		$tpl->parse("DisplayNoCompensationtBlock",false);
	}
	
	$tpl->pparse('main',false);
?>
