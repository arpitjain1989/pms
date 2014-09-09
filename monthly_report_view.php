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

		xlsWriteLabel(0,0,"Employee Code");
		xlsWriteLabel(0,1,"Name");
		xlsWriteLabel(0,2,"Reporting Head");
		xlsWriteLabel(0,3,"P");
		xlsWriteLabel(0,4,"Total PLT");
		xlsWriteLabel(0,5,"Break Exceed");
		xlsWriteLabel(0,6,"PPL");
		xlsWriteLabel(0,7,"UHL");
		xlsWriteLabel(0,8,"PHL");
		xlsWriteLabel(0,9,"WO");
		xlsWriteLabel(0,10,"PH");
		xlsWriteLabel(0,11,"SPL");
		xlsWriteLabel(0,12,"HA");
		xlsWriteLabel(0,13,"HLWP");
		xlsWriteLabel(0,14,"A");
		xlsWriteLabel(0,15,"PLWP");
		xlsWriteLabel(0,16,"ULWP");
		xlsWriteLabel(0,17,"UPL");
		xlsWriteLabel(0,18,"TOTAL");
		xlsWriteLabel(0,19,"Pay Days");
		xlsWriteLabel(0,20,"Opening Leaves Balance(PL)");
		xlsWriteLabel(0,21,"Leaves Earn(PL)");
		xlsWriteLabel(0,22,"Leave Consumed(TOTAL)");
		xlsWriteLabel(0,23,"Closing Leaves Balance(PL)");

		$xlsRow = 1;

		if(is_array($getAllCalculation) && count($getAllCalculation) > 0)
		{
			foreach($getAllCalculation as $calculations)
			{
				xlsWriteLabel($xlsRow,0,$calculations["employee_code"]);
				xlsWriteLabel($xlsRow,1,$calculations["name"]);
				xlsWriteLabel($xlsRow,2,$calculations["reporting_head"]);
				xlsWriteLabel($xlsRow,3,$calculations["present"]);
				xlsWriteLabel($xlsRow,4,$calculations["total_plt"]);
				xlsWriteLabel($xlsRow,5,$calculations["break_exceeds"]);
				xlsWriteLabel($xlsRow,6,$calculations["ppl"]);
				xlsWriteLabel($xlsRow,7,$calculations["uhl"]);
				xlsWriteLabel($xlsRow,8,$calculations["phl_taken"]);
				xlsWriteLabel($xlsRow,9,$calculations["wo"]);
				xlsWriteLabel($xlsRow,10,$calculations["ph"]);
				xlsWriteLabel($xlsRow,11,$calculations["spl"]);
				xlsWriteLabel($xlsRow,12,$calculations["ha"]);
				xlsWriteLabel($xlsRow,13,$calculations["hlwp"]);
				xlsWriteLabel($xlsRow,14,$calculations["abs"]);
				xlsWriteLabel($xlsRow,15,$calculations["plwp"]);
				xlsWriteLabel($xlsRow,16,$calculations["ulwp"]);
				xlsWriteLabel($xlsRow,17,$calculations["upl"]);
				xlsWriteLabel($xlsRow,18,$calculations["total_present"]);
				xlsWriteLabel($xlsRow,19,$calculations["pay_days"]);
				xlsWriteLabel($xlsRow,20,$calculations["opening_leave"]);
				xlsWriteLabel($xlsRow,21,$calculations["leave_earns"]);
				xlsWriteLabel($xlsRow,22,$calculations["pl_taken"]);
				xlsWriteLabel($xlsRow,23,$calculations["closing_balance"]);

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
			//$total_earn = $calculations['leave_earns'] + $calculations['ph_carry_forward'];
			
			$total_earn = $calculations['leave_earns'];
			$tpl->set_var('total_earn_leave',$total_earn);
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
