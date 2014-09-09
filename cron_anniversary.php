<?php

	include('common.php');
	
	include_once('includes/class.employee.php');
	include_once('includes/class.calculation.php');
	include_once('includes/class.holidays.php');
	
	
	$objHolidays = new holidays();
	
	$objEmployee = new employee();
	$objCalculation = new calculation();

	$temp1 = array();
	$getAllEmployees = $objEmployee->fnGetEmployeeAnni();
	
	//echo '<pre>'; print_r($getAllEmployees); die;
	$arrAnniversaryPersons = array();
	foreach($getAllEmployees as $employee)
	{
		//echo '<pre>'; print_r($employee);
		//echo '<br><br><br>dob:'.$employee['date_of_joining'];
		//echo '<br><br>$employee_id::'.$employee['id'];
		//echo '<br>$employee_email::'.$employee['email'];
		 $AnniYear = date('m-d', strtotime($employee['date_of_joining']));
		 $anniYear = date('Y', strtotime($employee['date_of_joining']));
		
		$FinalThisYearDate = date('Y').'-'.$AnniYear;

		$FinalThisYearDateFormated = date('d-m', strtotime($employee['date_of_joining'])).'-'.date('Y');

		//echo '<br><br>FinalThisYearDate:'.$FinalThisYearDate;
		
		//$prev = date('Y-m-d', strtotime('-1 day', strtotime($FinalThisYearDate)));
		//$prev = $FinalThisYearDate;

		//echo '<br>pre:'.$prev;
		
		$dayofDate = date('l', strtotime($FinalThisYearDate));
		
		$isHoliday = $objHolidays->fnGetHolidayByDate($dayofDate);

		if($dayofDate == 'Sunday' || $dayofDate == 'Monday')
		{
			$prev = date('Y-m-d', strtotime('-2 day', strtotime($FinalThisYearDate)));
		}
		else if(!empty($isHoliday))
		{
			$prev = date('Y-m-d', strtotime('-3 day', strtotime($FinalThisYearDate)));
		}
		else
		{
			$prev = date('Y-m-d', strtotime('-1 day', strtotime($FinalThisYearDate)));
		}

		$checkDay = $objCalculation->fnCheckPreviousDate1($prev,$temp1);
		

		//echo '<br>checkDay:'.$checkDay;

		//echo "<hr/>";
		
		//if('2013-12-02' == $checkDay)
		if(date('Y-m-d') == $checkDay)
		{
			
		  $date2 = date('Y', strtotime($employee['date_of_joining']));

			$date1 =  date('Y',strtotime($checkDay));
		 

			$diff = (intval($date1))-(intval($date2));
			if(strlen($diff)== 1)
			{
			$employee['years'] ="0".$diff;
			}
			else
			{
				$employee['years'] = $diff;
			}
			
			$arrAnniversaryPersons[] = $employee;
		}
	}
		//echo '<pre>'; print_r($arrAnniversaryPersons);die;
		//echo '<br>count:'.count($arrAnniversaryPersons);
		$AnniversaryPersonsComma = '';
		$dates = '';
		$count = count($arrAnniversaryPersons);
		$i=1;
		if(count($arrAnniversaryPersons) > 0)
		{
			$prefix = '';
			foreach ($arrAnniversaryPersons as $APerson)
			{
				//echo '<pre>'; print_r($APerson[name]);
				//echo $APerson['dob'];
				$AnniYear = date('d-m', strtotime($APerson['date_of_joining']));
				
				
				$FinalThisYearDate = $AnniYear.'-'.date('Y');
				$dateNeeds = date('Y').'-'.date('m-d', strtotime($APerson['date_of_joining']));
				$dobday = date('l', strtotime($dateNeeds));
				
				
				
				$prefix = ',';
				if($i<$count)
				{
				$AnniversaryPersonsComma .=  $APerson[name] . '-'.$APerson['years']." year".$prefix;
				}
				else
				{
					$AnniversaryPersonsComma .=  $APerson[name] . '-'.$APerson['years']." year";
				}
				$i=$i+1;
				
			}
			$dates .=  $FinalThisYearDate.' ['.$dobday. ']';
			$Subject = 'Anniversary Reminder';

			$content = "Dear HR Team, <br /><br />";
			$content .= "This is a reminder for the Anniversary of <b>$AnniversaryPersonsComma</b> completed as on <B>$dates</B>.";
			$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
			//echo '<br>'.$content.'<br>';
			sendmail('hr@transformsolution.net',$Subject,$content);
			//sendmail('gagan.mahatma@transformsolution.net',$Subject,$content);
		}

		
?>
