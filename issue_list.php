<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('issue_list.html','main_container');

	/* Rights management */
	$PageIdentifier = "ITIssues";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","Manage IT Issues");

	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Manage IT Issues</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.issue.php');

	/* Display message block */
	$tpl->set_var("DisplayMessageBlock","");
	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'success':
				$messageClass = "alert-success";
				$message = "Issue added successfully";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Issue already added. Cannot add again.";
				break;
		}

		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	$objIssue = new issue();
	$arrIssue = $objIssue->fnGetAllIssue();

	/* Display list */
	$tpl->set_var("FillIssueList","");
	if(count($arrIssue) >0)
	{
		foreach($arrIssue as $curIssue)
		{
			$tpl->SetAllValues($curIssue);
			$tpl->parse("FillIssueList",true);
		}
	}

	$tpl->pparse('main',false);

?>
