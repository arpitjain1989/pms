<?php

	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('late_coming_report.html','main_container');
	
	$PageIdentifier = "LateComingReport";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Late Coming Report");
	$breadcrumb = '<li class="active">Late Coming Report</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once("includes/class.attendance.php");
	
	$objAttendance = new attendance();

	$curYear = Date('Y');
	$curMonth = Date('m');

	/* Get current year and previous year */
	$arrYear = array($curYear, $curYear-1);

	/* Search late comings */
	if(isset($_POST["action"]) && trim($_POST["action"]) == "LateComingSearch")
	{
		$_SESSION["LateComingReport"]["month"] = $_POST["month"];
		$_SESSION["LateComingReport"]["year"] = $_POST["year"];
		$_SESSION["LateComingReport"]["viewall"] = $_POST["viewall"];
		
		header("Location: late_coming_report.php");
		exit;
	}
	
	if(!isset($_SESSION["LateComingReport"]["month"]))
		$_SESSION["LateComingReport"]["month"] = $curMonth;

	if(!isset($_SESSION["LateComingReport"]["year"]))
		$_SESSION["LateComingReport"]["year"] = $curYear;

	if(!isset($_SESSION["LateComingReport"]["viewall"]))
		$_SESSION["LateComingReport"]["viewall"] = 0;
	
	$year = $_SESSION["LateComingReport"]["year"];
	$month = $_SESSION["LateComingReport"]["month"];
	$viewall = $_SESSION["LateComingReport"]["viewall"];
	
	$tpl->set_var("year",$year);
	$tpl->set_var("month",$month);
	$tpl->set_var("viewall",$viewall);
	
	$lateComingInformation = $objAttendance->fnGetLateComingsByYearAndMonth($year, $month, $viewall);
	
	/* Export to excel */
	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "export") 
	{
		$filename = "LateCommingReportfor".$year."-".$month.".xls";
	
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header('Content-Type: text/html; charset=utf-8');
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");;
		header("Content-Disposition: attachment;filename=".$filename);
		header("Content-Transfer-Encoding: binary ");
		
		xlsBOF();

		xlsWriteLabel(0,0,"Employee Name");
		xlsWriteLabel(0,1,"Reporting Head");
		xlsWriteLabel(0,2,"Deduction");
		xlsWriteLabel(0,3,"Late Coming Date");
		xlsWriteLabel(0,4,"Late Time");

		$xlsRow = 1;

		if(is_array($lateComingInformation) && count($lateComingInformation) > 0)
		{
			foreach($lateComingInformation as $curUserInformation)
			{
				$totalLateComings = count($curUserInformation["lateinfo"]);
				if($totalLateComings  >= 3)
				{
					/* Remove 3 late comings for which deduction is not done */
					$deductedLateComings = $totalLateComings - 3;
					
					if($deductedLateComings <= 2)
					{
						/* deduct halfday for 2 days */
						$deduction = $deductedLateComings * 0.5;
					}
					else
					{
						/* deduct halfday for 2 days */
						$deduction = 2 * 0.5;
						
						/* deduct full days for the rest */
						$deductedLateComings = $deductedLateComings - 2;
						$deduction = $deduction + $deductedLateComings;
					}
				}
				else
				{
					$deduction = 0;
				}

				xlsWriteLabel($xlsRow,2,$deduction);

				if(count($curUserInformation["lateinfo"]) > 0)
				{
					foreach($curUserInformation["lateinfo"] as $curInfo)
					{
						xlsWriteLabel($xlsRow,0,$curUserInformation["name"]);
						xlsWriteLabel($xlsRow,1,$curUserInformation["teamleader"]);

						xlsWriteLabel($xlsRow,3,$curInfo["latedate"]);
						xlsWriteLabel($xlsRow,4,$curInfo["late_time"]);

						$xlsRow++;
					}
				}
				else
				{
					$xlsRow++;
				}
			}
		}
		else
		{
			xlsWriteLabel($xlsRow,1,"No Records");
		}

		xlsEOF();
		exit;
	}
	
	$tpl->set_var("DisplayLateComing","");
	$tpl->set_var("NoDisplayLateComing","");
	
	$tpl->set_var("FillLateComingInformation","");

	if(count($lateComingInformation) > 0)
	{
		foreach($lateComingInformation as $curUserInformation)
		{
			$tpl->set_var("employeename",$curUserInformation["name"]);
			$tpl->set_var("teamleader",$curUserInformation["teamleader"]);
			$totalLateComings = count($curUserInformation["lateinfo"]);
			
			if($totalLateComings  >= 3)
			{
				/* Remove 3 late comings for which deduction is not done */
				$deductedLateComings = $totalLateComings - 3;
				
				if($deductedLateComings <= 2)
				{
					/* deduct halfday for 2 days */
					$deduction = $deductedLateComings * 0.5;
				}
				else
				{
					/* deduct halfday for 2 days */
					$deduction = 2 * 0.5;
					
					/* deduct full days for the rest */
					$deductedLateComings = $deductedLateComings - 2;
					$deduction = $deduction + $deductedLateComings;
				}
			}
			else
			{
				$deduction = 0;
			}
			
			$tpl->set_var("deduction",$deduction);
			
			$tpl->set_var("FillLateComingDetailsInformation","");
			
			if($totalLateComings > 0)
			{
				$i = 1;
				foreach($curUserInformation["lateinfo"] as $curInfo)
				{
					$tpl->set_var("srno", $i++);
					$tpl->set_var("latecomingdate", $curInfo["latedate"]);
					$tpl->set_var("totallatetime", $curInfo["late_time"]);
					
					$tpl->parse("FillLateComingDetailsInformation",true);
				}
			}
			
			$tpl->parse("FillLateComingInformation",true);
		}
		$tpl->parse("DisplayLateComing",true);
	}
	else
	{
		$tpl->parse("NoDisplayLateComing",true);
	}

	/* Fill year dropdown */
	$tpl->set_var("DisplayYearBlock","");
	if(count($arrYear) > 0)
	{
		foreach($arrYear as $yr)
		{
			$tpl->set_var("curyr", $yr);
			
			$tpl->parse("DisplayYearBlock",true);
		}
	}
	
	$tpl->pparse('main',false);

?>
