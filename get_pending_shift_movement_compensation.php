<?php

	include_once('includes/class.shift_movement.php');
	$objShiftMovement = new shift_movement();
	
	$Date = Date('Y-m-d');

	$txtShiftMovementCompensation = "<select name='shift_movement_id' id='shift_movement_id' onchange='javascript: fnMovementChange($(this));'>";
	$txtShiftMovementCompensation .= "<option value=''>Please Select</option>";

	if(isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		$arrShiftMovement = $objShiftMovement->fnGetPendingCompensationMovementByUser($_REQUEST["id"]);
		//print_r($arrShiftMovement);
		if(count($arrShiftMovement) > 0)
		{
			foreach($arrShiftMovement as $curShiftMovement)
			{
				$txtShiftMovementCompensation .= "<option value='".$curShiftMovement["id"]."'>".$curShiftMovement["movement_date"]."</option>";
			}
		}
	}
	
	echo $txtShiftMovementCompensation .= "</select>";
	exit;

?>
