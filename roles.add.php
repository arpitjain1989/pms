<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('roles.add.html','main_container');

	$PageIdentifier = "Roles";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Add / Edit Roles");
	$breadcrumb = '<li><a href="roles.php">Manage Roles</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add / Edit Roles</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.roles.php');
	
	$objRoles = new roles();
	
	$arrGetAllModule = $objRoles->fnGetAllModules();
	
	if(isset($_REQUEST['id']))
	{
		$tpl->set_var('rolesid',"$_REQUEST[id]");
	}
	$tpl->set_var('action','hdnadd');
	if(isset($_POST['hdnaction']) && $_POST['hdnaction'] == 'hdnadd')
	{
		$insertdata = $objRoles->fnInsertRoles($_POST);
		
		$insertRolDetails = $objRoles->fnInsertRoleDetails($insertdata,$_POST['modules']);

		if($insertdata)
		{
			header("Location: roles.php?info=succ");
			exit;
		}
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] =='update')
	{
			$updateRoles = $objRoles->fnUpdateRoles($_POST);
			$updateRolesDetails = $objRoles->fnUpdateRolesDetails($_POST);
			
			if($updateRoles)
		{
			header("Location: roles.php?info=update");
			exit;
		}
	}
	
	if(isset($_REQUEST['action']) && $_REQUEST['action']='update')
	{
		$arrRoles = $objRoles->fnGetRolesById($_REQUEST['id']);
		$arrAllRolesvalues = $objRoles->fnGetAllRoleDetails($_REQUEST['id']);
		
		foreach($arrRoles as $arrRolesvalue)
		{
			$tpl->SetAllValues($arrRolesvalue);
		}
		$tpl->set_var('action','update');
	}

	$tpl->set_var('FillModules','');
	if(count($arrGetAllModule)> 0)
	{
		foreach($arrGetAllModule as $Module)
		{
			if(isset($arrAllRolesvalues))
			{
				if(in_array($Module['id'],$arrAllRolesvalues))
				{
					$tpl->set_var('check','checked = "checked"');
				}
				else
				{
					$tpl->set_var('check','');
				}
			}
			$tpl->setAllValues($Module);
			$tpl->parse('FillModules',true);
		}
	}
	
	$tpl->pparse('main',false);
?>