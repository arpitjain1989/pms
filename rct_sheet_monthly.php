<?php 
	include('common.php');
	$tpl = new Template($app_path);

	$tpl->load_file('template.html','main');
	$tpl->load_file('rct_sheet_monthly.html','main_container');

	$PageIdentifier = "MonthlyRctSheet";
	include_once('userrights.php');

	$tpl->set_var("mainheading","RCT Monthly Report");
	$breadcrumb = '<li class="active">RCT Monthly Report</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);
	
	include_once('includes/class.candidate_list.php');
	
	$objCandidateList = new candidate_list();
	

	$curDate = Date('Y-m-d');
	$curYear = Date('Y');
	$curMonth = Date('m');

	$tpl->set_var("curmonth",$curMonth);
	$tpl->set_var("curyear",$curYear);


	//$tpl->set_var("DisplayDivisionInner",'');
	//echo $_POST['final_hr_status'];
	if(isset($_POST['final_hr_status']) && $_POST['final_hr_status'] != '')
	{
		//$tpl->set_var("DisplayDivisionInner",'');
		
	}
	else
	{
		//$tpl->parse("DisplayDivisionInner",true);
	}



	$arrYear = array($curYear, $curYear-1);

	//$rct_source = $objCandidateList->fnGetAllRctSource();
	
	$rct_division = $objCandidateList->fnGetAllRctDivision();

	//echo '<pre>'; print_r($rct_source);
	//echo 'hello'.$_POST['month'].'hello1';
	if(isset($_POST['month']) && ($_POST['month'] == '' || $_POST['month'] == '0'))
	{
		$_POST['month'] = $curMonth;
	}
	if(isset($_POST['year']) && ($_POST['year'] == '' || $_POST['year'] == '0'))
	{
		$_POST['year'] = $curYear;
	}

	

	if((isset($_POST['month']) && $_POST['month'] != '') || (isset($_POST['year']) && $_POST['year'] != '') || (isset($_POST['final_hr_status']) && $_POST['final_hr_status'] != ''))
	{
		$arrCandidateList = $objCandidateList->fnGetAllCandidateForMonthlyReport($_POST['month'],$_POST['year'],$_POST['final_hr_status']);
		
		$rct_source = $objCandidateList->fnGetAllCandidateForMonthlyReportwithStatus($_POST['month'],$_POST['year'],$_POST['final_hr_status']);
		
		$tpl->set_var("curmonth",$_POST['month']);
		$tpl->set_var("curyear",$_POST['year']);
		$tpl->set_var("curStatus",$_POST['final_hr_status']);
		
		$countTotalCandidates = $objCandidateList->fnGetAllCandidatesMonthlyTotal($_POST['month'],$_POST['year'],$_POST['final_hr_status']);
	}
	else
	{
		//echo 'hello'; die;
		$arrCandidateList = $objCandidateList->fnGetAllCandidateListForMonthlyRCT();
		
		$rct_source = $objCandidateList->fnGetAllCandidateListForMonthlyResourceCal();
		
		$countTotalCandidates = count($arrCandidateList);
	}

	//echo '<br>arrTotalCandidates:'.$countTotalCandidates;

	$message = "";
	$messageClass = "";
	$tpl->set_var("DisplayMessageBlock","");
	
	

	$tpl->set_var("DisplayYearBlock","");
	if(count($arrYear) > 0)
	{
		foreach($arrYear as $curYr)
		{
			$tpl->set_var("curyr",$curYr);
			$tpl->parse("DisplayYearBlock",true);
		}
	}

	$tpl->set_var("FillRctSheetInfo","");
	$tpl->set_var("FillNoRecords","");

	$tpl->set_var("total_candidate",$countTotalCandidates);

	
	//echo '<pre>'; print_r($arrCandidateList);
	if(count($arrCandidateList) > 0)
	{
		foreach($arrCandidateList as $candidate)
		{
			$tpl->set_var("fcolor","");
			//echo '<pre>'; print_r($candidate);
			$hrstatus = array("1"=>"Y", "2"=>"Y", "3"=>"Y" ,"4"=>"Y" ,"0"=>"N/A");
			
			$finalHrStatus = array("1"=>"Declined", "2"=>"FRT","3"=>"Future Prospect", "4"=>"Shortlisted","5"=>"Hold", "6"=>"Selected","7"=>"Rejected","9"=>"Hired");

			$candidate['final_hr_remarks'] = str_replace("00:00:00","",str_replace("Doj","DOJ",$candidate['final_hr_remarks']));
			
			$tpl->set_var("registartion_date",$candidate['reg_date']);
			if(isset($candidate['divi_title']))
				$tpl->set_var("division_title",$candidate['divi_title']);
			if(isset($hrstatus[$candidate['recommend_test']]))
				$tpl->set_var("hr_status",$hrstatus[$candidate['recommend_test']]);
			$tpl->set_var("operations_status",$hrstatus[$candidate['om_status']]);
			$tpl->set_var("test_status",$hrstatus[$candidate['recommend_om_round']]);
			$arrOperationManagerComments = $objCandidateList->fnGetAllOpsComments($candidate['cand_id']);
			//echo $candidate['dev_id'];
			if($candidate['rctsource'] == '0')
			{
				$getReferencename = $objCandidateList->fnGetEmployeeNameById($candidate['reference_trans']);
				$tpl->set_var('rct_source_name',$getReferencename);
			}
			else
			{
				$tpl->set_var('rct_source_name',$candidate['source_title']);
			}

			$managerComment = $objCandidateList->fnGetOpsComments($candidate['cand_id'],$candidate['recommend_om']);

			$managerCommentOnApprove = $objCandidateList->fnGetOpsComments1($candidate['cand_id'],$candidate['recommend_om']);

			//echo '<pre>'; print_r($managerCommentOnApprove); 
			//echo $candidate['final_hr_status'];
			
			$tpl->set_var("final_status","");
			$tpl->set_var("final_remarks","");
			$tpl->set_var("fcolor","");
			
			if($candidate['final_hr_status'] == '1')
			{
				$tpl->set_var("final_status","Declined");
				$tpl->set_var("final_remarks",$candidate['final_hr_remarks']);
				$tpl->set_var("fcolor","#F5F500");
			}
			else if($candidate['final_hr_status'] == '2')
			{
				$tpl->set_var("final_status","FRT");
				$tpl->set_var("final_remarks",$candidate['final_hr_remarks']);
			}
			else if($candidate['final_hr_status'] == '3')
			{
				$tpl->set_var("final_status","Future Prospect");
				$tpl->set_var("final_remarks",$candidate['final_hr_remarks']);
			}
			else if($candidate['final_hr_status'] == '4')
			{
				if($candidate['status'] == '9')
				{
					$tpl->set_var("final_status","Hired");
					$tpl->set_var("final_remarks",$candidate['final_hr_remarks']);
					$tpl->set_var("fcolor","#1E9E1E");
				}
				else
				{
					$tpl->set_var("final_status","Shortlisted");
					$tpl->set_var("final_remarks",$candidate['final_hr_remarks']);
					$tpl->set_var("fcolor","#79BAEC");
				}
			}
			else if($candidate['final_hr_status'] == '5')
			{
				$tpl->set_var("final_status","Hold");
				$tpl->set_var("final_remarks",$candidate['final_hr_remarks']);
			}
			else if($candidate['final_hr_status'] == '6')
			{
				$tpl->set_var("final_status","Rejected");
				$tpl->set_var("final_remarks",$candidate['final_hr_remarks']);
				$tpl->set_var("fcolor","#FF6464");
			}
			else if($candidate['final_hr_status'] == '0')
			{
				//echo 'hello';
				
				if($candidate['om_status'] == '0' && $candidate['om_reasign_flag'] == '1')
				{
					if($candidate['recommend_om_round'] == '1')
					{
						$tpl->set_var("final_status","FRT");
						$tpl->set_var("final_remarks","OM's round need to be schedule");
					}
					if($candidate['recommend_om_round'] == '2')
					{
						$tpl->set_var("final_status","Rejected");
						$tpl->set_var("final_remarks",$candidate['test_hr_remarks']);
						$tpl->set_var("fcolor","#FF6464");
					}
					if($candidate['recommend_om_round'] == '3')
					{
						$tpl->set_var("final_status","Declined");
						$tpl->set_var("final_remarks",$candidate['test_hr_remarks']);
						$tpl->set_var("fcolor","#F5F500");
					}
					if($candidate['recommend_om_round'] == '0')
					{
						if($candidate['recommend_test'] == '1')
						{
							$tpl->set_var("final_status","Test");
							$tpl->set_var("final_remarks","Test to be taken");
						}
						else if($candidate['recommend_test'] == '2')
						{
							$tpl->set_var("final_status","Rejected");
							$tpl->set_var("final_remarks",$candidate['hrcomments']);
							$tpl->set_var("fcolor","#FF6464");
						}
						else if($candidate['recommend_test'] == '3')
						{
							$tpl->set_var("final_status","Hold");
							$tpl->set_var("final_remarks",$candidate['hrcomments']);
						}
						else if($candidate['recommend_test'] == '4')
						{
							$tpl->set_var("final_status","Declined");
							$tpl->set_var("final_remarks",$candidate['hrcomments']);
							$tpl->set_var("fcolor","#F5F500");
						}
						else if($candidate['recommend_test'] == '5')
						{
							$tpl->set_var("final_status","Future Prospect");
							$tpl->set_var("final_remarks",$candidate['hrcomments']);
						}
						else if($candidate['recommend_test'] == '0')
						{
							if($candidate['recommend_hr_round'] == '2')
							{
								$tpl->set_var("final_status","Rejected");
								$tpl->set_var("final_remarks","IQ is very low.");
								$tpl->set_var("fcolor","#FF6464");
							}
							else
							{
								$tpl->set_var("final_status","Hr round");
								$tpl->set_var("final_remarks","Hr round in process");
							}
						}
					}
				}
				else if($candidate['om_status'] == '1' && $candidate['om_reasign_flag'] == '0')
				{
					if($candidate['status'] == '9')
					{
						$tpl->set_var("final_status","Hired");
						$tpl->set_var("final_remarks",$candidate['final_hr_remarks']);
						$tpl->set_var("fcolor","#1E9E1E");
					}
					else
					{
						$tpl->set_var("final_status","Shortlisted");
						$tpl->set_var("final_remarks","DOJ ".$managerCommentOnApprove);
						$tpl->set_var("fcolor","#79BAEC");
					}
				}
				else if($candidate['om_status'] == '2' && $candidate['om_reasign_flag'] == '0')
				{
					$tpl->set_var("final_status","Rejected");
					$tpl->set_var("final_remarks",$managerComment);
					$tpl->set_var("fcolor","#FF6464");
				}
				else if($candidate['om_status'] == '3' && $candidate['om_reasign_flag'] == '0')
				{
					$tpl->set_var("final_status","Hold");
					$tpl->set_var("final_remarks",$managerComment);
				}
				else if($candidate['om_status'] == '4' && $candidate['om_reasign_flag'] == '0')
				{
					$tpl->set_var("final_status","Declined");
					$tpl->set_var("final_remarks",$managerComment);
					$tpl->set_var("fcolor","#F5F500");
					
				}
				else if(($candidate['om_status'] != '0' || $candidate['om_status'] != '') && $candidate['om_reasign_flag'] == '1')
				{
					$tpl->set_var("final_status","FRT");
					$tpl->set_var("final_remarks","OM's round need to be schedule");
					$tpl->set_var("operations_status","N/A");
				}
			}
			
			if(isset($finalHrStatus[$candidate['final_hr_status']]))
			{
				$tpl->set_var("final_hr_stat",$finalHrStatus[$candidate['final_hr_status']]);
			}
			
	 		$tpl->SetAllValues($candidate);
			$tpl->parse("FillRctSheetInfo",true);
		}
	}
	else
	{
		$tpl->set_var("noRecord",'No records found.');
		$tpl->parse("FillNoRecords",true);
	}

	$tpl->set_var("FillRctSource",'');
	$tpl->set_var("source_title",'');
	$tpl->set_var("sourceCount",'');
	//echo '<pre>'; print_r($rct_source); die;
	if(count($rct_source) > 0)
	{
		if(!isset($_POST['month']))
		{
			$_POST['month'] = $curMonth;
		}
		if(!isset($_POST['year']))
		{
			$_POST['year'] = $curYear;
		}
		if(!isset($_POST['final_hr_status']))
		{
			$_POST['final_hr_status'] = '';
		}
		//$getTotalEmployeeReference = $objCandidateList->fnGetAllEmployeeReference('0',$_POST['month'],$_POST['year'],$_POST['final_hr_status']);
		//$tpl->set_var("EmployeeReferenceCount",$getTotalEmployeeReference);
		//echo $getTotalEmployeeReference; die;
		foreach($rct_source as $rctsource)
		{
			//echo '<pre>'; print_r($rctsource);
			//$recordFromRctSource = $objCandidateList->fnGetAllRctSourceRecordsCount($rctsource['id'],$_POST['month'],$_POST['year'],$_POST['final_hr_status']);
			
			$tpl->set_var("source_title","");
			$tpl->set_var("sourceCount","");
			if(isset($rctsource['source_name']))
			{
				$tpl->set_var("source_title",$rctsource['source_name']);
			}
			if(isset($rctsource['count_rct_source']))
			{
				$tpl->set_var("sourceCount",$rctsource['count_rct_source']);
			}
			$tpl->parse("FillRctSource",true);
		}
	}
	/*foreach($rct_source as $rctsource)
	{
		//echo '<pre>'; print_r($rctsource); die;
		if(!isset($_POST['month']))
		{
			$_POST['month'] = $curMonth;
		}
		if(!isset($_POST['year']))
		{
			$_POST['year'] = $curYear;
		}
		if(!isset($_POST['final_hr_status']))
		{
			$_POST['final_hr_status'] = '';
		}
		$recordFromRctSource = $objCandidateList->fnGetAllRctSourceRecordsCountMonthlyReport($rctsource['id'],$_POST['month'],$_POST['year'],$_POST['final_hr_status']);
		$getTotalEmployeeReference = $objCandidateList->fnGetAllEmployeeReferenceMonthlyReport('0',$_POST['month'],$_POST['year'],$_POST['final_hr_status']);
		$tpl->set_var("source_title",$rctsource['title']);
		$tpl->set_var("sourceCount",$recordFromRctSource);
		$tpl->set_var("EmployeeReferenceCount",$getTotalEmployeeReference);
		$tpl->parse("FillRctSource",true);
	}*/
	
	$tpl->set_var("FillRctDivision",'');
	$tpl->set_var("division_title",'');
	$tpl->set_var("divisionCount",'');
	$tpl->set_var("rctShortlistedCount",'');
	$tpl->set_var("rctHiredCount",'');
	$tpl->set_var("rctRejectedCount",'');
	$tpl->set_var("rctDeclinedCount",'');
	$tpl->set_var("rctHoldCount",'');
	$tpl->set_var("rctTestCount",'');
	$tpl->set_var("rctFRTCount",'');
	$tpl->set_var("rctFutureProspectsCount",'');
	//echo '<pre>'; print_r($rct_division); die;
	foreach($rct_division as $rctdivision)
	{
		//echo '<pre>'; print_r($rctdivision);
		$recordFromRctDivision = $objCandidateList->fnGetAllRctDivisionRecordsCountMonthlyReport($rctdivision['id'],$_POST['month'],$_POST['year'],$_POST['final_hr_status']);
		$getShortlistedCandidates = $objCandidateList->fnGetAllShortlistedCandidatesCountMonthlyReport($rctdivision['id'],$_POST['month'],$_POST['year'],$_POST['final_hr_status']);
		$getHiredCandidates = $objCandidateList->fnGetAllHiredCandidatesCountMonthlyReport($rctdivision['id'],$_POST['month'],$_POST['year'],$_POST['final_hr_status']);
		$getRejectedCandidates = $objCandidateList->fnGetAllRejectedCandidatesCountMonthlyReport($rctdivision['id'],$_POST['month'],$_POST['year'],$_POST['final_hr_status']);
		$getDeclinedCandidates = $objCandidateList->fnGetAllDeclinedCandidatesCountMonthlyReport($rctdivision['id'],$_POST['month'],$_POST['year'],$_POST['final_hr_status']);
		$getHoldCandidates = $objCandidateList->fnGetAllHoldCandidatesCountMonthlyReport($rctdivision['id'],$_POST['month'],$_POST['year'],$_POST['final_hr_status']);
		$getTestCandidates = $objCandidateList->fnGetAllTestCandidatesCountMonthlyReport($rctdivision['id'],$_POST['month'],$_POST['year'],$_POST['final_hr_status']);
		$getFRTCandidates = $objCandidateList->fnGetAllFRTCandidatesCountMonthlyReport($rctdivision['id'],$_POST['month'],$_POST['year'],$_POST['final_hr_status']);
		$getFutureProspectsCandidates = $objCandidateList->fnGetAllFutureCandidatesCountMonthlyReport($rctdivision['id'],$_POST['month'],$_POST['year'],$_POST['final_hr_status']);
		
		if(isset($rctdivision['title']))
			$tpl->set_var("division_title",$rctdivision['title']);
		$tpl->set_var("divisionCount",$recordFromRctDivision);
		$tpl->set_var("rctShortlistedCount",$getShortlistedCandidates);
		$tpl->set_var("rctHiredCount",$getHiredCandidates);
		$tpl->set_var("rctRejectedCount",$getRejectedCandidates);
		$tpl->set_var("rctDeclinedCount",$getDeclinedCandidates);
		$tpl->set_var("rctHoldCount",$getHoldCandidates);
		$tpl->set_var("rctTestCount",$getTestCandidates);
		$tpl->set_var("rctFRTCount",$getFRTCandidates);
		$tpl->set_var("rctFutureProspectsCount",$getFutureProspectsCandidates);
		if($_POST['final_hr_status'] != '')
		{
			//$tpl->set_var("DisplayDivisionInner",'');
		}
		else
		{
			//$tpl->parse("DisplayDivisionInner",true);
		}
		
		$tpl->parse("FillRctDivision",true);
		$tpl->parse("FillRctDivision1",true);
		$tpl->parse("FillRctDivision2",true);
		$tpl->parse("FillRctDivision3",true);
		$tpl->parse("FillRctDivision4",true);
		$tpl->parse("FillRctDivision5",true);
		$tpl->parse("FillRctDivision6",true);
		$tpl->parse("FillRctDivision7",true);
		$tpl->parse("FillRctDivision8",true);
		$tpl->parse("FillRctDivision9",true);
	}
		
	$tpl->pparse('main',false);
?>
