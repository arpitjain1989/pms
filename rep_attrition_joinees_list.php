<?php 
	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('rep_attrition_joinees_list.html','main_container');

	$PageIdentifier = "HrReportAttrition";
	include_once('userrights.php');

	$curDate = Date('Y-m-d');
	$curYear = Date('Y');

	$arrYear = array($curYear+1, $curYear, $curYear-1);

	$tpl->set_var("mainheading","Attrition Report");
	$breadcrumb = '<li><a href="rep_attrition.php">Attrition Report</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Attrition Report : Joinees</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.employee.php');
	$objEmployee = new employee();

	$tpl->set_var("DisplayReportingHeadHiddenBlock","");
	$tpl->set_var("DisplayReportingHeadBlock","");

	$arrJoiners = $objEmployee->fnGetJoinersYTD($_SESSION["SearchAttrition"]["reporting_head"],$_SESSION["SearchAttrition"]["month"],$_SESSION["SearchAttrition"]["year"]);

	$tpl->set_var("DisplayEmployeeJoineesBlock","");
	if(count($arrJoiners) > 0)
	{
		foreach($arrJoiners as $curJoiners)
		{
			$tpl->set_var("doj",$curJoiners['date_of_joining']);
			$tpl->set_var("e_code",$curJoiners['employee_code']);
			$tpl->set_var("e_name",$curJoiners['name']);

			$tpl->parse("DisplayEmployeeJoineesBlock",true);
		}
	}

	$tpl->pparse('main',false);
?>
