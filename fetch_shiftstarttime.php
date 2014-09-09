<?php

	include('common.php');
	
	include_once('includes/class.shifts.php');
	
	$objShifts = new shifts();
	
	$starttime = "00:00";
	if(isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		$arrShift = $objShifts->fnGetShiftById($_REQUEST["id"]);
		if(count($arrShift) > 0)
		{
			$starttime = $arrShift["starttime"];
		}
	}
	
	echo $starttime;
	exit;

?>
