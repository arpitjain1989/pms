<?php

	include('common.php');
	
	include_once('includes/class.shift_movement.php');
	
	$objShiftMovement = new shift_movement();
	
	$arrResult = array("movement_date"=>"-", "movement_starttime"=>"-", "movement_endtime"=>"-", "fromH"=>"-", "fromM"=>"-", "fromAMPM"=>"-", "toH"=>"-", "toM"=>"-", "toAMPM"=>"-");
	
	/* Set default compensation time */
	
	if(isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		$MovementInfo = $objShiftMovement->fnShiftMovementById($_REQUEST["id"]);
		
		if(count($MovementInfo) > 0)
		{
			$curdate = Date('Y-m-d');
			$fromtime = strtotime($curdate." ".$MovementInfo["movementfrom"].":00");
			$totime = strtotime($curdate." ".$MovementInfo["movementto"].":00");
			
			$pendingsec = $totime - $fromtime;
			$pendingtime = $pendingsec / 60;
			
			$minTime = 0;
			if($pendingtime <= 30)
				$minTime = 30;
			else if($pendingtime <= 60)
				$minTime = 60;
			else if($pendingtime <= 90)
				$minTime = 90;
			else if($pendingtime <= 120)
				$minTime = 120;
			
			$toH = Date('h');
			$toM = Date('i');
			$toAMPM = Date('a');
			
			$timespent = strtotime("-$minTime minutes");
			
			$fromH = Date('h',$timespent);
			$fromM = Date('i',$timespent);
			$fromAMPM = Date('a',$timespent);
			
			$arrResult = array("movement_date"=>$MovementInfo["movementdate"], "movement_starttime"=>$MovementInfo["movementfrom"], "movement_endtime"=>$MovementInfo["movementto"], "fromH"=>$fromH, "fromM"=>$fromM, "fromAMPM"=>$fromAMPM, "toH"=>$toH, "toM"=>$toM, "toAMPM"=>$toAMPM);
		}
	}
	
	echo json_encode($arrResult);

?>
