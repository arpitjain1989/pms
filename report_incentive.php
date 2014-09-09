<?php 
	include('common.php');
	
	error_reporting(E_ALL);
	
	$tpl = new Template($app_path);
	
	set_time_limit(0);
	
	$tpl->load_file('template.html','main');
	$tpl->load_file('report_incentive.html','main_container');

	$PageIdentifier = "IncentiveReport";
	include_once('userrights.php');

	//$curDate = Date('Y-m-d');
	//echo '<pre>'; print_r($_SESSION); die;
	
	$first_date_this_month =  '';
	$current_date_this_month = '';
	
	$curYear = Date('Y');

	$arrYear = array($curYear, $curYear-1);
	
	$tpl->set_var("mainheading","KRA / Incentive Report");
	$breadcrumb = '<li class="active">KRA / Incentive Report</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	

	include_once('includes/class.attendance.php');
	$objAttendance = new attendance();

	include_once('includes/class.employee.php');
	$objEmployee = new employee();
	
	include_once("includes/class.calculation.php");
	$objCalculation = new calculation();

	include_once("includes/class.designation.php");
	$objDesignation = new designations();

	$tpl->set_var("FillIncentive","");
	
	if(isset($_POST["action"]) && (trim($_POST["action"]) == "IncentiveSearch" || trim($_POST["action"]) == "export"))
	{
		//echo 'hello'; die;
		/*$_SESSION["SearchLeave"]["start_date"] = $first_day_this_month;
		$_SESSION["SearchLeave"]["end_date"] = $last_day_this_month;*/
		$_SESSION["incentive"]["first_date"] = $_POST["start_date"];
		$_SESSION["incentive"]["last_date"] = $_POST["end_date"];
		
		header("Location: report_incentive.php");
		exit;
	}

	/*if(!isset($_SESSION["SearchLeave"]["start_date"]))
		$_SESSION["SearchLeave"]["start_date"] = $first_day_this_month;

	if(!isset($_SESSION["SearchLeave"]["end_date"]))
		$_SESSION["SearchLeave"]["end_date"] = $last_day_this_month;*/
		
	if(!isset($_SESSION["incentive"]["first_date"]))
		$_SESSION["incentive"]["first_date"] = $first_date_this_month;
	if(!isset($_SESSION["incentive"]["last_date"]))
		$_SESSION["incentive"]["last_date"] = $current_date_this_month;

	//$_SESSION["SearchLeave"]["month"] = "06";
	//$_SESSION["SearchLeave"]["year"] = "2013";

	/*if(isset($_SESSION["SearchLeave"]["start_date"]))
		$tpl->set_var("start_date", $_SESSION["SearchLeave"]["start_date"]);
	if(isset($_SESSION["SearchLeave"]["end_date"]))
		$tpl->set_var("end_date", $_SESSION["SearchLeave"]["end_date"]);*/

	if(isset($_SESSION["incentive"]["first_date"]))
		$tpl->set_var("start_date", $_SESSION["incentive"]["first_date"]);
	if(isset($_SESSION["incentive"]["last_date"]))
		$tpl->set_var("end_date", $_SESSION["incentive"]["last_date"]);

	/*$arrEmployee = $objEmployee->fnGetEmployees($_SESSION["SearchLeave"]["manager"], $_SESSION["SearchLeave"]["teamleader"], $_SESSION["SearchLeave"]["agents"],$_SESSION["SearchLeave"]["shiftid"],$_SESSION["SearchLeave"]["start_date"],$_SESSION["SearchLeave"]["end_date"]);*/

	//echo '<pre>'; print_r($_SESSION);
	
	$arrEmployee = $objEmployee->fnGetAllEmployeeForIncentive($_SESSION['incentive']);

	/* Export to excel */
	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "export") 
	{
		$filename = "Incentive_Monthly_Report_for_date_".$_SESSION["incentive"]["first_date"]."_To_".$_SESSION["incentive"]["last_date"].".xls";

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
		xlsWriteLabel(0,2,"Total Present");
		xlsWriteLabel(0,3,"PHL");
		xlsWriteLabel(0,4,"UPL");
		xlsWriteLabel(0,5,"ULWP");
		xlsWriteLabel(0,6,"UHL");
		xlsWriteLabel(0,7,"HLWP");
		xlsWriteLabel(0,8,"HA");
		xlsWriteLabel(0,9,"A");
		xlsWriteLabel(0,10,"Late Coming");
		xlsWriteLabel(0,11,"KRA(%)");

		$xlsRow = 1;
		if(is_array($arrEmployee) && count($arrEmployee) > 0)
		{
			foreach($arrEmployee as $curEmp)
			{
				//echo '<pre>';print_r($curEmp);
				$tpl->set_var("employeename",$curEmp["emp_name"]);
				$tpl->set_var("teamLeaderName",$curEmp["teamlead_name"]);
				$tpl->set_var("employeeid",$curEmp["emp_id"]);

				//echo '<br><br>eid:'.$curEmp['emp_id'];

				$arrDesignation = $objDesignation->fnGetDesignationById($curEmp['emp_des']);

				$sm_time = '00:00';
				if(isset($arrDesignation["sm_minimum_working_hour"]))
					$sm_time = $arrDesignation["sm_minimum_working_hour"];
	
				/* Total break exists */
				$break_exceed_days = $objCalculation->fnGetTotalIncentiveBreaks($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],$curEmp['emp_des']);

				/* Total late comings */
				$total_late_coming = $objCalculation->fnGetTotalIncentiveLateComings($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],$curEmp['emp_des']);

				/* Total presents */
				$in_total = $objCalculation->fnGetTotalIncentivePresents($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"]);

				/* Total ppl */
				$total_ppl = $objCalculation->fnGetTotalIncentiveLeave($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],'ppl');

				/* Total uhl */
				$total_uhl = $objCalculation->fnGetTotalIncentiveLeave($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],'uhl');

				/* Total php */
				$total_phl = $objCalculation->fnGetTotalIncentiveLeave($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],'phl');

				/* Total wo */
				$wo = $objCalculation->fnGetTotalIncentiveLeave($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],'wo');

				/* Total ph */
				$total_ph = $objCalculation->fnGetTotalIncentiveLeave($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],'ph');

				/* Total ha */
				$ha = $objCalculation->fnGetTotalIncentiveLeave($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],'ha');

				/* Total hlwp */
				$hlwp = $objCalculation->fnGetTotalIncentiveLeave($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],'hlwp');

				/* Total abs */
				$abs = $objCalculation->fnGetTotalIncentiveLeave($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],'a');
			
				/* Total plwp */
				$plwp = $objCalculation->fnGetTotalIncentiveLeave($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],'plwp');

				/* Total ulwp */
				$ulwp = $objCalculation->fnGetTotalIncentiveLeave($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],'ulwp');

				/* Total upl */
				$total_upl = $objCalculation->fnGetTotalIncentiveLeave($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],'upl');

				/* Total smplt */
				$smplt = $objCalculation->fnGetTotalIncentiveLeave($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],'smplt');

				/* Total half day that not marked*/
				$checkHalfDays = $objCalculation->fnGetTotalIncentiveHalfDays1($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],$curEmp['emp_des']);

				/* Total movement details */
				$MovementDetails = $objCalculation->fnGetTotalIncentiveOfficialShiftMovementDays($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],$sm_time);

				if(isset($MovementDetails))
				{
					$total_shift_mooment_days = $MovementDetails['total'];
					$total_shift_mooment_approved_days = $MovementDetails['approved'];
					$total_shift_mooment_unapproved = $MovementDetails['total'] - $MovementDetails['approved'];
					$total_shift_mooment_unapproved_deduction = $total_shift_mooment_unapproved * .5 ;
					$total_shift_mooment_approved_days = ($total_shift_mooment_approved_days * 1) + ($total_shift_mooment_unapproved * .5 );
					//$in_total = $in_total + ($total_shift_mooment_approved_days); 
				}
				else
				{
					$total_shift_mooment_approved_days = 0;
					$total_shift_mooment_unapproved_deduction = 0;
				}

				$ha = $total_shift_mooment_unapproved + $ha + $checkHalfDays;

				
				$total_presents_days = $in_total + (0.5 * $total_uhl) + (0.5 * $hlwp) + (0.5 * $ha) + (0.5 * $total_phl);

				/*if($total_late_coming > 3)
				{
					$officialLateComings = $total_late_coming - 3;
				}
				else
				{
					$officialLateComings = 0;
				}*/

				$total_p_for_calc = $total_presents_days ;

				$total_p_with_leaves_for_calc = $total_presents_days + (0.5 * $total_uhl) + (0.5 * $hlwp) + (0.5 * $ha) + $total_upl + $abs + $ulwp;

				if($total_p_with_leaves_for_calc > 0)
				{
					$final_percentile = (($total_p_for_calc / $total_p_with_leaves_for_calc) * 100 );

					$final_round = round($final_percentile , 2);
				}
				else
				{
					$final_round = '0';
				}

				$final_round_with_per = $final_round.'%';
				
				xlsWriteLabel($xlsRow,0,$curEmp["emp_name"]);
				xlsWriteLabel($xlsRow,1,$curEmp["teamlead_name"]);
				xlsWriteLabel($xlsRow,2,$total_presents_days);
				xlsWriteLabel($xlsRow,3,$total_phl);
				xlsWriteLabel($xlsRow,4,$total_upl);
				xlsWriteLabel($xlsRow,5,$ulwp);
				xlsWriteLabel($xlsRow,6,$total_uhl);
				xlsWriteLabel($xlsRow,7,$hlwp);
				xlsWriteLabel($xlsRow,8,$ha);
				xlsWriteLabel($xlsRow,9,$abs);
				xlsWriteLabel($xlsRow,10,$total_late_coming);
				xlsWriteLabel($xlsRow,11,$final_round_with_per);
				
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

	

	$tpl->set_var("FillAttendanceInformation","");

	if($_SESSION["incentive"]["first_date"] != '' && $_SESSION["incentive"]["last_date"] != '')
	{
		//echo 'hello'.$_SESSION["incentive"]["first_date"]; die;
		if(count($arrEmployee) > 0)
		{
			foreach($arrEmployee as $curEmp)
			{
				//echo $_SESSION["incentive"]["first_date"];
				//echo '<pre>';print_r($curEmp); die;
				$tpl->set_var("employeename",$curEmp["emp_name"]);
				$tpl->set_var("teamLeaderName",$curEmp["teamlead_name"]);
				$tpl->set_var("employeeid",$curEmp["emp_id"]);

				//echo '<br><br>eid:'.$curEmp['emp_id'];

				$arrDesignation = $objDesignation->fnGetDesignationById($curEmp['emp_des']);

				$sm_time = '00:00';
				if(isset($arrDesignation["sm_minimum_working_hour"]))
					$sm_time = $arrDesignation["sm_minimum_working_hour"];

				/* Total break exists */
				$break_exceed_days = $objCalculation->fnGetTotalIncentiveBreaks($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],$curEmp['emp_des']);

				/* Total late comings */
				$total_late_coming = $objCalculation->fnGetTotalIncentiveLateComings($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],$curEmp['emp_des']);

				/* Total presents */
				$in_total = $objCalculation->fnGetTotalIncentivePresents($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"]);

				/* Total ppl */
				$total_ppl = $objCalculation->fnGetTotalIncentiveLeave($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],'ppl');

				/* Total uhl */
				$total_uhl = $objCalculation->fnGetTotalIncentiveLeave($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],'uhl');

				/* Total php */
				$total_phl = $objCalculation->fnGetTotalIncentiveLeave($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],'phl');

				/* Total wo */
				$wo = $objCalculation->fnGetTotalIncentiveLeave($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],'wo');

				/* Total ph */
				$total_ph = $objCalculation->fnGetTotalIncentiveLeave($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],'ph');

				/* Total ha */
				$ha = $objCalculation->fnGetTotalIncentiveLeave($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],'ha');

				/* Total hlwp */
				$hlwp = $objCalculation->fnGetTotalIncentiveLeave($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],'hlwp');

				/* Total abs */
				$abs = $objCalculation->fnGetTotalIncentiveLeave($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],'a');

				
				/* Total plwp */
				$plwp = $objCalculation->fnGetTotalIncentiveLeave($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],'plwp');

				/* Total ulwp */
				$ulwp = $objCalculation->fnGetTotalIncentiveLeave($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],'ulwp');

				/* Total upl */
				$total_upl = $objCalculation->fnGetTotalIncentiveLeave($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],'upl');

				/* Total smplt */
				$smplt = $objCalculation->fnGetTotalIncentiveLeave($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],'smplt');

				/* Total half day that not marked*/
				$checkHalfDays = $objCalculation->fnGetTotalIncentiveHalfDays1($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],$curEmp['emp_des']);

				/* Total movement details */
				$MovementDetails = $objCalculation->fnGetTotalIncentiveOfficialShiftMovementDays($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"],$sm_time);

				$weekOfDays = $objCalculation->fnGetWeekOfDates($curEmp["emp_id"],$_SESSION["incentive"]["first_date"],$_SESSION["incentive"]["last_date"]);
				//print_r($weekOfDays);
				
				if(isset($MovementDetails))
				{
					$total_shift_mooment_days = $MovementDetails['total'];
					$total_shift_mooment_approved_days = $MovementDetails['approved'];
					$total_shift_mooment_unapproved = $MovementDetails['total'] - $MovementDetails['approved'];
					$total_shift_mooment_unapproved_deduction = $total_shift_mooment_unapproved * .5 ;
					$total_shift_mooment_approved_days = ($total_shift_mooment_approved_days * 1) + ($total_shift_mooment_unapproved * .5 );
					//$in_total = $in_total + ($total_shift_mooment_approved_days); 
				}
				else
				{
					$total_shift_mooment_approved_days = 0;
					$total_shift_mooment_unapproved_deduction = 0;
				}

				$ha = $total_shift_mooment_unapproved + $ha + $checkHalfDays;

				/*echo '<br>break_exceed_days:'.$break_exceed_days;
				echo '<br>total_late_coming:'.$total_late_coming;
				echo '<br>in_total:'.$in_total;
				echo '<br>total_ppl:'.$total_ppl;
				echo '<br>total_uhl:'.$total_uhl;
				echo '<br>total_phl:'.$total_phl;
				echo '<br>wo:'.$wo;
				echo '<br>total_ph:'.$total_ph;
				echo '<br>ha:'.$ha;
				echo '<br>abs:'.$abs;
				echo '<br>plwp:'.$plwp;
				echo '<br>ulwp:'.$ulwp;
				echo '<br>total_upl:'.$total_upl;
				echo '<br>smplt:'.$smplt;*/
				//echo 'here';

				$total_presents_days = $in_total + (0.5 * $total_uhl) + (0.5 * $hlwp) + (0.5 * $ha) + (0.5 * $total_phl);

				/*if($total_late_coming > 3)
				{
					$officialLateComings = $total_late_coming - 3;
				}
				else
				{
					$officialLateComings = 0;
				}*/

				$tpl->set_var("ppl",$total_ppl);
				$tpl->set_var("upl",$total_upl);
				$tpl->set_var("phl",$total_phl);
				$tpl->set_var("uhl",$total_uhl);
				$tpl->set_var("ha",$ha);
				$tpl->set_var("plwp",$plwp);
				$tpl->set_var("hlwp",$hlwp);
				$tpl->set_var("abs",$abs);
				$tpl->set_var("ulwp",$ulwp);
				$tpl->set_var("plt",$total_late_coming);
				$tpl->set_var("total_present",$total_presents_days);

				//$total_p_for_calc = $total_presents_days + (0.5 * $total_uhl) + (0.5 * $hlwp) + (0.5 * $ha);
				$total_p_for_calc = $total_presents_days ;

				$total_p_with_leaves_for_calc = $total_presents_days + (0.5 * $total_uhl) + (0.5 * $hlwp) + (0.5 * $ha) + $total_upl + $abs + $ulwp;

				if($total_p_with_leaves_for_calc > 0)
				{
					$final_percentile = (($total_p_for_calc / $total_p_with_leaves_for_calc) * 100);

					$final_round = round($final_percentile , 2);
				}
				else
				{
					$final_round = '0';
				}

				//echo '<br>total_presents_days:'.$total_presents_days;
				//echo '<br>final_round:'.$final_round;

				$tpl->set_var("final_percentile",$final_round);
				
			//echo 'h1'; die;	
				//$curEmpLeaveInfo = $arrEmpLeave;
				//echo '<pre>';print_r($curEmp);
				//$tpl->set_var("FillEmployeeAttendanceBlock","");
				
				$tpl->parse("FillAttendanceInformation",true);
			}
			$tpl->parse("FillIncentive",true);
		}
	}


	

	//echo 'helo';

	$tpl->pparse('main',false);
?>
