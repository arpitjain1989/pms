<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('issue_category.html','main_container');

	/* Rights management */
	$PageIdentifier = "IssueCategory";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","Issue Category");
	
	/* Set breadcrumb */
	$breadcrumb = '<li><a href="issue_category_list.php">Manage Issue Category</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Issue Category</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.issue_category.php');

	$objIssueCategory = new issue_category();

	/* Set values for update mode */
	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "update" && isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != "")
	{
		$issue_category = $objIssueCategory->fnGetIssueCategoryById($_REQUEST["id"]);
		if(count($issue_category) > 0)
		{
			$tpl->SetAllValues($issue_category);
		}
	}

	/* Save issue category */
	if(isset($_POST["action"]) && trim($_POST["action"]) == "IssueCategory")
	{
		$issue_category_status = $objIssueCategory->fnSaveIssueCategory($_POST);

		if($issue_category_status == 1)
		{
			header("Location: issue_category_list.php?info=success");
			exit;
		}
		else if($issue_category_status == 0)
		{
			header("Location: issue_category_list.php?info=err");
			exit;
		}
	}

	$tpl->pparse('main',false);

?>
