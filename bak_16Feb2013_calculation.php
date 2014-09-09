<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	//$tpl->load_file('attendance.view.html','main_container');

	include_once('userrights.php');

	include_once('includes/class.calculation.php');
	
	$objCalculation = new calculation();
	
	$month = '01';
	$year = '2013';
	
	$employeeList = $objCalculation -> fnGetEmployees();
	
	foreach($employeeList as $employee)
	{
		
		$userid = $employee['id'];
		$total_break_exceed = $objCalculation->fnGetTotalBreackExceeds($userid,$month,$year);
		$total_late_coming = $objCalculation->fnGetTotalLateComings($userid,$month,$year);
		$in_total_present = $objCalculation->fnGetTotalPresents($userid,$month,$year);
		$total_ppl = $objCalculation->fnGetTotalLeave($userid,$month,$year,'ppl');
		$total_ph = $objCalculation->fnGetTotalLeave($userid,$month,$year,'ph');
		$total_upl = $objCalculation->fnGetTotalLeave($userid,$month,$year,'upl');
		$total_phl = $objCalculation->fnGetTotalLeave($userid,$month,$year,'phl');
		$total_uhl = $objCalculation->fnGetTotalLeave($userid,$month,$year,'uhl');
		$wo = $objCalculation->fnGetTotalLeave($userid,$month,$year,'wo');
		$ha = $objCalculation->fnGetTotalLeave($userid,$month,$year,'ha');
		$hlwp = $objCalculation->fnGetTotalLeave($userid,$month,$year,'hlwp');
		$ulwp = $objCalculation->fnGetTotalLeave($userid,$month,$year,'ulwp');
		$plwp = $objCalculation->fnGetTotalLeave($userid,$month,$year,'plwp');
		$smplt = $objCalculation->fnGetTotalLeave($userid,$month,$year,'smplt');
		$abs = $objCalculation->fnGetTotalLeave($userid,$month,$year,'a');
		
		
		$total_avail_leave = $objCalculation->fnGetTotalLeaveAvails($userid,$month,$year,'upl');
		
		if($total_phl > 0)
		{
			$deducted_phl = $total_phl * .5 ;
		}
		if($total_uhl > 0)
		{
			$deducted_uhl = $total_uhl * .5 ;
		}
		
		$total_leave_taken = $total_ppl + $total_upl + $deducted_phl + $deducted_uhl;
		
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
		
		$sub_final_total_present = $in_total_present - $total_absence;
		//echo $total_absence = 10;
		$deducted_days =0;
		if($total_absence > 3)
		{
			$deducted_days = (3 * 1.25);
			$total_absence = $total_absence -3;
			if($total_absence > 0)
			{
				if($total_absence > 2)
				{
					$deducted_days = ($deducted_days + (2 * 1.50));
					$total_absence = $total_absence -2;	
						if($total_absence > 0)
						{
								$deducted_days = ($deducted_days + ($total_absence * 2));
						}
				}
				else
				{	
					
					$total_absence * 1.50;
					$deducted_days = ($deducted_days + ($total_absence * 1.50));
				}
			}
		}
		else
		{
			$deducted_days = ($total_absence + ($total_absence * .25));
		}
		//echo '<br>'.$deducted_days;
		//echo $total_late_coming;
		$new_sub_final_total_present = $sub_final_total_present - $deducted_days;
		
		$deducted_late_comings = '';
		$deducted_late_comings_days = '';
		$break_exceed_days = '';
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
		$final_total_presents =  ($new_sub_final_total_present - $leave_taken);
		
		$leave_earns = 0;
		$eml_taken = 0;
		
		/*echo '<pre>';
		echo 'total_break_exceed------'.$total_break_exceed;
		echo '<br>total_late_coming-------'.$total_late_coming;
		echo '<br>in_total_present--------'.$in_total_present;
		echo '<br>total_ppl-----'.$total_ppl;
		echo '<br>total_ph-----'.$total_ph;
		echo '<br>total_upl-----'.$total_upl;
		echo '<br>total_phl----'.$total_phl;
		echo '<br>total_uhl-----'.$total_uhl;
		echo '<br>remailning_leave----'.$remailning_leave;
		echo '<br>total_absence----'.$total_absence;*/
		
		$summary = array("employee_id"=>$userid,"month"=>$month,"year"=>$year,"present"=>$in_total_present,"total_plt"=>$total_late_coming,"ppl"=>$total_ppl,"uhl"=>$total_uhl,"wo"=>$wo,"ph"=>$total_ph,"ha"=>$ha,"hlwp"=>$hlwp,"abs"=>$total_absence,"plwp"=>$plwp,"ulwp"=>$ulwp,"total_present"=>$final_total_presents,"pay_days"=>$final_total_presents,"opening_leave"=>$total_avail_leave,"leave_earns"=>$leave_earns,"pl_taken"=>$total_ppl,"eml_taken"=>$eml_taken,"phl_taken"=>$total_phl,"uhl_taken"=>$total_uhl,"total_leave_taken"=>$leave_taken,"closing_balance"=>$remaining_leave);
		
		$checkRecordExistence = $objCalculation -> fnCheckExistence($userid,$month,$year);
		
		if(isset($checkRecordExistence) &&  $checkRecordExistence != '0')
		{
			$updateSummary = $objCalculation -> fnUpdateSummary($checkRecordExistence,$summary);
		}
		else
		{
			$insertSummary = $objCalculation -> fnInsertSummary($summary);
		}
		/*echo '<pre>';
		print_r($summary);
		die;*/
	}
	
	$tpl->pparse('main',false);
?>
