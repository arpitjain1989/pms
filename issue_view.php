<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('issue_view.html','main_container');

	/* Rights management */
	$PageIdentifier = "ITIssues";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","View IT Issue");

	/* Set breadcrumb */
	$breadcrumb = '<li><a href="issue_list.php">Manage IT Issue</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View IT Issue</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.issue.php');

	$objIssue = new issue();

	$tpl->set_var("DisplayIssueInformationBlock","");
	$tpl->set_var("DisplayNoIssueInformationBlock","");

	if(isset($_REQUEST['id']) && trim($_REQUEST['id']) != "")
	{
		$arrIssue = $objIssue->fnGetIssueById($_REQUEST['id']);
		
		if(count($arrIssue) > 0)
		{
			$tpl->SetAllValues($arrIssue);
			$tpl->parse("DisplayIssueInformationBlock",false);
		}
		else
		{
			$tpl->parse("DisplayNoIssueInformationBlock",false);
		}
	}
	else
	{
		$tpl->parse("DisplayNoIssueInformationBlock",false);
	}
	
	$tpl->pparse('main',false);

?>
