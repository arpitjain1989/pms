<?php
include_once('db_mysql.php');
	class user_registration extends DB_Sql
	{
		function __construct()
		{
		}
		function fnInsertUser($arrEmployee)
		{
			$arrNewRecords = array("title"=>$arrEmployee['title'],"description"=>$arrEmployee['description']);
			$this->insertArray('pms_rct_source',$arrNewRecords);
			return true;
		}
		
		function fnInsertCandidates($arrEmployee)
		{
			//echo '<pre>'; print_r($arrEmployee);
			$this->insertArray('pms_user_registration',$arrEmployee);
			$last_id = mysql_insert_id();
			$this->fnInsertStatusChanges($last_id,$arrEmployee);
			return true;
		}

		function fnInsertStatusChanges($id,$arrData)
		{
			//echo 'id'.$id;
			$arrNewRecords = array();
			$arrNewRecords['date'] = $arrData['date'];
			$arrNewRecords['cand_id'] = $id;
			if($arrData['final_hr_status'] == '3' || $arrData['final_hr_status'] == '5')
			{
				$arrNewRecords['status'] = $arrData['final_hr_status'];
				
			}
			else if($arrData['final_hr_status'] == '' && $arrData['recommend_test'] == '1')
			{
				$arrNewRecords['status'] = '7';
			}
			$this->insertArray('pms_rct_status',$arrNewRecords);
			return true;
		}
		
		
		function fnGetAllRctSource()
		{
			$arrRctDetails = array();
			$query = "SELECT `id` as rct_id,`title` as rct_title FROM `pms_rct_source`";
			$this->query($query);
			
			if($this->num_rows() > 0)
			{	
				while($this->next_record())
				{
					$arrRctDetails[] = $this->fetchrow();
				}
			}
			return $arrRctDetails;
		}

		function fnGetAllDesignations()
		{
			$arrAllDesignation = array();
			$query = "SELECT `id` as des_id,`title` as des_title FROM `pms_designation` where id NOT IN(17,8)";
			$this->query($query);
			
			if($this->num_rows() > 0)
			{	
				while($this->next_record())
				{
					$arrAllDesignation[] = $this->fetchrow();
				}
			}
			return $arrAllDesignation;
		}
		
		function fnGetAllDivisions()
		{
			$arrAllDivisions = array();
			$query = "SELECT `id` as dev_id,`title` as dev_title FROM `pms_rct_division`";
			$this->query($query);
			
			if($this->num_rows() > 0)
			{	
				while($this->next_record())
				{
					$arrAllDivisions[] = $this->fetchrow();
				}
			}
			return $arrAllDivisions;
		}
		function fnInsertRegistration($arrPostData)
		{
			//echo 'hello'; print_r($arrPostData); die;
			if (file_exists("media/resume/" . $_FILES["file"]["name"]))
			{
				header("Location: candidates.php?info=exist");
			}
			else
			{
				move_uploaded_file($_FILES["file"]["tmp_name"],"media/resume/" . $_FILES["file"]["name"]);
			}
				
			$arrPostData['resume'] = $_FILES["file"]["name"];
			$date = Date('Y-m-d H:i:s');
			$arrPostData['date']= $date;
			$this->insertArray('pms_user_registration',$arrPostData);
			return mysql_insert_id();
		}

		function fnGetUserNameById($id)
		{
			$username = '';
			$query = "SELECT name FROM `pms_user_registration` WHERE id = '$id'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$username = $this->f("name");
				}
			}
			return $username;
		}

		function fnGetAllCurrentOpenings()
		{
			$arrAllDesignation = array();
			$query = "SELECT des.`id` as des_id,des.`title` as des_title FROM `pms_designation` as des left join `pms_latest_openings` as ope on des.id = ope.latest_opening where ope.isDelete = '0'";
			$this->query($query);
			
			if($this->num_rows() > 0)
			{	
				while($this->next_record())
				{
					$arrAllDesignation[] = $this->fetchrow();
				}
			}
			return $arrAllDesignation;
		}
	}
?>
