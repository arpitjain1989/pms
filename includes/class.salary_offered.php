<?php
include_once('db_mysql.php');
	class salary_offered extends DB_Sql
	{
		function __construct()
		{
		}
		/* Insert salary offered */
		function fnInsertSalaryOffered($arrEmployee)
		{
			//echo 'hello<pre>'; print_r($arrEmployee); die;
			$arrEmployee['des_id'] = $arrEmployee['designation'];
			$arrEmployee['user_id'] = $_SESSION['id'];
			$arrEmployee['usertype'] = $_SESSION['usertype'];
			$arrEmployee['date'] = Date('Y-m-d H:i:s');
			$checkExistId = $this->fnCheckExistingData($arrEmployee);
			if($checkExistId != '')
			{
				
				$newArray =array();
				$newArray['status'] = '1';
				$newArray['id'] = $checkExistId;
				//echo '<pre>'; print_r($arrEmployee); die;
				$this->updateArray('pms_salary_offered',$newArray);
			}
			//die;
			$arrEmployee['id'] = '';
			$this->insertArray('pms_salary_offered',$arrEmployee);
			return true;
		}

		function fnCheckExistingData($arrData)
		{
			//echo 'hello<pre>'; print_r($arrData);
			if($arrData['id'] != '')
			{
				$query = "select id from pms_salary_offered where id = '".$arrData['id']."' and status = '0'";
				$this->query($query);
				if($this->num_rows() > 0)
				{
					if($this->next_record())
					{
						$oldId = $this->f('id');
					}
				}
				return $oldId;
			}
			else
			{
				return '';
			}
		}
		
		function fnGetSalaryOfferedByDesId($id)
		{
			$arrRCTDivisionValues = array();
			$query = "SELECT * FROM `pms_salary_offered` where des_id = '$id' and status = '0'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrRCTDivisionValues = $this->fetchrow();
				}
			}
			return $arrRCTDivisionValues;
		}

		function fnGetAllSalaryOffered()
		{
			$arrRCTSalaryOffered = array();
			$query = "SELECT sal.*,des.*,sal.id as sal_id FROM `pms_salary_offered` as sal left join `pms_designation` as des on sal.des_id = des.id where sal.status = '0'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRCTSalaryOffered[] = $this->fetchrow();
				}
			}
			return $arrRCTSalaryOffered;
		}

		function fnGetSalaryOfferedById($id)
		{
			$arrData = array();
			$sql = "select sal.lowest_amount as lowest,sal.highest_amount as highest,des.title as des_title from pms_salary_offered as sal left join pms_designation as des on sal.des_id = des.id where sal.id = '$id'";
			$this->query($sql);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrData = $this->fetchrow();
				}
			}
			return $arrData;
		}
	}
?>
