<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('issue_category_list.html','main_container');

	/* Rights management */
	$PageIdentifier = "IssueCategory";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","Manage Issue Category");

	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Manage Issue Category</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.issue_category.php');

	/* Display message block */
	$tpl->set_var("DisplayMessageBlock","");
	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'success':
				$messageClass = "alert-success";
				$message = "Issue Category added successfully";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Issue Category already added. Cannot add again.";
				break;
		}

		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	$objIssueCategory = new issue_category();
	$arrIssueCategory = $objIssueCategory->fnGetAllIssueCategory();

	/* Display list */
	$tpl->set_var("FillIssueCategoryList","");
	if(count($arrIssueCategory) >0)
	{
		foreach($arrIssueCategory as $curIssueCategory)
		{
			$tpl->SetAllValues($curIssueCategory);
			$tpl->parse("FillIssueCategoryList",true);
		}
	}

	$tpl->pparse('main',false);

?>
