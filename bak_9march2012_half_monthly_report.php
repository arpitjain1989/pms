<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('half_monthly_report.html','main_container');
	
	
	

	include_once('includes/class.calculation.php');
	
	$objCalculation = new calculation();
	
	$tpl->set_var("mainheading","Attendance Report");
	$breadcrumb = '<li class="active">Attendance Report</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	$month = '01';
	$year = '2013';
	
	$employeeList = $objCalculation -> fnGetEmployees();
	
	foreach($employeeList as $employee)
	{
		
		//$userid = $employee['id'];
		$userid =40;
		$break_exceed_days = $objCalculation->fnGetTotalBreacks($userid,$month,$year);
		$total_late_coming = $objCalculation->fnGetHalfTotalLateComings($userid,$month,$year);
		$in_total = $objCalculation->fnGetHalfTotalPresents($userid,$month,$year);
		$total_ppl = $objCalculation->fnGetHalfTotalLeave($userid,$month,$year,'ppl');
		$total_uhl = $objCalculation->fnGetHalfTotalLeave($userid,$month,$year,'uhl');
		$total_phl = $objCalculation->fnGetHalfTotalLeave($userid,$month,$year,'phl');
		$wo = $objCalculation->fnGetHalfTotalLeave($userid,$month,$year,'wo');
		$total_ph = $objCalculation->fnGetHalfTotalLeave($userid,$month,$year,'ph');
		$ha = $objCalculation->fnGetHalfTotalLeave($userid,$month,$year,'ha');
		$hlwp = $objCalculation->fnGetHalfTotalLeave($userid,$month,$year,'hlwp');
		$abs = $objCalculation->fnGetHalfTotalLeave($userid,$month,$year,'a');
		$plwp = $objCalculation->fnGetHalfTotalLeave($userid,$month,$year,'plwp');
		$ulwp = $objCalculation->fnGetHalfTotalLeave($userid,$month,$year,'ulwp');
		$total_upl = $objCalculation->fnGetHalfTotalLeave($userid,$month,$year,'upl');
		$smplt = $objCalculation->fnGetHalfTotalLeave($userid,$month,$year,'smplt');
		$MovementDetails = $objCalculation->fnGetTotalOfficialShiftMovementDays($userid,$month,$year);
	 	//echo 'hello';print_r($MovementDetails);
		if(isset($MovementDetails))
		{
			$total_shift_mooment_days = $MovementDetails['total'];
			$total_shift_mooment_approved_days = $MovementDetails['approved'];
			$total_shift_mooment_unapproved = $MovementDetails['total'] - $MovementDetails['approved'];
			$total_shift_mooment_unapproved_deduction = $total_shift_mooment_unapproved * .5 ;
			$total_shift_mooment_approved_days = ($total_shift_mooment_approved_days * 1) + ($total_shift_mooment_unapproved * .5 );
			//$in_total = $in_total + ($total_shift_mooment_approved_days) ; 
		}
		else
		{
			$total_shift_mooment_approved_days = 0;
			$total_shift_mooment_unapproved_deduction = 0;
		}
		$weekOfDays = $objCalculation->fnGetHalfWeekOfDates($userid,$month,$year);
		//print_r($weekOfDays);
		echo 'hello';
		if(count($weekOfDays) > 0 )
		{
			foreach($weekOfDays as $weekDays)
			{
				$cur = '2013-01-13 00:00:00';
				$prev_date = date('Y-m-d', strtotime('-1 day', strtotime($cur)));
				$next_date = date('Y-m-d', strtotime('+1 day', strtotime($cur)));
				$temp = array();
				echo 'hello';
				$checkPrevDay = $objCalculation->fnCheckNextDate($userid,$cur,$prev_date,$next_date,$temp);
			}
		}		
		die;
		//echo 'in_total'.$in_total;
		
		$total_avail_leave = $objCalculation->fnGetTotalLeaveAvails($userid);
		
		$present_week_ph_total =  $in_total + $wo + $total_ph;
		
		$deducted_late_comings = '';
		$deducted_late_comings_days = '';
		//$break_exceed_days = '';
		$deducted_break_exceed_days = '';
		if($total_late_coming > 3)
		{	
			$deducted_late_comings = $total_late_coming - 3;
			if($deducted_late_comings > 2)
			{
				$deducted_late_comings = $deducted_late_comings - 2;
				$deducted_late_comings_days = $deducted_late_comings_days + 1;
				
				if($deducted_late_comings > 0)
				{
					$deducted_late_comings_days = $deducted_late_comings_days + $deducted_late_comings ;
				}
			}
			else
			{
				$deducted_late_comings_days = $deducted_late_comings_days + ($deducted_late_comings * .5);
			}
		}
		else
		{
			$deducted_late_comings_days = 0 ;
		}
		
		if($break_exceed_days > 3)
		{
			$break_exceed_days = $break_exceed_days - 3;
			if($break_exceed_days > 0 )
			{
				$deducted_break_exceed_days = $deducted_break_exceed_days + ($break_exceed_days * .25); 
			}
		}
		else
		{
			$deducted_break_exceed_days = 0;
		}
		$total_deducted_late_and_break = ($deducted_late_comings_days + $deducted_break_exceed_days); 	
		
		
		
		if($total_phl > 0)
		{
			$deducted_phl = $total_phl * .5 ;
		}
		else
		{
			$deducted_phl = 0;
		}
		if($total_uhl > 0)
		{
			$deducted_uhl = $total_uhl * .5 ;
		}
		else
		{
			$deducted_uhl = 0;
		}
		
		$total_planned_leave_taken = $total_ppl + $deducted_phl;
		//echo '<br>total_upl------'.$total_upl.'<br>deducted_uhl--------'.$deducted_uhl;
		$total_unplanned_leave_taken = $total_upl + $deducted_uhl;
		
		//$total_planned_leave_taken = 5;
		//$total_hlwp = 1;
		//$total_unplanned_leave_taken = 5;
		
		
		
		if($total_planned_leave_taken > $total_avail_leave)
		{
			$plwp_eligible_leaves_total = $total_planned_leave_taken - $total_avail_leave;
			$remaining_ppl_leave_for_hlwp = 0;
			
			$ppl_given_for_plwp = $total_avail_leave;
			if($plwp_eligible_leaves_total > 3)
			{
				$total_plwp_given = 3;
				$elegible_absent_after_plwp = $plwp_eligible_leaves_total - 3;
				$remaining_plwp_leave_for_hlwp = 0;
				
			}
			else
			{
				$elegible_absent_after_plwp = 0;
				$total_plwp_given = $plwp_eligible_leaves_total;
				$remaining_plwp_leave_for_hlwp = 3 - $total_plwp_given;
			}
		}
		else
		{
			$plwp_eligible_leaves_total = 0;
			$elegible_absent_after_plwp = 0;
			$total_plwp_given = 0;
			$remaining_plwp_leave_for_hlwp = 3;
			$remaining_ppl_leave_for_hlwp = $total_avail_leave - $total_planned_leave_taken;
			$ppl_given_for_plwp = $total_avail_leave - $remaining_ppl_leave_for_hlwp;
		}
		
		
		
		$total_hlwp = $hlwp * .5 ;
		
		
		if($total_hlwp > 0)
		{
			if($elegible_absent_after_plwp > 0)
			{
				$elegible_absent_after_hlwp = $total_hlwp;
				$ppl_given_for_hlwp = 0;
				$total_hlwp_given = 0;
			}
			else
			{	
			
				if($total_hlwp > $remaining_ppl_leave_for_hlwp)
				{
					$plwp_eligible_leaves_total_in_hlwp =  $total_hlwp - $remaining_ppl_leave_for_hlwp;
					$ppl_given_for_hlwp = $remaining_ppl_leave_for_hlwp;
					$remaining_ppl_leave_for_ulwp = 0;
					
					if($plwp_eligible_leaves_total_in_hlwp > $remaining_plwp_leave_for_hlwp)
					{
						$elegible_absent_after_hlwp = $plwp_eligible_leaves_total_in_hlwp - $remaining_plwp_leave_for_hlwp ;
						
						$remaining_plwp_leave_for_ulwp = 0;
						$total_hlwp_given = $remaining_plwp_leave_for_hlwp;
						
					}
					else
					{
						$remaining_plwp_leave_for_ulwp = $remaining_plwp_leave_for_hlwp - $plwp_eligible_leaves_total_in_hlwp;
						$elegible_absent_after_hlwp =0;
						$total_hlwp_given = $plwp_eligible_leaves_total_in_hlwp;
					}
					
					
				}
				else
				{
					$remaining_ppl_leave_for_ulwp = $remaining_ppl_leave_for_hlwp - $total_hlwp;
					$plwp_eligible_leaves_total_in_hlwp = 0;
					$total_hlwp_given = 0;
					$remaining_plwp_leave_for_ulwp = 3;
					$ppl_given_for_hlwp = $total_hlwp;
					$elegible_absent_after_hlwp = 0;
				}
			}
		}
		else
		{
			$remaining_ppl_leave_for_ulwp = $remaining_ppl_leave_for_hlwp;
			$remaining_plwp_leave_for_ulwp = $remaining_plwp_leave_for_hlwp;
			$elegible_absent_after_hlwp = 0;
			$total_hlwp_given = 0;
			$ppl_given_for_hlwp = 0;
		}
		
		
		
		
		
		
		$elegible_absent_after_plwp_hlwp = $elegible_absent_after_hlwp + $elegible_absent_after_plwp;
		if($total_unplanned_leave_taken > 0)
		{
			if($elegible_absent_after_plwp_hlwp > 0)
			{
				$elegible_absent_after_ulwp = $total_unplanned_leave_taken;
				$total_ulwp_given = 0;
				$ppl_given_for_ulwp = 0;
			}
			else
			{
				if($total_unplanned_leave_taken > $remaining_ppl_leave_for_ulwp)
				{
					$plwp_eligible_leaves_total_in_ulwp =  $total_unplanned_leave_taken - $remaining_ppl_leave_for_ulwp;
					$ppl_given_for_ulwp = $remaining_ppl_leave_for_ulwp;
					$remaining_ppl_leave_after_ulwp = 0;
					
					if($plwp_eligible_leaves_total_in_ulwp > $remaining_plwp_leave_for_ulwp)
					{
						$elegible_absent_after_ulwp = $plwp_eligible_leaves_total_in_ulwp - $remaining_plwp_leave_for_ulwp ;
						
						$remaining_plwp_leave_after_ulwp = 0;
						$total_ulwp_given = $remaining_plwp_leave_for_ulwp;
						$remaining_plwp_leave_after_ulwp = 0;
					}
					else
					{
						$remaining_plwp_leave_after_ulwp = $remaining_plwp_leave_for_ulwp - $plwp_eligible_leaves_total_in_ulwp;
						$total_ulwp_given = $remaining_plwp_leave_for_ulwp - $remaining_plwp_leave_after_ulwp;
						$elegible_absent_after_ulwp = 0;
					}
				}
				else
				{
					$ulwp_eligible_leaves_total = 0;
					$elegible_absent_after_ulwp = 0;
					$total_ulwp_given = 0;
					$remaining_plwp_leave_after_ulwp = 3;
					$remaining_ppl_leave_after_ulwp = $remaining_ppl_leave_for_ulwp - $total_unplanned_leave_taken;
					$ppl_given_for_ulwp = $remaining_ppl_leave_for_ulwp - $remaining_ppl_leave_after_ulwp;
				}
			}
		}
		else
		{
			$remaining_ppl_leave_for_ulwp = $remaining_ppl_leave_for_hlwp;
			$remaining_plwp_leave_for_ulwp = $remaining_plwp_leave_for_hlwp;
			$ppl_given_for_ulwp = 0;
			$remaining_plwp_leave_after_ulwp = 0;
			$elegible_absent_after_ulwp = 0;
			$total_ulwp_given = 0;
		}
		
		if($ha > 0)
		{
			$deducted_abs = $ha * .5;
		}
		else
		{
			$deducted_abs = 0;
		}
		
		$total_abs_and_deducted_abs = $deducted_abs + $abs;
		
		$total_leave_after_plwp_ulwp_hlwp = $elegible_absent_after_plwp + $elegible_absent_after_hlwp + $elegible_absent_after_ulwp;
		$total_absence = $total_abs_and_deducted_abs + $total_leave_after_plwp_ulwp_hlwp;
		
		if($total_absence > 3)
		{
			$deducted_days = (3 * 1.25);
			$total_absence = $total_absence -3;
			if($total_absence > 0)
			{
				if($total_absence > 2)
				{
					$deducted_days = ($deducted_days + (2 * 1.50));
					$total_absence = $total_absence - 2;	
						if($total_absence > 0)
						{
								$deducted_days = ($deducted_days + ($total_absence * 2));
						}
				}
				else
				{						
					$deducted_days = ($deducted_days + ($total_absence * 1.50));
				}
			}
		}
		else
		{
			$deducted_days = ($total_absence + ($total_absence * .25));
		}

		$final_deducted_day = $deducted_days + $total_deducted_late_and_break + $total_shift_mooment_unapproved_deduction;
	
		/*echo '<pre>';
	
		echo '<br>userid--------'.$userid;
		echo '<br>total_avail_leave--------'.$total_avail_leave;
		echo '<br>total_abs_and_deducted_abs--------'.$total_abs_and_deducted_abs;
		echo '<br>total_planned_leave_taken--------'.$total_planned_leave_taken;
		echo '<br>total_hlwp--------'.$total_hlwp;
		echo '<br>total_unplanned_leave_taken--------'.$total_unplanned_leave_taken;
		
		
		echo '<br><br><br>ppl_given_for_plwp--------'.$ppl_given_for_plwp;
		echo '<br>ppl_given_for_hlwp--------'.$ppl_given_for_hlwp;
		echo '<br>ppl_given_for_ulwp--------'.$ppl_given_for_ulwp;
		echo '<br><br>total_plwp_given--------'.$total_plwp_given;
		echo '<br>total_hlwp_given--------'.$total_hlwp_given;
		echo '<br>total_ulwp_given--------'.$total_ulwp_given;
		echo '<br><br>elegible_absent_after_plwp--------'.$elegible_absent_after_plwp;
		echo '<br>elegible_absent_after_hlwp--------'.$elegible_absent_after_hlwp;
		echo '<br>elegible_absent_after_ulwp--------'.$elegible_absent_after_ulwp;
		
		echo '<br><br><br>total_abs_and_deducted_abs--------'.$total_abs_and_deducted_abs;
		echo '<br>total_absence--------'.$total_absence;
		echo '<br>deducted_days--------'.$deducted_days;
		echo '<br>total_deducted_late_and_break--------'.$total_deducted_late_and_break;
		echo '<br>final_deducted_day--------'.$final_deducted_day;*/
	
		$total_ppl_consumed = $ppl_given_for_plwp + $ppl_given_for_hlwp + $ppl_given_for_ulwp;
		
		
		
		/*echo '<br>in_total--------'.$in_total;
		echo '<br>wo--------'.$wo;
		echo '<br>total_ph--------'.$total_ph;
		echo '<br>in_total---'.$in_total.'wo----'.$wo.'total_ph-----'.$total_ph.'total_uhl------'.$total_uhl.'total_phl-------'.$total_phl;*/
		$total_present = $in_total + $wo + $total_ph + ($total_uhl * .5) + ($total_phl * .5) ;
		/*echo '<br>total_present'.$total_present.'final_deducted_day'.$final_deducted_day.'total_ppl_consumed'.$total_ppl_consumed.'<br>';*/
		$payDays = ($total_present - $final_deducted_day) + $total_ppl_consumed;
		
		if($payDays < 15)
		{
			$leaves_earn = 0;
		}
		else
		{
			$leaves_earn = 1;
		}
		/*echo '<br>leaves_earn--------'.$leaves_earn;
		echo '<br>total_present--------'.$total_present;
		echo '<br>payDays--------'.$payDays; 	*/
		$ClosingLeavesBalance = ($total_avail_leave - $total_ppl_consumed) + $leaves_earn;
		
		$reason = array("p"=>$in_total,"total_plt"=>$total_late_coming,"ppl"=>$total_ppl,"uhl"=>$total_uhl,"total_phl"=>$total_phl,"wo"=>$wo,"ph"=>$total_ph,"ha"=>$ha,"hlwp"=>$hlwp,"a"=>$abs,"plwp"=>$plwp,"ulwp"=>$ulwp,"upl"=>$total_upl,"total_present"=>$total_present,"deducted_days"=>$final_deducted_day,"payDays"=>$payDays,"OpeningLeavesBalance"=>$total_avail_leave,"leave_earn"=>$leaves_earn,"pl_consume"=>$total_ppl_consumed,"ClosingLeavesBalance"=>$ClosingLeavesBalance);
		
		
		$current_date = date("Y-m-d");
		
		$summary = array("emp_id"=>$userid,"added_no_of_leaves"=>$leaves_earn,"date"=>$current_date,"reason"=>json_encode($reason));
		
		$checkRecordExistence = $objCalculation -> fnCheckHalfExistence($userid,$month,$year);
		$insertRemainingLeaves = $objCalculation -> fnUpdateRemainingLeave($userid,$ClosingLeavesBalance);
		
		if(isset($checkRecordExistence) &&  $checkRecordExistence != '')
		{
			$updateSummary = $objCalculation -> fnUpdateHalfSummary($checkRecordExistence,$summary,$month,$year);
		}
		else
		{
			$insertSummary = $objCalculation -> fnInsertHalfSummary($summary,$month,$year);
		}
	}
	
	$getAllCalculation = $objCalculation -> fnGetAllHalfCalculation($month,$year);
	$tpl->set_var("FillReport","");
	if(count($getAllCalculation) > 0 )
	{
		foreach($getAllCalculation as $calculations)
		{
		/*echo '<pre>';
		print_r($calculations);*/
			$tpl->setAllValues($calculations);
			$tpl->parse("FillReport",true);
		}
	}
	
	$tpl->pparse('main',false);
?>
die
