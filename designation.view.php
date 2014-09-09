<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('designation.view.html','main_container');

	$PageIdentifier = "Designation";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Designation");
	$breadcrumb = '<li><a href="designation.php">Manage Designation</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Designation</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.designation.php');
	
	$objDesignation = new designations();
	
	$arrDesignations = $objDesignation->fnGetDesignationById($_REQUEST['id']);
	
	$tpl->set_var("DisplayDesignationBlock","");
	
	if(count($arrDesignations) > 0)
	{
		$tpl->SetAllValues($arrDesignations);

		if($arrDesignations["allow_delegation"] == "1")
		{
			$strDelegateDesignations = "";
			$comma = "";

			$arrDelegateDesignations = explode(",",$arrDesignations["delegation_designation"]);
			foreach($arrDelegateDesignations as $DesignationId)
			{
				$tmpName = $objDesignation->fnGetDesignationNameById($DesignationId);
				if($tmpName != "")
				{
					$strDelegateDesignations .= $comma . $tmpName;
					$comma = ", ";
				}
			}

			$tpl->set_var("strDelegateDesignations", $strDelegateDesignations);
			$tpl->parse("DisplayDesignationBlock",false);
		}
	}

	$tpl->pparse('main',false);
?>
