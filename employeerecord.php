<?php 
	include('common.php');
	
	$tpl = new Template($app_path);

	include("includes/class.quality_form.php");
	$objQualityForm = new quality_form();
	
	$tpl->load_file("employeerecord.html","main");
	unset($_SESSION['form_id']);
	
	$_SESSION['form_id'] = $_REQUEST['ftype'];
	$_SESSION['date'] = $_REQUEST['date'];
	
	$tpl->set_var("date",$_REQUEST['date']);
	$tpl->set_var("ftype",$_REQUEST['ftype']);
	
	$arrReportData=$objQualityForm->fnGetRecordData($_REQUEST['date'],$_REQUEST['ftype'],$_SESSION['id']);
	$arrParameterInfo = $objQualityForm->getParameterInfo($_REQUEST['ftype']);
	
	$tpl->set_var("FillParameter","");
	if(count($arrParameterInfo) > 0)
	{
		foreach($arrParameterInfo as $paraInfo)
		{
			$tpl->SetAllValues($paraInfo);
			$tpl->parse("FillParameter",true);
		}
	}
	$tpl->set_var("FillRecords","");
	$tpl->set_var("FillNoRecord","");
	if(count($arrReportData) > 0)
	{
		foreach($arrReportData as $recordData)
		{
			$tpl->SetAllValues($recordData);
			$tpl->set_var("FillAfds","");
			$arrAfdDetails = $objQualityForm->getAfdsDetais($_REQUEST['date'],$_REQUEST['ftype'],$_SESSION['id']);

			foreach($arrParameterInfo as $paraInfo)
			{
				$tpl->set_var("ShowErrorInfo","");
				$tpl->set_var("ShowComment","");
				if(isset($arrAfdDetails[$recordData["recordid"]][$paraInfo['id']]))
				{
					$tpl->SetAllValues($arrAfdDetails[$recordData["recordid"]][$paraInfo['id']]);
					if($arrAfdDetails[$recordData["recordid"]][$paraInfo['id']]['haserror'] == 'No')
					{
						$tpl->parse("ShowErrorInfo",true);
					}
					
					if($arrAfdDetails[$recordData["recordid"]][$paraInfo['id']]['comment'] != '')
					{
						$tpl->parse("ShowComment",true);
					}
				}
				else
				{
					$tpl->set_var("recordid",$recordData["recordid"]);
					$tpl->set_var("para_id",$paraInfo['id']);
					$tpl->set_var("date",$_REQUEST['date']);
					$tpl->set_var("afdid","");

					$tpl->set_var("haserrorinfo","Record not marked");
				}
				$tpl->parse("FillAfds",true);
			}
			$tpl->parse("FillRecords",true);
		}
	}
	else
	{
		$tpl->parse("FillNoRecord",false);
	}
	
	
	$tpl->pparse("main",false);
?>
