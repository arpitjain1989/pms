<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('holiday.html','main_container');

	$PageIdentifier = "Holidays";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Add Holiday");
	$breadcrumb = '<li><a href="holiday_list.php">Manage Holiday</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add Holiday</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.holidays.php');

	$objHolidays = new holidays();

	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "update" && isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		$holiday = $objHolidays->fnGetHolidayById($_REQUEST["id"]);
		if(count($holiday) > 0)
		{
			$tpl->SetAllValues($holiday);
		}
	}

	if(isset($_POST["action"]) && trim($_POST["action"]) == "Holiday")
	{
		$holiday_status = $objHolidays->fnSaveHolidays($_POST);

		if($holiday_status == 1)
		{
			header("Location: holiday_list.php?info=success");
			exit;
		}
		else if($holiday_status == 0)
		{
			header("Location: holiday_list.php?info=err");
			exit;
		}
	}

	$tpl->pparse('main',false);
?>
