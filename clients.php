<?php 
	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('clients.html','main_container');

	$PageIdentifier = "Clients";
	include_once('userrights.php');

	$tpl->set_var("mainheading","Manage Clients");
	$breadcrumb = '<li class="active">Manage Clients</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.clients.php');
	
	$objClients = new clients();
	$arrAllClients = $objClients->fnGetAllClients();
	
	
	if(isset($_REQUEST['info']))
	{
		if($_REQUEST['info'] == 'succ')
		{
			$tpl->set_var('message',"Clients inserted successfully.");
		}
		else if($_REQUEST['info'] == 'update')
		{
			$tpl->set_var('message',"Clients updated successfully.");
		}
		else if($_REQUEST['info'] == 'delete')
		{
			$tpl->set_var('message',"Clients deleted successfully.");
		}
	}
	
	if(isset($_REQUEST['hdnaction']) && $_REQUEST['hdnaction'] = 'delete')
	{
		$delteclients = $objClients->fnDeleteClients($_POST);
		if($delteclients)
		{
			header("Location: clients.php?info=delete");
		}
		else
		{
			
		}
	}
	
	$tpl->set_var("FillClientsValues","");

	if(count($arrAllClients) >0)
	{
		foreach($arrAllClients as $arrAllClientsvalue)
		{	
			$tpl->SetAllValues($arrAllClientsvalue);
			if($arrAllClientsvalue['has_target'] == '0')
			{
				$tpl->set_var("state","Yes");
			}
			else
			{
				$tpl->set_var("state","No");
			}
			$tpl->parse("FillClientsValues",true);
		}
	}
	else
	{
		$tpl->set_var("FillHideDelete","");
		$tpl->set_var("norecord","No records found.");
	}
	
	
	
	
	$tpl->pparse('main',false);
?>