<?php

	include('common.php');
	
	include_once('includes/class.employee.php');
	include_once('includes/class.calculation.php');
	include_once('includes/class.holidays.php');
	
	
	$objHolidays = new holidays();
	$objEmployee = new employee();
	$objCalculation = new calculation();

	$temp1 = array();
	$getAllEmployees = $objEmployee->fnGetEmployeeDob();
	//echo '<pre>'; print_r($getAllEmployees); die;
	$arrBirthdaysPersons = array();
	foreach($getAllEmployees as $employee)
	{
		//echo '<pre>'; print_r($employee);
		//echo '<br><br><br>dob:'.$employee['dob'];
		//echo '<br><br>$employee_id::'.$employee['id'];
		//echo '<br>$employee_email::'.$employee['email'];
		$dobYear = date('m-d', strtotime($employee['dob']));
		
		$FinalThisYearDate = date('Y').'-'.$dobYear;

		$FinalThisYearDateFormated = date('d-m', strtotime($employee['dob'])).'-'.date('Y');

		//echo '<br><br>FinalThisYearDate:'.$FinalThisYearDate;
		
		//$prev = date('Y-m-d', strtotime('-1 day', strtotime($FinalThisYearDate)));
		//$prev = $FinalThisYearDate;

		//echo '<br>pre:'.$prev;
		
		$dayofDate = date('l', strtotime($FinalThisYearDate));
		$isHoliday = $objHolidays->fnGetHolidayByDate($dayofDate);
		if(($dayofDate == 'Sunday' || $dayofDate == 'Monday'))
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
			$arrBirthdaysPersons[] = $employee;
		}
	}
		//echo '<pre>'; print_r($arrBirthdaysPersons);die;
		//echo '<br>count:'.count($arrBirthdaysPersons);
		$BirthdayPersonsComma = '';
		$dates = '';
		if(count($arrBirthdaysPersons) > 0)
		{
			$prefix = '';
			foreach ($arrBirthdaysPersons as $BPerson)
			{
				//echo '<pre>'; print_r($BPerson[name]);
				//echo $BPerson['dob'];
				$dobYear = date('d-m', strtotime($BPerson['dob']));
				
				$FinalThisYearDate = $dobYear.'-'.date('Y');
				$dateNeeds = date('Y').'-'.date('m-d', strtotime($BPerson['dob']));
				$dobday = date('l', strtotime($dateNeeds));
				
				$BirthdayPersonsComma .= $prefix . '' . $BPerson[name] . '';
				$dates .= $prefix . '' . $FinalThisYearDate.' ['.$dobday. ']';
				$prefix = ', ';
				
			}
			$Subject = 'Birthday Reminder';

			$content = "Dear HR Team, <br /><br />";
			$content .= "This is a reminder for the birthday of <b>$BirthdayPersonsComma</b> as on <B>$dates</B>.";
			$content .= "<br/><br/>Regards,<br/>".SITEADMINISTRATOR;
			//echo '<br>'.$content.'<br>';
			sendmail('hr@transformsolution.net',$Subject,$content);
			//sendmail('gagan.mahatma@transformsolution.net',$Subject,$content);
		}

		
?>
