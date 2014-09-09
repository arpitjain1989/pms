<?php 

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('time_compensation.html','main_container');

	$PageIdentifier = "LateCommingCompensation";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Add Late comming Compensation");
	$breadcrumb = '<li><a href="time_compensation_list.php">Manage Compensation</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add   Compensation</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.attendance.php');
	
	$objAttendance = new attendance();
	
	if(isset($_POST["action"]) && trim($_POST["action"]) == "TimeCompensation")
	{
		if($objAttendance->fnSaveCompensation($_POST))
		{
			header("Location: time_compensation_list.php?info=success");
			exit;
		}
		else
		{
			header("Location: time_compensation_list.php?info=err");
			exit;
		}
	}

	$tpl->set_var("currentdate",Date('Y-m-d'));

	$arrCompensation = $objAttendance->fnGetUnCompensatedExceedByUser($_SESSION["id"]);

	$tpl->set_var("FillCompensationDate","");
	$exceedFor = array("1"=>"Late Comming", "2"=>"Break exceed");

	if(count($arrCompensation) > 0)
	{
		foreach($arrCompensation as $currentInfo)
		{
			$tpl->set_var("compensation_id",$currentInfo["attendanceid"]."-".$currentInfo["exceedfor"]);
			/*$tpl->set_var("compensation_date",$currentInfo["date"]."-".$exceedFor[$currentInfo["exceedfor"]]);*/

			$tpl->set_var("compensation_date",$currentInfo["date"]);
			
			$tpl->parse("FillCompensationDate",true);
		}
	}
	else
	{
		header("Location: time_compensation_list.php?info=nopending");
		exit;
	}

	/* Display hours */
	$tpl->set_var("FillHoursBlock","");
	for($i = 1; $i<13; $i++)
	{
		$tpl->set_var("hours",str_pad($i,2,'0',STR_PAD_LEFT));
		$tpl->parse("FillHoursBlock",true);
	}

	/* Display minutes */
	$tpl->set_var("FillMinutesBlock","");
	for($i = 0; $i<60; $i++)
	{
		$tpl->set_var("minutes",str_pad($i,2,'0',STR_PAD_LEFT));
		$tpl->parse("FillMinutesBlock",true);
	}
	
	$tpl->pparse('main',false);
?>
