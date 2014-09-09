<?php 
	include('common.php');

	$tpl = new Template($app_path);

	$tpl->load_file("leveldetail.html","main");

	include("includes/class.quality_form.php");
	$objQualityForm = new quality_form();

	$arrAllMemberAfdValues = $objQualityForm->getAllMemberAfdValues($_REQUEST['paraid'],$_REQUEST['recid'],$_REQUEST['date']);
	$tpl->set_var("FillAfdValues","");
	$tpl->set_var("FillNoRecord","");
	if(count($arrAllMemberAfdValues) > 0)
	{
		foreach($arrAllMemberAfdValues as $arrAfdValues)
		{
			if($arrAfdValues['haserror'] == '1')
			{
				$tpl->set_var("haserr","Yes");
			}
			else if($arrAfdValues['haserror'] == '2')
			{
				$tpl->set_var("haserr","No");
			}
			if($arrAfdValues['afd_id'] == $_REQUEST['afdid'])
			{
				$tpl->set_var("filcolor","");
			}
			else
			{
				$tpl->set_var("filcolor","red");
			}
			$tpl->SetAllValues($arrAfdValues);
			$tpl->parse("FillAfdValues",true);
		}
	}
	else
	{
		$tpl->set_var("norecord","No Records Found");
		$tpl->parse("FillNoRecord",false);
	}
	
	$tpl->pparse("main",false);
?>
