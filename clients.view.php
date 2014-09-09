<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('clients.view.html','main_container');

	$PageIdentifier = "Clients";
	include_once('userrights.php');

	$tpl->set_var("mainheading","View Clients");
	$breadcrumb = '<li><a href="clients.php">Manage Clients</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">View Clients</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.clients.php');
	
	$objDepartment = new clients();
	
	$arrClients = $objDepartment->fnGetClientsById($_REQUEST['id']);

	if($arrClients)
	{
		if($arrClients[0][overtime] == '0')
		{
			$tpl->set_var("newovertime","Yes");
		}
		else
		{
			$tpl->set_var("newovertime","No");
		}
		if($arrClients[0]['rework'] == '0')
		{
			$tpl->set_var("newrework","Yes");
		}
		else
		{
			$tpl->set_var("newrework","No");
		}
	}
	
	foreach($arrClients as $arrDepartmentvalue)
	{
		$tpl->SetAllValues($arrDepartmentvalue);
	}

	$tpl->pparse('main',false);
?>