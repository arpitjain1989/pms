<?php 
	include('common.php');

	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('shift_movement_view.html','main_container');

	$PageIdentifier = "ShiftMovement";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Shift Movement");
	$breadcrumb = '<li><a href="shift_movement_list.php">Manage Shift Movement</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Shift Movement</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.shift_movement.php');

	$objShiftMovement = new shift_movement();

	$tpl->set_var("DisplayMovementInformationBlock","");
	$tpl->set_var("DisplayNoMovementBlock","");
	$tpl->set_var("DisplayDelegatedTlBlock","");
	$tpl->set_var("DisplayDelegatedManagerBlock","");
	$tpl->set_var("DisplayApprovalDetailsBlock","");
	$tpl->set_var("DisplayAdminNoteBlock","");
	$tpl->set_var("DisplayTeamLeaderBlock","");
	$tpl->set_var("DisplayManagerBlock","");

	if(isset($_REQUEST["id"]))
	{
		$MovementInfo = $objShiftMovement->fnUserShiftMovementById($_REQUEST["id"]);

		if($MovementInfo)
		{
			$tpl->SetAllValues($MovementInfo);

			if(isset($MovementInfo["isadminadded"]) && trim($MovementInfo["isadminadded"]) == "1")
			{
				/* If added by admin, display the admin note. */
				$tpl->parse("DisplayAdminNoteBlock",false);
			}
			else
			{

				if(isset($MovementInfo["reportinghead1"]) && $MovementInfo["reportinghead1"] != "0" && $MovementInfo["reportinghead1"] != "")
				{
					$tpl->parse("DisplayTeamLeaderBlock",false);
				}

				if(isset($MovementInfo["reportinghead2"]) && $MovementInfo["reportinghead2"] != "0" && $MovementInfo["reportinghead2"] != "")
				{
					$tpl->parse("DisplayManagerBlock",false);
				}

				if(isset($MovementInfo["delegatedtl_id"]) && $MovementInfo["delegatedtl_id"] != "0" && $MovementInfo["delegatedtl_id"] != "")
				{
					$tpl->parse("DisplayDelegatedTlBlock",false);
				}

				if(isset($MovementInfo["delegatedmanager_id"]) && $MovementInfo["delegatedmanager_id"] != "0" && $MovementInfo["delegatedmanager_id"] != "")
				{
					$tpl->parse("DisplayDelegatedManagerBlock",false);
				}
			}
			$tpl->parse("DisplayMovementInformationBlock",false);
		}
		else
		{
			$tpl->parse("DisplayNoMovementBlock",false);
		}
	}
	else
	{
		$tpl->parse("DisplayNoMovementBlock",false);
	}

	$tpl->pparse('main',false);
?>
