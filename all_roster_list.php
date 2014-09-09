<?php
	include('common.php');

	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('all_roster_list.html','main_container');

	$PageIdentifier = "ViewAllRoster";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View All Rosters");
	$breadcrumb = '<li class="active">View All Rosters</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.roster.php');

	$objRoster = new roster();
	$arrRoster = $objRoster->fnGetAllRosters();

	$tpl->set_var("FillRosters","");
	if(count($arrRoster) > 0)
	{
		foreach($arrRoster as $RosterDays)
		{
			$tpl->setAllValues($RosterDays);
			$tpl->parse("FillRosters",true);
		}
	}

	$tpl->pparse('main',false);
?>
