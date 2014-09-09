<?php
include_once('db_mysql.php');
	class increment extends DB_Sql
	{
		function __construct()
		{
		}
		function fnGetIncentiveDetailsById($id)
		{
			$arrAllIncentive = array();
			echo $query = "select name as ename,employee_code as e_code,date_of_joining as e_dete_join,start_ctc as e_sta_ctc,current_salary_ctc as e_cur_ctc from pms_employee where id = '$id'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrAllIncentive =  $this->fetchrow();
				}
			}
			return $arrAllIncentive;
		}
		
		function fnAddIncrement($post)
		{
			//echo '<pre>'; print_r($post);
			$arrPostData = array();
			$arrPostData['emp_id'] = $post['userid'];
			$arrPostData['prev_salary'] = $post['current_salary'];
			$arrPostData['current_salary'] = $post['newsalary'];
			$arrPostData['date'] = date("Y-m-d H:i:s");
			$this->insertArray("pms_increment",$arrPostData);
			return true;
		}
		
		function fnGetAllIncrements($id)
		{
			$arrAllIncentive = array();
			$query = "select *,date_format(date,'%d-%m-%Y') as dt from pms_increment where emp_id = '$id'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAllIncentive[] =  $this->fetchrow();
				}
			}
			return $arrAllIncentive;
		}
	}
?>
