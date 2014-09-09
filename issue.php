<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('issue.html','main_container');

	/* Rights management */
	$PageIdentifier = "ITIssues";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","IT Issues");
	
	/* Set breadcrumb */
	$breadcrumb = '<li><a href="issue_list.php">Manage IT Issue</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">IT Issue</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.issue.php');
	include_once('includes/class.issue_category.php');

	$objIssue = new issue();
	$objIssueCategory = new issue_category();

	/* Set values for update mode */
	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "update" && isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		$issue = $objIssue->fnGetIssueById($_REQUEST["id"]);
		if(count($issue) > 0)
		{
			$tpl->SetAllValues($issue);
		}
	}

	/* Save issue */
	if(isset($_POST["action"]) && trim($_POST["action"]) == "SaveITIssue")
	{
		$issue_status = $objIssue->fnSaveIssue($_POST);

		if($issue_status == 1)
		{
			header("Location: issue_list.php?info=success");
			exit;
		}
		else if($issue_status == 0)
		{
			header("Location: issue_list.php?info=err");
			exit;
		}
	}
	
	/* Fill issue category */
	$tpl->set_var("FillIssueCategoryBlock","");
	$arrIssueCategory = $objIssueCategory->fnGetAllIssueCategory();
	if(count($arrIssueCategory) > 0)
	{
		foreach($arrIssueCategory as $curIssueCategory)
		{
			$tpl->set_var("issuecategory_id",$curIssueCategory["id"]);
			$tpl->set_var("issuecategory_name",$curIssueCategory["issue_category"]);
			
			$tpl->parse("FillIssueCategoryBlock", true);
		}
	}

	$tpl->pparse('main',false);

?>
