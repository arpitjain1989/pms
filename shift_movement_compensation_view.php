<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('shift_movement_compensation_view.html','main_container');

	$PageIdentifier = "ShiftMovementCompensation";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Shift Movement Compensation");
	$breadcrumb = '<li><a href="shift_movement_compensation_list.php">Manage Compensation</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Compensation</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.shift_movement.php');

	$objShiftMovement = new shift_movement();
	
	$tpl->set_var("DisplayMovementCompensationInformationBlock","");
	$tpl->set_var("DisplayNoMovemenCompensationtBlock","");
	$tpl->set_var("DisplayDelegatedReportingHead","");
	$tpl->set_var("DisplayAdminNoteBlock","");
	$tpl->set_var("DisplayReportingHead","");
	
	if(isset($_REQUEST["id"]))
	{
		$CompensationInfo = $objShiftMovement->fnUserShiftMovementCompensationById($_REQUEST["id"]);

		if(count($CompensationInfo) > 0)
		{
			$tpl->SetAllValues($CompensationInfo);

			if(isset($CompensationInfo["isadminadded"]) && trim($CompensationInfo["isadminadded"]) == "1")
			{
				/* If added by admin, display the admin note. */
				$tpl->parse("DisplayAdminNoteBlock",false);
			}
			else
			{
				$tpl->parse("DisplayReportingHead",false);
				if($CompensationInfo["delegatedtl_id"] != "0" && $CompensationInfo["delegatedtl_id"] != "")
				{
					$tpl->parse("DisplayDelegatedReportingHead",false);
				}
			}

			$tpl->parse("DisplayMovementCompensationInformationBlock",false);
		}
		else
		{
			$tpl->parse("DisplayNoMovemenCompensationtBlock",false);
		}
	}
	else
	{
		$tpl->parse("DisplayNoMovemenCompensationtBlock",false);
	}
	
	$tpl->pparse('main',false);
?>
