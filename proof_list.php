<?php

	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('proof_list.html','main_container');

	$PageIdentifier = "Proof";
	include_once('userrights.php');
	
	$tpl->set_var("mainheading","Manage Proof");
	$breadcrumb = '<li class="active">Manage Proof</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.proof.php');

	$tpl->set_var("DisplayMessageBlock","");
	
	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'success':
				$messageClass = "alert-success";
				$message = "Proof added successfully";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Proof already added. Cannot add again.";
				break;
		}

		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);	
		}
	}

	$objProof = new proof();
	$arrProof = $objProof->fnGetAllProof();

	$tpl->set_var("FillProofList","");
	if(count($arrProof) >0)
	{
		$arrTypeText = array("1"=>"ID Proof", "2"=>"Address Proof");
		foreach($arrProof as $curProof)
		{	
			$tpl->set_var("proof_type",$arrTypeText[$curProof["type"]]);
			$tpl->SetAllValues($curProof);
			$tpl->parse("FillProofList",true);
		}
	}

	$tpl->pparse('main',false);

?>
