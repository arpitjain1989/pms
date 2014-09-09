<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('calculation.html','main_container');
	
	$PageIdentifier = "Attendance";
	include_once('userrights.php');

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
		
	//	$userid = $employee['id'];
		$userid =45;
		$break_exceed_days = $objCalculation->fnGetTotalBreackExceeds($userid,$month,$year);
		$total_late_coming = $objCalculation->fnGetTotalLateComings($userid,$month,$year);
		$in_total = $objCalculation->fnGetTotalPresents($userid,$month,$year);
		$total_ppl = $objCalculation->fnGetTotalLeave($userid,$month,$year,'ppl');
		$total_uhl = $objCalculation->fnGetTotalLeave($userid,$month,$year,'uhl');
		$total_phl = $objCalculation->fnGetTotalLeave($userid,$month,$year,'phl');
		$wo = $objCalculation->fnGetTotalLeave($userid,$month,$year,'wo');
		$total_ph = $objCalculation->fnGetTotalLeave($userid,$month,$year,'ph');
		$ha = $objCalculation->fnGetTotalLeave($userid,$month,$year,'ha');
		$hlwp = $objCalculation->fnGetTotalLeave($userid,$month,$year,'hlwp');
		$abs = $objCalculation->fnGetTotalLeave($userid,$month,$year,'a');
		$plwp = $objCalculation->fnGetTotalLeave($userid,$month,$year,'plwp');
		$ulwp = $objCalculation->fnGetTotalLeave($userid,$month,$year,'ulwp');
		$total_upl = $objCalculation->fnGetTotalLeave($userid,$month,$year,'upl');
		$smplt = $objCalculation->fnGetTotalLeave($userid,$month,$year,'smplt');
		
		$weekOfDays = $objCalculation->fnGetWeekOfDates($userid,$month,$year);
		
		
		if(count($weekOfDays) > 0)
		{
			$cur = '2013-01-06';
			//$checkPrevDay = $objCalculation->fnCheckPreviousDate($userid,$cur);
			//echo 'hello'.$checkPrevDay; 
			$checkNextDay = $objCalculation->fnCheckNextDate($userid,$cur);
			//print_r($checkNextDay);
			/*foreach($weekOfDays as $ofDays)
			{
			//print_r($ofDays);
				//$prev_date = date('Y-m-d', strtotime('-1 day', strtotime($ofDays['date'])));
				//$next_date = date('Y-m-d', strtotime('+1 day', strtotime($ofDays['date'])));
				if($ofDays['date'] != '')
				{
					$checkPrevDay = $objCalculation->fnCheckPreviousDate($userid,$ofDays['date']);
				}
				
			}*/
		}
		
		
	

		$in_total_present = ($in_total + $wo + $total_ph);
		
		$total_avail_leave = $objCalculation->fnGetTotalLeaveAvails($userid);
		
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
		$leave_taken = ($deducted_late_comings_days + $deducted_break_exceed_days);
		
		
		
		$total_leave_taken = $total_ppl + $total_upl + $deducted_phl + $deducted_uhl + $leave_taken;
		
		/*echo '<pre>';
		echo '<br>total_avail_leave--------'.$total_avail_leave;
		echo '<br>userid--------'.$userid;
		echo '<br>total_ppl--------'.$total_ppl;
		echo '<br>total_upl--------'.$total_upl;
		echo '<br>eml_taken-------'.$eml_taken;
		echo '<br>total_phl-----'.$total_phl;
		echo '<br>deducted_phl-----'.$deducted_phl;
		echo '<br>uhl_taken------'.$total_uhl;
		echo '<br>deducted_uhl------'.$deducted_uhl;
		echo '<br>break_exceed_days------'.$break_exceed_days;
		echo '<br>deducted_break_exceed_days------'.$deducted_break_exceed_days;
		echo '<br>leave_taken------'.$leave_taken;
		echo '<br>total_leave_taken--------'.$total_leave_taken;*/
		
		if($total_leave_taken > $total_avail_leave)
		{	
			$total_absence = $total_leave_taken -  $total_avail_leave;
			$remaining_leave = 0;
		}
		else
		{	
			$remailning_leave = $total_avail_leave - $total_leave_taken;
			$total_absence = 0;
		}
		
		
		//$sub_final_total_present = $in_total_present - $total_absence;
		
		//echo '<br>sub_final_total_present------------'.$sub_final_total_present;

		//echo $total_absence = 10;
		$deducted_days = 0;
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
	
		//echo $total_late_coming;
		//$new_sub_final_total_present = $sub_final_total_present - $deducted_days;
		$leave_earns = 0;
		$eml_taken = 0;
		
		$final_total_presents =  ($in_total_present - $deducted_days);
		
		if($month == 11 || $month == 12)
		{
			if($final_total_presents > 15 && $final_total_presents < 26)
			{
				$earned_leaves = .5;
			}
			else if($final_total_presents > 26)
			{
				$earned_leaves = 1;
			}
			else
			{
				$earned_leaves = 0;
			}
		}
		else
		{
			if($final_total_presents > 15 && $final_total_presents < 26)
			{
				$earned_leaves = 1;
			}
			else if($final_total_presents > 26)
			{
				$earned_leaves = 2;
			}
			else
			{
				$earned_leaves = 0;
			}
		}
		
		$final_remaining_leaves = $remaining_leave + $earned_leaves;
		
		
		
		/*echo '<br>PH-----'.$total_ph;
		echo '<br>HA----'.$ha;
		echo '<br>HLWP-----'.$hlwp;
		echo '<br>A----'.$leave_taken;
		echo '<br>PLWP----'.$plwp;
		echo '<br>ULWP----'.$ulwp;
		echo '<br>TOTAL----'.$final_total_presents;
		echo '<br>total_absence----'.$total_absence;
		echo '<br>total_leave_taken======='.$total_leave_taken;
		echo '<br>deducted_days======='.$deducted_days;
		echo '<br>earned_leaves++++++++'.$earned_leaves;
		echo '<br>-----------------------------------------------------------------<br>';*/
		
		
		
		$summary = array("employee_id"=>$userid,"month"=>$month,"year"=>$year,"present"=>$in_total_present,"break_exceeds"=>$break_exceed_days,"total_plt"=>$total_late_coming,"ppl"=>$total_ppl,"uhl"=>$total_uhl,"wo"=>$wo,"ph"=>$total_ph,"ha"=>$ha,"hlwp"=>$hlwp,"abs"=>$total_absence,"plwp"=>$plwp,"ulwp"=>$ulwp,"total_present"=>$final_total_presents,"pay_days"=>$final_total_presents,"opening_leave"=>$total_avail_leave,"leave_earns"=>$earned_leaves,"pl_taken"=>$total_ppl,"eml_taken"=>$eml_taken,"phl_taken"=>$deducted_phl,"uhl_taken"=>$deducted_uhl,"total_leave_taken"=>$total_leave_taken,"closing_balance"=>$final_remaining_leaves);
		
		$checkRecordExistence = $objCalculation -> fnCheckExistence($userid,$month,$year);
		$insertRemainingLeaves = $objCalculation -> fnUpdateRemainingLeave($userid,$final_remaining_leaves);
		
		if(isset($checkRecordExistence) &&  $checkRecordExistence != '')
		{
			
			$updateSummary = $objCalculation -> fnUpdateSummary($checkRecordExistence,$summary);
		}
		else
		{
			$insertSummary = $objCalculation -> fnInsertSummary($summary);
		}
	}
	
	$getAllCalculation = $objCalculation -> fnGetAllCalculation($month,$year);
	$tpl->set_var("FillReport","");
	if(count($getAllCalculation) > 0 )
	{
		foreach($getAllCalculation as $calculations)
		{
			$tpl->setAllValues($calculations);
			$tpl->parse("FillReport",true);
		}
	}
	
	$tpl->pparse('main',false);
?>
