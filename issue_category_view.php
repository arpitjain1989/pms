<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('issue_category_view.html','main_container');

	/* Rights management */
	$PageIdentifier = "IssueCategory";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","View Issue Category");

	/* Set breadcrumb */
	$breadcrumb = '<li><a href="issue_category_list.php">Manage Issue Category</a></li><span class="divider"><span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Issue Category</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.issue_category.php');

	$objIssueCategory = new issue_category();

	$tpl->set_var("DisplayIssueCategoryInformationBlock","");
	$tpl->set_var("DisplayNoIssueCategoryInformationBlock","");

	if(isset($_REQUEST['id']) && trim($_REQUEST['id']) != "")
	{
		$arrIssueCategory = $objIssueCategory->fnGetIssueCategoryById($_REQUEST['id']);
		
		if(count($arrIssueCategory) > 0)
		{
			$tpl->SetAllValues($arrIssueCategory);
			$tpl->parse("DisplayIssueCategoryInformationBlock",false);
		}
		else
		{
			$tpl->parse("DisplayNoIssueCategoryInformationBlock",false);
		}
	}
	else
	{
		$tpl->parse("DisplayNoIssueCategoryInformationBlock",false);
	}
	
	$tpl->pparse('main',false);

?>
