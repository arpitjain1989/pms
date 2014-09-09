<?php

	include('common.php');

	$tpl = new Template($app_path);

	$tpl->load_file('team_shift_time_detail.html','main');

	//$PageIdentifier = "Roster";
	//include_once('userrights.php');

	include_once('includes/class.employee.php');
	include_once('includes/class.shifts.php');
	
	$objEmployee = new employee();
	$objShifts = new shifts();
	
	$tpl->set_var("DisplayShiftInformation","");
	$tpl->set_var("DisplayNoShiftInformation","");
	
	if(isset($_POST["action"]) && trim($_POST["action"]) == "AllowedShiftTime")
	{
		if($objShifts->fnAddAllowedShiftTime($_POST))
		{
			header("Location: team_shift_time.php?info=succ");
			exit;
		}
		else
		{
			header("Location: team_shift_time.php?info=err");
			exit;
		}
	}
	
	if(isset($_REQUEST["v"]) && trim($_REQUEST["v"]))
	{
		$tpl->set_var("DisplayShiftTime","");
		
		$tpl->set_var("headid",trim($_REQUEST["v"]));
		
		$EmployeeName = $objEmployee->fnGetEmployeeNameById(trim($_REQUEST["v"]));
		$tpl->set_var("teamleadername",$EmployeeName);
		
		$arrShifts = $objShifts->fnGetAllShifts(false);
		
		$arrUserShifts = $objShifts->fnAllowedShiftsByHeadId(trim($_REQUEST["v"]));
		
		if(count($arrShifts) > 0)
		{
			foreach($arrShifts as $curshift)
			{
				$tpl->set_var("shiftid",$curshift["id"]);
				$tpl->set_var("shifttitle",$curshift["title"] . " [".$curshift["starttime"]." : ".$curshift["endtime"]."]");
				
				$strChecked = "";
				if(in_array($curshift["id"],$arrUserShifts))
						$strChecked = "checked='checked'";
				
				$tpl->set_var("strChecked",$strChecked);
				$tpl->parse("DisplayShiftTime",true);
			}
			
			$tpl->parse("DisplayShiftInformation",false);
		}
		else
		{
			$tpl->parse("DisplayNoShiftInformation",false);
		}
	}
	else
	{
		$tpl->parse("DisplayNoShiftInformation",false);
	}
	
	$tpl->pparse('main',false);
?>
