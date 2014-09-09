<?php 

	include('common.php');

	include_once('includes/class.shifts.php');
	
	$objShifts = new shifts();
	
	//echo '<pre>'; print_r($_REQUEST['v']);
	if($_REQUEST['v'] != '')
	{
		$arrEmpTest = $objShifts->fnAllowedShiftsByHeadId($_REQUEST['v']);
	}
	else
	{
		$arrEmpTest = $objShifts->fnGetAllShifts();
	}

	if(count($arrEmpTest) > 0)
	{
		$print =  '<select id="shift_timning_by_manager" name="shift_timning_by_manager">';
		$print .=  '<option value="0">Please select</option>';
		foreach($arrEmpTest as $empShifts)
		{
			$shifts = $objShifts->fnGetShiftById($empShifts);
			
			$print .=  '<option value="'.$empShifts.'">'.$shifts['title'].'</option>';
			
		}
		$print .=  '<select>';
	}
	else
	{
		$print =  '<select id="shift_timning_by_manager" name="shift_timning_by_manager">';
		$print .=  '<option value="0">Please select</option>';
		$print .=  '<select>';
	}
	echo $print;
	
?>
