<?php
	include('common.php');

	$tpl = new Template($app_path);
	
	$tpl->load_file("averagescore.html","main");

	include("includes/class.quality_form.php");
	$objQualityForm = new quality_form();
	
	if(isset($_REQUEST["action"]) && trim($_REQUEST["action"]) == "export") 
	{
		$filename = "AverageReport_".$_SESSION["averagescore"]["date"].".xls";
		
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");;
		header("Content-Disposition: attachment;filename=".$filename);
		header("Content-Transfer-Encoding: binary ");
		
		xlsBOF();

		xlsWriteLabel(0,0,"Average score report - ".$_SESSION["averagescore"]["date"]);
		 
		xlsWriteLabel(1,0,"Employee Name");
		xlsWriteLabel(1,1,"Score");

		$xlsRow = 2;
		
		if(is_array($_SESSION["averagescore"]["employees"]) && count($_SESSION["averagescore"]["employees"]) > 0 && is_array($_SESSION["averagescore"]["score"]) && count($_SESSION["averagescore"]["score"]) > 0)
		{
			foreach($_SESSION["averagescore"]["employees"] as $k => $v)
			{
				xlsWriteLabel($xlsRow,0,$v);
				xlsWriteNumber($xlsRow,1,$_SESSION["averagescore"]["score"][$k]);
				$xlsRow++;
			}

		}
		else
		{
			xlsWriteLabel($xlsRow,1,"No Records");
		}
		
		xlsEOF();
		
		exit;	
	}
	
	$tpl->set_var("date",$_REQUEST['date']);
	
	$arrAllMembers = $objQualityForm->getAllMembers($_REQUEST['date'],$_REQUEST['ftype']);
	$arrMasterData = $objQualityForm->fnGetMasterData($_REQUEST['date'],$_REQUEST['ftype']);
	$arrUniqueRecord = $objQualityForm->fnGetAllRecoredId($_REQUEST['date'],$_REQUEST['ftype']);
	
	$totalrecords = count($arrUniqueRecord);
	$arrGetAllParameters = $objQualityForm->fnGetAllParameters($_REQUEST['ftype']);
	
	
	$arrGetHowManyPara = $objQualityForm->fnGetParaCount($_REQUEST['ftype']);
	
	$calculatevalue = array();
	
	if(count($arrAllMembers) >0 )
	{
		foreach($arrAllMembers as $arrMembers)
		{
			foreach($arrUniqueRecord as $arrUniRecords)
			{	
				$newrecordid = $arrUniRecords['recordid'];
				$add = '0';
				$arrAllRecords = $objQualityForm->fnGetMemberData($arrMembers['id'],$_REQUEST['date'],$_REQUEST['ftype']);
				
				if(count($arrAllRecords) > 0)
				{
					$totalpara = count($arrGetAllParameters);
					$onevalue = 100/$totalpara;
					
					forEach($arrGetAllParameters as $key => $allParameters)
					{
						$newparaid = $allParameters['id'];
						if(isset($arrMasterData[$newrecordid][$newparaid]['afdid']) && isset($arrAllRecords[$newrecordid][$newparaid]['afdid']) && $arrMasterData[$newrecordid][$newparaid]['afdid'] != $arrAllRecords[$newrecordid][$newparaid]['afdid'])
						{
							if(isset($calculatevalue[$arrMembers['id']][$newrecordid]))
								$calculatevalue[$arrMembers['id']][$newrecordid] = $calculatevalue[$arrMembers['id']][$newrecordid] + $onevalue;
							else
								$calculatevalue[$arrMembers['id']][$newrecordid] = $onevalue;
						}
					}
				}
				else
				{
					$onevalue = 0;
					if(isset($calculatevalue[$arrMembers['id']][$newrecordid]))
						$calculatevalue[$arrMembers['id']][$newrecordid] = $calculatevalue[$arrMembers['id']][$newrecordid] + $onevalue;
					else
						$calculatevalue[$arrMembers['id']][$newrecordid] = $onevalue;
				}
			}
		}
	}
	$tpl->set_var("FillRecords","");
	$tpl->set_var("FillNoRecord","");
	if($totalrecords >0)
	{
		foreach($arrAllMembers as $arrMembers)
		{
			//print_r($calculatevalue);
		
			$average = sprintf("%.2f",0);
			if(isset($calculatevalue[$arrMembers["id"]]))
				$average = sprintf("%.2f",array_sum($calculatevalue[$arrMembers["id"]])/$totalrecords);
			$arrempname[] = $arrMembers['name'];
			$arraverage[] = $average;
			$tpl->set_var('empname',$arrMembers['name']);
			$tpl->set_var('average',$average);
			$tpl->parse("FillRecords",true);
		}
	}
	else
	{
		$tpl->set_var('notfound',$average);
		$tpl->parse("FillNoRecord",true);
	}

	$_SESSION["averagescore"]["date"] = $_REQUEST['date'];
	$_SESSION["averagescore"]["employees"] = $arrempname;
	$_SESSION["averagescore"]["score"] = $arraverage;

	if(isset($arrempname))
	{
		$a1 = $arrempname;
	
		$jsonname = implode(",",$a1);
		$jsonaverage  = implode(",",$arraverage);
		
		$tpl->set_var('jsonname',$jsonname);
		$tpl->set_var('jsonaverage',$jsonaverage);
	}

	$tpl->pparse("main",false);
?>
