<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('holiday_view.html','main_container');

	$PageIdentifier = "Holidays";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Holiday");
	$breadcrumb = '<li><a href="holiday.php">Manage Holidays</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Holiday</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.holidays.php');
	
	$objHolidays = new holidays();
	
	$tpl->set_var("DisplayHolidayInformationBlock","");
	$tpl->set_var("DisplayNoHolidayBlock","");
	
	if(isset($_REQUEST['id']) && trim($_REQUEST['id']) != "")
	{
		$arrHolidays = $objHolidays->fnGetHolidayById($_REQUEST['id']);
		
		if(count($arrHolidays) > 0)
		{
			$tpl->SetAllValues($arrHolidays);
			$tpl->parse("DisplayHolidayInformationBlock",false);
		}
		else
		{
			$tpl->parse("DisplayNoHolidayBlock",false);
		}
	}
	else
	{
		$tpl->parse("DisplayNoHolidayBlock",false);
	}
	
	$tpl->pparse('main',false);
?>
