<?php
	include_once('db_mysql.php');
	class holidays extends DB_Sql
	{
		function __construct()
		{
		}
		
		function fnSaveHolidays($arrHolidays)
		{
			if(isset($arrHolidays["id"]) && trim($arrHolidays["id"]) == "")
			{
				if($this->fnValidateHolidays($_POST["title"], $_POST["holidaydate"]))
					$this->insertArray("pms_holidays",$arrHolidays);
				else
					return false;
			}
			else
			{
				if($this->fnValidateHolidays($_POST["title"], $_POST["holidaydate"], $_POST["id"]))
					$this->updateArray("pms_holidays",$arrHolidays);
				else
					return false;
			}
			return true;
		}
		
		function fnValidateHolidays($holiday_title, $holiday_date, $id = 0)
		{
			$cond = "";
			if($id != 0)
				$cond = " and id!='$id'";

			$sSQL = "select * from pms_holidays where ((title='".mysql_real_escape_string($holiday_title)."' and date_format(holidaydate,'%Y') = date_format('".mysql_real_escape_string($holiday_date)."','%Y')) or date_format(holidaydate,'%Y-%m-%d') = '".mysql_real_escape_string($holiday_date)."') $cond";
			$this->query($sSQL);
			if($this->num_rows() == 0)
				return true;
			else
				return false;
		}
		
		function fnGetAllHolidays()
		{
			$arrHolidays = array();
			
			$sSQL = "select *, date_format(holidaydate,'%Y-%m-%d') as holidaydate from pms_holidays";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrHolidays[] = $this->fetchrow();
				}
			}
			
			return $arrHolidays;
		}
		
		function fnGetHolidayById($id)
		{
			$arrHolidays = array();
			$sSQL = "select *, date_format(holidaydate,'%Y-%m-%d') as holidaydate from pms_holidays where id='$id'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrHolidays = $this->fetchrow();
				}
			}

			return $arrHolidays;
		}
		
		function fnGetHolidayByDate($date)
		{
			$arrHoliday = array();
			$sSQL = "select *, date_format(holidaydate,'%Y-%m-%d') as holidaydate from pms_holidays where date_format(holidaydate,'%Y-%m-%d')='$date'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrHoliday = $this->fetchrow();
				}
			}

			return $arrHoliday;
		}
	}
?>
