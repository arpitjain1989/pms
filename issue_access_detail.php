<?php

	include('common.php');

	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('issue_access_detail.html','main');

	include_once('includes/class.issue_category.php');
	include_once('includes/class.issue.php');
	include_once('includes/class.designation.php');

	$objIssueCategory = new issue_category();
	$objIssue = new issue();
	$objDesignation = new designations();

	/* Hide blocks */
	$tpl->set_var("DisplayIssueInformationBlock","");
	$tpl->set_var("DisplayNoIssueInformationBlock","");

	if(isset($_POST["action"]) && trim($_POST["action"]) == "AllowIssueAccess")
	{
		$objIssue->fnSaveIssueAccess($_POST["designation_id"], $_POST["chkissue"]);
		header("Location: issue_access.php?info=success");
		exit;
	}

	if(isset($_REQUEST["designationId"]) && trim($_REQUEST["designationId"]) != "")
	{
		$arrDesignation = $objDesignation->fnGetDesignationById($_REQUEST["designationId"]);
		/*if(isset($arrDesignation))
			$arrDesignation = array_pop($arrDesignation);*/

		if(is_array($arrDesignation) && count($arrDesignation) > 0)
		{
			$tpl->set_var("designation_id",$arrDesignation["id"]);
			$tpl->set_var("designation",$arrDesignation["title"]);

			$arrIssueAccess = $objIssue->fnGetIssueAccessByDesignationId($arrDesignation["id"]);

			/* Display Issue category */
			$tpl->set_var("DisplayIssueCategory","");
			$arrIssueCategory = $objIssueCategory->fnGetAllIssueCategory();
			if(count($arrIssueCategory) > 0)
			{
				foreach($arrIssueCategory as $curIssueCategory)
				{
					$tpl->set_var("issue_category_name",$curIssueCategory["issue_category"]);

					$tpl->set_var("DisplayIssueBlock","");
					$tpl->set_var("DisplayNoIssueBlock","");

					$arrIssue = $objIssue->fnGetIssueByCategoryId($curIssueCategory["id"]);
					if(count($arrIssue) > 0)
					{
						foreach($arrIssue as $curIssue)
						{
							$tpl->set_var("issue_id", $curIssue["id"]);
							$tpl->set_var("issue_name", $curIssue["issue"]);

							$setchecked = "";
							if(in_array($curIssue["id"],$arrIssueAccess))
								$setchecked = "checked='checked'";

							$tpl->set_var("setchecked", $setchecked);

							$tpl->parse("DisplayIssueBlock",true);
						}
					}
					else
					{
						$tpl->parse("DisplayNoIssueBlock",false);
					}

					$tpl->parse("DisplayIssueCategory",true);
				}
				
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
	}
	else
	{
		$tpl->parse("DisplayNoIssueInformationBlock",false);
	}

	$tpl->pparse('main',false);

?>
