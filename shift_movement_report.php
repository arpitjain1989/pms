<?php

	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('shift_movement_report.html','main_container');
	
	$PageIdentifier = "ShiftMovementReport";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Shift Movement Report");
	$breadcrumb = '<li class="active">Shift Movement Report</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once("includes/class.shift_movement.php");
	
	$objShiftMovement = new shift_movement();

	$curYear = Date('Y');
	$curMonth = Date('m');

	/* Get current year and previous year */
	$arrYear = array($curYear, $curYear-1);

	/* Search late comings */
	if(isset($_POST["action"]) && trim($_POST["action"]) == "shiftMovementSearch")
	{
		$_SESSION["ShiftMovementReport"]["month"] = $_POST["month"];
		$_SESSION["ShiftMovementReport"]["year"] = $_POST["year"];
		
		header("Location: shift_movement_report.php");
		exit;
	}
	
	if(!isset($_SESSION["ShiftMovementReport"]["month"]))
		$_SESSION["ShiftMovementReport"]["month"] = $curMonth;

	if(!isset($_SESSION["ShiftMovementReport"]["year"]))
		$_SESSION["ShiftMovementReport"]["year"] = $curYear;
	
	$year = $_SESSION["ShiftMovementReport"]["year"];
	$month = $_SESSION["ShiftMovementReport"]["month"];
	
	$tpl->set_var("year",$year);
	$tpl->set_var("month",$month);
	
	$shiftMovementFromAttendance = $objShiftMovement->fnGetAllShiftMovementsEmployee($year, $month);


	$tpl->set_var("DisplayShiftMovement","");
	$tpl->set_var("NoDisplayShiftMovement","");
	
	$tpl->set_var("FillMovementInformation","");

	if(count($shiftMovementFromAttendance) > 0)
	{
		foreach($shiftMovementFromAttendance as $key=>$shiftMovementAttendance)
		{
			//echo '<pre>'; echo 'key:'.$key; print_r($shiftMovementAttendance);
			$tpl->set_var("employeename",$shiftMovementAttendance["name"]);
			$tpl->set_var("FillShiftMovementDetail","");
			foreach($shiftMovementAttendance['date'] as $dates)
			{
				$shiftMovementAndComponsation = $objShiftMovement->fnGetAllShiftMovementAndComponsations($key,$dates);
				
				if($shiftMovementAndComponsation > 0)
				{
					foreach($shiftMovementAndComponsation as $curInfo)
					{
						$tpl->set_var("movementDate", $curInfo["mov_date"]);
						$tpl->set_var("componsationDate", $curInfo["comp_date"]);


						if($curInfo['approvedby_tl'] == '1' || ($curInfo['approvedby_tl'] == '0' && $curInfo['delegatedtl_id'] !='' && $curInfo['delegatedtl_status'] =='1'))
						{
							$tpl->set_var("finalStaus", 'Approved');
						}
						else if($curInfo['approvedby_tl'] == '2' || ($curInfo['approvedby_tl'] == '0' && $curInfo['delegatedtl_id'] !='' && $curInfo['delegatedtl_status'] =='2'))
						{
							$tpl->set_var("finalStaus", 'Un-Approved');
						}
						else
						{
							$tpl->set_var("finalStaus", 'Pending');
						}


						
						
						$tpl->parse("FillShiftMovementDetail",true);
					}
				}	
				//echo '<pre>'; print_r($shiftMovementAndComponsation);
				
			}
			
			/*$shiftMovementAndComponsation  =  $objShiftMovement->fnGetAllShiftMovementAndComponsations($shiftMovementAttendance['eid'],$shiftMovementAttendance['at_date']);
			//echo '<pre>'; print_r($shiftMovementAndComponsation);
			$tpl->set_var("FillShiftMovementDetail","");
			if($shiftMovementAndComponsation > 0)
			{
				foreach($shiftMovementAndComponsation as $curInfo)
				{
					$tpl->set_var("movementDate", $curInfo["mov_date"]);
					$tpl->set_var("componsationDate", $curInfo["comp_date"]);
					
					$tpl->parse("FillShiftMovementDetail",true);
				}
			}*/
			
			$tpl->parse("FillMovementInformation",true);
		}
		$tpl->parse("DisplayShiftMovement",true);
	}
	else
	{
		$tpl->parse("NoDisplayShiftMovement",true);
	}

	/* Fill year dropdown */
	$tpl->set_var("DisplayYearBlock","");
	if(count($arrYear) > 0)
	{
		foreach($arrYear as $yr)
		{
			$tpl->set_var("curyr", $yr);
			
			$tpl->parse("DisplayYearBlock",true);
		}
	}
	
	$tpl->pparse('main',false);

?>
