<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('clients.add.html','main_container');

	$PageIdentifier = "Clients";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Add / Edit Clients");
	$breadcrumb = '<li><a href="clients.php">Manage Clients</a></li><span class="divider"> <span class="icon16 icomoon-icon-arrow-right"></span></span><li class="active">Add / Edit Clients</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.clients.php');
	
	$objClients = new clients();
	
	
	if(isset($_REQUEST['id']))
	{
		$tpl->set_var('clientsid',"$_REQUEST[id]");
	}
	$tpl->set_var('action','hdnadd');
	if(isset($_POST['hdnaction']) && $_POST['hdnaction'] == 'hdnadd')
	{
		$insertdata = $objClients->fnInsertClients($_POST);
		if($insertdata)
		{
			header("Location: clients.php?info=succ");
			exit;
		}
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] =='update')
	{
			$updateClients = $objClients->fnUpdateClients($_POST);
			if($updateClients)
		{
			header("Location: clients.php?info=update");
			exit;
		}
	}
	
	if(isset($_REQUEST['action']) && $_REQUEST['action']='update')
	{
	$arrClients = $objClients->fnGetClientsById($_REQUEST['id']);
	foreach($arrClients as $arrClientsvalue)
	{
		$tpl->SetAllValues($arrClientsvalue);
	}
		$tpl->set_var('action','update');
	}
	
	
	
	
	
	$tpl->pparse('main',false);
?>