<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('holiday_list.html','main_container');

	$PageIdentifier = "Holidays";
	include_once('userrights.php');
	
	$tpl->set_var("mainheading","Manage Holidays");
	$breadcrumb = '<li class="active">Manage Holidays</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.holidays.php');

	$tpl->set_var("DisplayMessageBlock","");
	
	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'success':
				$messageClass = "alert-success";
				$message = "Holiday added successfully";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Holiday already added. Cannot add again.";
				break;
		}

		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);	
		}
	}
	
	$objHolidays = new holidays();
	$arrHolidays = $objHolidays->fnGetAllHolidays();

	$tpl->set_var("FillHolidayList","");
	if(count($arrHolidays) >0)
	{
		foreach($arrHolidays as $curHoliday)
		{	
			$tpl->SetAllValues($curHoliday);
			$tpl->parse("FillHolidayList",true);
		}
	}

	$tpl->pparse('main',false);

?>
