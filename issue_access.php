<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('issue_access.html','main_container');

	/* Rights management */
	$PageIdentifier = "IssueAccess";
	include_once('userrights.php');

	/* Set heading */
	$tpl->set_var("mainheading","Issue Access");
	
	/* Set breadcrumb */
	$breadcrumb = '</span><li class="active">Issue Access</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	/* Display message block */
	$tpl->set_var("DisplayMessageBlock","");
	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'success':
				$messageClass = "alert-success";
				$message = "Issue access saved successfully.";
				break;
		}

		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	include_once('includes/class.designation.php');

	$objDesignation = new designations();

	/* Fill designation dropdown */
	$tpl->set_var("FillDesignationIdBlock","");
	$arrDesignation = $objDesignation->fnGetAllDesignations();
	if(count($arrDesignation) > 0)
	{
		foreach($arrDesignation as $curDesignation)
		{
			$tpl->set_var("designation_id",$curDesignation["id"]);
			$tpl->set_var("designation_name",$curDesignation["title"]);

			$tpl->parse("FillDesignationIdBlock",true);
		}
	}

	$tpl->pparse('main',false);

?>
