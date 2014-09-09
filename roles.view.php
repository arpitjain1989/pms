<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('roles.view.html','main_container');

	$PageIdentifier = "Roles";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Role");
	$breadcrumb = '<li><a href="roles.php">Manage Roles</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Role</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.roles.php');
	
	$objRoles = new roles();

	$arrRoles = $objRoles->fnGetRolesById($_REQUEST['id']);
	$arrGetAllModule = $objRoles->fnGetAllModulesById($_REQUEST['id']);
	
	/*if($arrRoles)
	{
		if(isset($arrRoles['status']) && $arrRoles['status'] == '0')
		{
			$tpl->set_var("stat","Active");
		}
		else
		{
			$tpl->set_var("stat","Active");
		}
	}*/
	
	foreach($arrRoles as $arrDepartmentvalue)
	{
		$tpl->SetAllValues($arrDepartmentvalue);
	}
	
	$tpl->set_var('FillModules','');
	if(count($arrGetAllModule)> 0)
	{
		foreach($arrGetAllModule as $Module)
		{
			//$tpl->SetAllValues($Module);
			//print_r($Module);
			$tpl->set_var("mod_title",$Module["title"]);
			$tpl->parse('FillModules',true);
		}	
	}

	$tpl->pparse('main',false);
?>
