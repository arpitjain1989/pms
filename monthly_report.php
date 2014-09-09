<?php
	include('common.php');

	error_reporting(E^ALL);
	set_time_limit(0);

	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('monthly_report.html','main_container');
	
		
	include_once('includes/class.calculation.php');
	
	$objCalculation = new calculation();
	
	$tpl->set_var("mainheading","Attendance Report");
	$breadcrumb = '<li class="active">Attendance Report</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	$month = '05';
	$year = '2013';
	
	/* Get all employee list */
	$employeeList = $objCalculation -> fnGetEmployees();

	foreach($employeeList as $employee)
	{
		
		$userid = $employee['id'];
		//$userid = 162;
		
		/* Total break exists */
		$break_exceed_days = $objCalculation->fnGetTotalBreaks($userid,$month,$year,$employee['designation']);

		/* Total late comings */
		$total_late_coming = $objCalculation->fnGetTotalLateComings($userid,$month,$year,$employee['designation']);

		/* Total presents */
		$in_total = $objCalculation->fnGetTotalPresents($userid,$month,$year);

		/* Total ppl */
		$total_ppl = $objCalculation->fnGetTotalLeave($userid,$month,$year,'ppl');

		/* Total uhl */
		$total_uhl = $objCalculation->fnGetTotalLeave($userid,$month,$year,'uhl');

		/* Total php */
		$total_phl = $objCalculation->fnGetTotalLeave($userid,$month,$year,'phl');

		/* Total wo */
		$wo = $objCalculation->fnGetTotalLeave($userid,$month,$year,'wo');

		/* Total ph */
		$total_ph = $objCalculation->fnGetTotalLeave($userid,$month,$year,'ph');

		/* Total ha */
		$ha = $objCalculation->fnGetTotalLeave($userid,$month,$year,'ha');

		/* Total hlwp */
		$hlwp = $objCalculation->fnGetTotalLeave($userid,$month,$year,'hlwp');

		/* Total abs */
		$abs = $objCalculation->fnGetTotalLeave($userid,$month,$year,'a');

		//$abs123 = $objCalculation->fnGetHalfTotalLeaveByIntimeOut($userid,$month,$year,'a');

		/* Total plwp */
		$plwp = $objCalculation->fnGetTotalLeave($userid,$month,$year,'plwp');

		/* Total ulwp */
		$ulwp = $objCalculation->fnGetTotalLeave($userid,$month,$year,'ulwp');

		/* Total upl */
		$total_upl = $objCalculation->fnGetTotalLeave($userid,$month,$year,'upl');

		/* Total smplt */
		$smplt = $objCalculation->fnGetTotalLeave($userid,$month,$year,'smplt');

		/* Total movement details */
		$MovementDetails = $objCalculation->fnGetTotalOfficialShiftMovementDays($userid,$month,$year);
	 	//echo 'hello'; print_r($MovementDetails);

		/* Get half monthly leaves earned */
		$HalfMonthlyLeavesEarned = $objCalculation->fnHalfMonthlyLeaveEarned($userid,$month,$year);

		/*echo '<br>in_total---'.$in_total;
		echo '<br>break_exceed_days---'.$break_exceed_days;
		echo '<br>total_late_coming---'.$total_late_coming;
		echo '<br>total_uhl---'.$total_uhl;
		echo '<br>total_phl---'.$total_phl;
		echo '<br>wo---'.$wo;
		echo '<br>ha---'.$ha;
		echo '<br>hlwp---'.$hlwp;
		echo '<br>abs---'.$abs;
		echo '<br>plwp---'.$plwp;
		echo '<br>ulwp---'.$ulwp;
		echo '<br>total_ppl---'.$total_ppl;
		echo '<br>total_upl---'.$total_upl;
		echo '<br>smplt---'.$smplt;
		echo '<br>MovementDetails---'.$MovementDetails.'<br>';*/

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
		$weekOfDays = $objCalculation->fnGetWeekOfDates($userid,$month,$year);
		//print_r($weekOfDays); 

		if(count($weekOfDays) > 0 )
		{
			$non_week_of_Days = array();
			$absent_mark = 0;
			$count_ppl = 0;
			$count_upl = 0;
			$count_abs = 0;
			foreach($weekOfDays as $weekDays)
			{
				//print_r($weekDays['date']);
				$cur = $weekDays['date'];
				//$cur = '2013-01-06';
				$sendwitch_dates = array();
				$prev_date = date('Y-m-d', strtotime('-1 day', strtotime($cur)));
				$next_date = date('Y-m-d', strtotime('+1 day', strtotime($cur)));
				$temp = array();
				$temp1 = array();
				$sendwitch1 = array();
				$checkNextDay = $objCalculation->fnCheckNextDate($userid,$cur,$next_date,$temp);
				$count = 0;
				
				//print_r($checkNextDay);
				if($checkNextDay == 'noSendwitch')
				{
					//echo 'No Sendwitch';
				}
				else if(is_array($checkNextDay))
				{
						$checkPrevDay = $objCalculation->fnCheckPrevDate($userid,$prev_date,$temp1);
						//print_r($checkPrevDay);
						if($checkPrevDay == 'noSendwitch')
						{
							//echo 'No Sendwitch';
						}
						if(is_array($checkPrevDay))
						{
							$sendwitch1 = array_merge($checkPrevDay,$checkNextDay);
						}
				}
				//echo 'helllo1';print_r($sendwitch1);
				if(isset($sendwitch1) && count($sendwitch1) > 0)
				{
					//echo $weekDays.'  finds in sendwitch';
					$sendwitch1[] = $cur;
				}
				//echo 'hello';print_r($sendwitch1);
				if(in_array($cur,$sendwitch1))
				{
					$non_week_of_Days[] = $cur; 
				}
			}
			//echo $count;
		}
		//print_r($non_week_of_Days);
		if(isset($non_week_of_Days) && count($non_week_of_Days) > 0)
		{
			
			$count_absent_sendwitch = 0;
			foreach($non_week_of_Days as $nonweekdays)
			{
				$prev_date = date('Y-m-d', strtotime('-1 day', strtotime($nonweekdays)));
				$next_date = date('Y-m-d', strtotime('+1 day', strtotime($nonweekdays)));
				
				$checkPrevDateLeaveId = $objCalculation->fnCheckPreviousDateLeaveId($userid,$prev_date,'prev');
				//echo 	'<Br>@@@@checkPrevDateLeaveId===='.$checkPrevDateLeaveId.'<br />';
				if($checkPrevDateLeaveId == 1)
				{
					$checkNextDateLeaveId = $objCalculation->fnCheckPreviousDateLeaveId($userid,$next_date,'next');
					//echo 	'<Br>@checkNextDateLeaveId===='.$checkNextDateLeaveId.'<br/>';
					if($checkNextDateLeaveId == 1)
					{
						$count_ppl += 1;
					}
					else
					{
						$checkLeaveForm = $objCalculation->fnCheckLeaveExistence($userid,$nonweekdays);
						if($checkLeaveForm == 'approved') 
						{
							$count_ppl += 1;
						}
						else if($checkLeaveForm == 'unapproved') 
						{
							$count_upl += 1;
						}
						else
						{
							$count_abs += 1;
						}
					}
				}
				else if($checkPrevDateLeaveId == 2)
				{
					$checkNextDateLeaveId = $objCalculation->fnCheckPreviousDateLeaveId($userid,$next_date,'next');
					//echo 	'<Br>@checkNextDateLeaveId===='.$checkNextDateLeaveId.'<br/>';
					if($checkNextDateLeaveId == 1 || $checkNextDateLeaveId == 2)
					{
						$count_upl += 1;
					}
					else
					{
						$checkLeaveForm = $objCalculation->fnCheckLeaveExistence($userid,$nonweekdays);
						if($checkLeaveForm == 'approved') 
						{
							$count_ppl += 1;
						}
						else if($checkLeaveForm == 'unapproved') 
						{
							$count_upl += 1;
						}
						else
						{
							$count_abs += 1;
						}
					}
				}
				else if($checkPrevDateLeaveId == '3')
				{
						//echo 'hello';
					$checkNextDateLeaveId = $objCalculation->fnCheckPreviousDateLeaveId($userid,$next_date,'next');
					
						$checkLeaveForm = $objCalculation->fnCheckLeaveExistence($userid,$nonweekdays);
						if($checkLeaveForm == 'approved') 
						{
							$count_ppl += 1;
						}
						else if($checkLeaveForm == 'unapproved') 
						{
							$count_abs += 1;
						}
						else
						{
							$count_abs += 1;
						}
					//echo $count_abs;
				}
				else if($checkPrevDateLeaveId != '')
				{
					$checkNextDateLeaveId = $objCalculation->fnCheckPreviousDateLeaveId($userid,$next_date,'next');
					if($checkNextDateLeaveId != '')
					{
						$checkLeaveForm = $objCalculation->fnCheckLeaveExistence($userid,$nonweekdays);
						if($checkLeaveForm == 'approved') 
						{
							$count_ppl += 1;
						}
						else if($checkLeaveForm == 'unapproved')
						{
							$count_abs += 1;
						}
						else
						{
							$count_abs += 1;
						}
					}
				}
			}
		}
		
		$not_WeekOfDays = count($non_week_of_Days);
		//echo 'in_total'.$in_total;
		//die;
		$final_wo = $wo - $not_WeekOfDays;
		$total_avail_leave = $objCalculation->fnGetTotalLeaveAvails($userid);
		
		$present_week_ph_total =  $in_total + $final_wo + $total_ph;
		
		$deducted_late_comings = '';
		$deducted_late_comings_days = '';
		//$break_exceed_days = '';
		$deducted_break_exceed_days = '';
		if($employee['designation'] == '6' || $employee['designation'] == '18' || $employee['designation'] == '19' || $employee['designation'] == '7' || $employee['designation'] == '8' || $employee['designation'] == '13' || $employee['designation'] == '17' || $employee['designation'] == '20' || $employee['designation'] == '21' || $employee['designation'] == '22' || $employee['designation'] == '23' || $employee['designation'] == '24' || $employee['designation'] == '25' || $employee['designation'] == '26' || $employee['designation'] == '27' || $employee['designation'] == '28')
		{
			$total_late_coming = '0';
			$break_exceed_days = '0';
		}
		if($total_late_coming > 3)
		{	
			$deducted_late_comings = $total_late_coming - 3;
			$total_late_coming = $total_late_coming - 3;
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
			$total_late_coming = 0 ;
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
			$break_exceed_days = 0;
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
		
		$total_planned_leave_taken = $total_ppl + $deducted_phl + $count_ppl + $plwp;
		$total_planned_leave_taken_with_sandwitch = $total_ppl + $count_ppl;
		//echo '<br>total_upl------'.$total_upl.'<br>deducted_uhl--------'.$deducted_uhl;
		$total_unplanned_leave_taken = $total_upl + $deducted_uhl + $count_upl + $ulwp;
		$total_unplanned_leave_taken_with_sandwitch = $total_upl + $count_upl;
		
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
		$total_absence = $total_abs_and_deducted_abs + $total_leave_after_plwp_ulwp_hlwp + $count_abs;
		$total_absence_old = $total_absence;
		
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
		
		$deducted_days = $deducted_days - $total_absence_old;
/* when we run this file after shift movement working with our system uncomment this line */
		//$final_deducted_day = $deducted_days + $total_deducted_late_and_break + $total_shift_mooment_unapproved_deduction;
		
/* for now we not consider shift movement apporval done or not */
		$final_deducted_day = $deducted_days + $total_deducted_late_and_break;
	
		echo '<pre>';
	
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
		echo '<br>deducted_late_comings_days--------'.$deducted_late_comings_days;
		echo '<br>deducted_break_exceed_days--------'.$deducted_break_exceed_days;
		echo '<br>total_deducted_late_and_break--------'.$total_deducted_late_and_break;
		echo '<br>total_shift_mooment_unapproved_deduction--------'.$total_shift_mooment_unapproved_deduction;
		echo '<br>final_deducted_day--------'.$final_deducted_day;
		echo '<br>not_WeekOfDays--------'.$not_WeekOfDays;
		echo '<br>count_ppl'.$count_ppl;
		echo '<br>count_upl'.$count_upl;
		echo '<br>count_abs'.$count_abs;	
	
		$total_ppl_consumed = $ppl_given_for_plwp + $ppl_given_for_hlwp + $ppl_given_for_ulwp;
		
		echo '<br>in_total--------'.$in_total;
		echo '<br>finalwo--------'.$final_wo;
		echo '<br>total_ph--------'.$total_ph;
		echo '<br><br><br>in_total---'.$in_total.'<br>finalwo----'.$final_wo.'<br>total_ph-----'.$total_ph.'<br>total_uhl------'.$total_uhl.'<br>total_phl-------'.$total_phl;
		
		$total_present = $in_total + $final_wo + $total_ph + ($total_uhl * .5) + ($total_phl * .5) ;
		echo '<br><br>total_present---'.$total_present.'<br>final_deducted_day---'.$final_deducted_day.'<br>total_ppl_consumed---'.$total_ppl_consumed.'<br>';
		$payDays = ($total_present - $final_deducted_day) + $total_ppl_consumed;
		
		if($payDays < 15)
		{
			$leaves_earn = 0;
		}
		else if($payDays < 24)
		{
			$leaves_earn = 1;
		}
		else
		{
			$leaves_earn = 2;
		}
		
		/* Deduct leave earned half monthly */
		if($HalfMonthlyLeavesEarned > 0)
		{
			$leaves_earn = $leaves_earn - $HalfMonthlyLeavesEarned;
		}
		
		if($leaves_earn < 0)
		{
			/* Remove leaves from paydays is leaves is less than 0 */
			$payDays = $payDays + $leaves_earn;
		}
		
		echo '<br>leaves_earn--------'.$leaves_earn;
		echo '<br>total_present--------'.$total_present;
		echo '<br>payDays--------'.$payDays; 	
		$ClosingLeavesBalance = ($total_avail_leave - $total_ppl_consumed) + $leaves_earn;
		$total_leave_taken = $total_absence + $total_upl + ($total_phl * .5 ) + ($total_uhl * .5 ) + $total_ppl + $abs + ($ha * .5);
		//$total_leave_taken = $total_upl + ($total_phl * .5 ) + ($total_uhl * .5 ) + $total_ppl_consumed + $abs + ($ha * .5);
		echo '<br>total_leave_taken--------'.$total_leave_taken;
		echo '<br>ClosingLeavesBalance--------'.$ClosingLeavesBalance;
		
//die;
		$current_date = date("Y-m-d");
		
		/*$checkRecordExistence = $objCalculation -> fnCheckHalfExistence($userid,$month,$year);
		
	
		if(isset($checkRecordExistence) &&  $checkRecordExistence != '')
		{
			//$getAllLeaveRecord = $objCalculation->fnGetMonthlyRecord($checkRecordExistence,$month,$year);

			//echo 'hello'; print_r($getAllLeaveRecord); die;
			//echo '<br>total_avail_leave===='.$total_avail_leave = $getAllLeaveRecord['opening'];
			//echo '<br>ClosingLeavesBalance===='.$ClosingLeavesBalance = ($total_avail_leave - $total_ppl_consumed) + $leaves_earn;
			
die;	
			$reason = array("p"=>$in_total,"total_plt"=>$total_late_coming,"ppl"=>$total_ppl,"uhl"=>$total_uhl,"total_phl"=>$total_phl,"wo"=>$final_wo,"ph"=>$total_ph,"ha"=>$ha,"hlwp"=>$hlwp,"a"=>$abs,"plwp"=>$plwp,"ulwp"=>$ulwp,"upl"=>$total_upl,"total_present"=>$total_present,"deducted_days"=>$final_deducted_day,"payDays"=>$payDays,"OpeningLeavesBalance"=>$total_avail_leave,"leave_earn"=>$leaves_earn,"pl_consume"=>$total_ppl_consumed,"ClosingLeavesBalance"=>$ClosingLeavesBalance);
			
			$summary = array("emp_id"=>$userid,"added_no_of_leaves"=>$leaves_earn,"date"=>$current_date,"reason"=>json_encode($reason),"opening_leave"=>$total_avail_leave,"closing_leave"=>$ClosingLeavesBalance,"ishalfmonthly"=>'0');
			
			$updateSummary = $objCalculation -> fnUpdateHalfSummary($checkRecordExistence,$summary,$month,$year);
			
		}
		else
		{*/
			$reason = array("p"=>$in_total,"total_plt"=>$total_late_coming,"ppl"=>$total_planned_leave_taken_with_sandwitch,"uhl"=>$total_uhl,"total_phl"=>$total_phl,"wo"=>$final_wo,"ph"=>$total_ph,"ha"=>$ha,"hlwp"=>$total_hlwp_given,"a"=>$abs,"plwp"=>$total_plwp_given,"ulwp"=>$total_ulwp_given,"upl"=>$total_unplanned_leave_taken_with_sandwitch,"total_present"=>$total_present,"deducted_days"=>$final_deducted_day,"payDays"=>$payDays,"OpeningLeavesBalance"=>$total_avail_leave,"leave_earn"=>$leaves_earn,"pl_consume"=>$total_ppl_consumed,"ClosingLeavesBalance"=>$ClosingLeavesBalance);
			
			$summary = array("emp_id"=>$userid,"added_no_of_leaves"=>$leaves_earn,"date"=>$current_date,"reason"=>json_encode($reason),"opening_leave"=>$total_avail_leave,"closing_leave"=>$ClosingLeavesBalance,"ishalfmonthly"=>'0');
			
			$insertSummary = $objCalculation -> fnInsertHalfSummary($summary,$month,$year);
			
		/*}*/
		
		$insertRemainingLeaves = $objCalculation -> fnUpdateRemainingLeave($userid,$ClosingLeavesBalance);

		
		$finalCalcHalfMonthly = array("employee_id"=>$userid,"month"=>$month,"year"=>$year,"present"=>$in_total,"break_exceeds"=>$break_exceed_days,"total_plt"=>$total_late_coming,"ppl"=>$total_planned_leave_taken_with_sandwitch,"uhl"=>$total_uhl,"wo"=>$final_wo,"ph"=>$total_ph,"ha"=>$ha,"hlwp"=>$total_hlwp_given,"abs"=>$abs,"plwp"=>$total_plwp_given,"ulwp"=>$total_ulwp_given,"nj"=>'0',"le"=>'0',"awol"=>'0',"total_present"=>$total_present,"pay_days"=>$payDays,"opening_leave"=>$total_avail_leave,"leave_earns"=>$leaves_earn,"pl_taken"=>$total_ppl_consumed,"eml_taken"=>'0',"phl_taken"=>$total_phl,"uhl_taken"=>$total_uhl,"total_leave_taken"=>$total_leave_taken,"closing_balance"=>$ClosingLeavesBalance,"upl"=>$total_unplanned_leave_taken_with_sandwitch,"deducted_days"=>$final_deducted_day);
		//echo '<pre>'; print_r($finalCalcHalfMonthly); 
		$insertHalfReport = $objCalculation -> fnInsertMonthReport($finalCalcHalfMonthly);
		//echo "<br/><hr>";
		//die;
	}
	//die;
	/*$getAllCalculation = $objCalculation->fnGetAllHalfCalculation($month,$year);
	//echo '<pre>';print_r($getAllCalculation);
	$tpl->set_var("FillReport","");
	if(count($getAllCalculation) > 0 )
	{
		foreach($getAllCalculation as $calculations)
		{
		//echo '<pre>';
		//print_r($calculations);
			$tpl->setAllValues($calculations);
			$resultArr = json_decode($calculations["reason"]);

			if($resultArr)
			{
				$tpl->set_var("p",$resultArr->p);
				$tpl->set_var("total_plt",$resultArr->total_plt);
				$tpl->set_var("ppl",$resultArr->ppl);
				$tpl->set_var("uhl",$resultArr->uhl);
				$tpl->set_var("total_phl",$resultArr->total_phl);
				$tpl->set_var("wo",$resultArr->wo);
				$tpl->set_var("ph",$resultArr->ph);
				$tpl->set_var("ha",$resultArr->ha);
				$tpl->set_var("hlwp",$resultArr->hlwp);
				$tpl->set_var("a",$resultArr->a);
				$tpl->set_var("plwp",$resultArr->plwp);
				$tpl->set_var("ulwp",$resultArr->ulwp);
				$tpl->set_var("upl",$resultArr->upl);
				$tpl->set_var("total",$resultArr->total_present);
				$tpl->set_var("deducted_days",$resultArr->deducted_days);
				$tpl->set_var("pay_days",$resultArr->payDays);
				$tpl->set_var("OpeningLeavesBalance",$resultArr->OpeningLeavesBalance);
				$tpl->set_var("leave_earn",$resultArr->leave_earn);
				$tpl->set_var("pl_consume",$resultArr->pl_consume);
				$tpl->set_var("ClosingLeavesBalance",$resultArr->ClosingLeavesBalance);
			}
			
			//print_r($resultArr);
			$tpl->parse("FillReport",true);
		}
	}*/
	
	$tpl->pparse('main',false);
?>
