<?php

	include('common.php');
	
	include_once('includes/class.shift_movement.php');
	
	$objShiftMovement = new shift_movement();
	
	$sm = 0;
	if(isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "" && isset($_REQUEST["dt"]) && trim($_REQUEST["dt"]) != "")
	{
		$sm = $objShiftMovement->fnIsUserShiftMovementApprovedByDate(trim($_REQUEST["id"]), trim($_REQUEST["dt"]));
	}
	
	echo $sm;
	exit;

?>
