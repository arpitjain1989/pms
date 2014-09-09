<?php

	include('common.php');

	//error_reporting(E^ALL);
	set_time_limit(0);

	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('monthly_report.html','main_container');
	
		
	include_once('includes/class.calculation.php');

	$PageIdentifier = "MonthlyReport";
	include_once('userrights.php');
	
	$objCalculation = new calculation();
	//print_r($_POST);die;
	$tpl->set_var("mainheading","Attendance Report");
	$breadcrumb = '<li class="active">Attendance Report</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	$curDate = Date('Y-m-d');
	$curYear = Date('Y');

	$arrYear = array($curYear, $curYear-1);


	if(isset($_POST['month']) && $_POST['month'] != '')
	{
		$_SESSION["AttendanceMonthlyReport"]["month"] = $_POST['month'];
	}

	if(isset($_POST['year']) && $_POST['year'] != '')
	{
		$_SESSION["AttendanceMonthlyReport"]["year"] = $_POST['year'];
	}

	if(isset($_SESSION["AttendanceMonthlyReport"]["year"]) && $_SESSION["AttendanceMonthlyReport"]["year"] != "")
		$year = $_SESSION["AttendanceMonthlyReport"]["year"];
	else
		$year = Date('Y');

	if(isset($_SESSION["AttendanceMonthlyReport"]["month"]) && $_SESSION["AttendanceMonthlyReport"]["month"] != "")
		$month = $_SESSION["AttendanceMonthlyReport"]["month"];
	else
		$month = Date('m');


	$tpl->set_var("premonth", $month);
	$tpl->set_var("preyear", $year);

	$getAllCalculation = $objCalculation->fnGetMonthlyReport($month,$year);

	/* Export to excel */
	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "export") 
	{
		$filename = "Attendance Monthly Report for ".$year."-".$month." - ".Date('Y-m-d H:i').".xls";
	
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");;
		header("Content-Disposition: attachment;filename=".$filename);
		header("Content-Transfer-Encoding: binary ");
		
		xlsBOF();

		xlsWriteLabel(0,0,"Name");
		xlsWriteLabel(0,1,"Reporting Head");
		xlsWriteLabel(0,2,"P");
		xlsWriteLabel(0,3,"Total PLT");
		xlsWriteLabel(0,4,"Break Exceed");
		xlsWriteLabel(0,5,"PPL");
		xlsWriteLabel(0,6,"UHL");
		xlsWriteLabel(0,7,"PHL");
		xlsWriteLabel(0,8,"WO");
		xlsWriteLabel(0,9,"PH");
		xlsWriteLabel(0,10,"HA");
		xlsWriteLabel(0,11,"HLWP");
		xlsWriteLabel(0,12,"A");
		xlsWriteLabel(0,13,"PLWP");
		xlsWriteLabel(0,14,"ULWP");
		xlsWriteLabel(0,15,"UPL");
		xlsWriteLabel(0,16,"TOTAL");
		xlsWriteLabel(0,17,"Pay Days");
		xlsWriteLabel(0,18,"Opening Leaves Balance(PL)");
		xlsWriteLabel(0,19,"Leaves Earn(PL)");
		xlsWriteLabel(0,20,"Leave Consumed(TOTAL)");
		xlsWriteLabel(0,21,"Closing Leaves Balance(PL)");

		$xlsRow = 1;

		if(is_array($getAllCalculation) && count($getAllCalculation) > 0)
		{
			foreach($getAllCalculation as $calculations)
			{
				xlsWriteLabel($xlsRow,0,$calculations["name"]);
				xlsWriteLabel($xlsRow,1,$calculations["reporting_head"]);
				xlsWriteLabel($xlsRow,2,$calculations["present"]);
				xlsWriteLabel($xlsRow,3,$calculations["total_plt"]);
				xlsWriteLabel($xlsRow,4,$calculations["break_exceeds"]);
				xlsWriteLabel($xlsRow,5,$calculations["ppl"]);
				xlsWriteLabel($xlsRow,6,$calculations["uhl"]);
				xlsWriteLabel($xlsRow,7,$calculations["phl_taken"]);
				xlsWriteLabel($xlsRow,8,$calculations["wo"]);
				xlsWriteLabel($xlsRow,9,$calculations["ph"]);
				xlsWriteLabel($xlsRow,10,$calculations["ha"]);
				xlsWriteLabel($xlsRow,11,$calculations["hlwp"]);
				xlsWriteLabel($xlsRow,12,$calculations["abs"]);
				xlsWriteLabel($xlsRow,13,$calculations["plwp"]);
				xlsWriteLabel($xlsRow,14,$calculations["ulwp"]);
				xlsWriteLabel($xlsRow,15,$calculations["upl"]);
				xlsWriteLabel($xlsRow,16,$calculations["total_present"]);
				xlsWriteLabel($xlsRow,17,$calculations["pay_days"]);
				xlsWriteLabel($xlsRow,18,$calculations["opening_leave"]);
				xlsWriteLabel($xlsRow,19,$calculations["leave_earns"]);
				xlsWriteLabel($xlsRow,20,$calculations["pl_taken"]);
				xlsWriteLabel($xlsRow,21,$calculations["closing_balance"]);

				$xlsRow++;
			}
		}
		else
		{
			xlsWriteLabel($xlsRow,1,"No Records");
		}

		xlsEOF();

		exit;
	}
	
	$tpl->set_var("FillReport","");
	if(count($getAllCalculation) > 0 )
	{
		foreach($getAllCalculation as $calculations)
		{
			$tpl->setAllValues($calculations);
			$tpl->parse("FillReport",true);
		}
	}


	$tpl->set_var("DisplayYearBlock","");
	if(count($arrYear) > 0)
	{
		foreach($arrYear as $curYr)
		{
			$tpl->set_var("curyr",$curYr);
			$tpl->parse("DisplayYearBlock",true);
		}
	}

	
	$tpl->pparse('main',false);
?>
