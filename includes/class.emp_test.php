<?php
include_once('db_mysql.php');
	class emp_test extends DB_Sql
	{
		function __construct()
		{
		}

		function fnInsertEmpTest($arrEmployee)
		{
			$this->insertArray('pms_emp_test',$arrEmployee);
			return true;
		}
		
		function fnInsertEmpTestMarks($arrEmployee)
		{
			$this->insertArray('pms_emp_test_marks',$arrEmployee);
			return true;
		}

		function fnGetAllEmpTest()
		{
			$arrEmpTestValues = array();
			//$query = "SELECT * FROM `pms_emp_test`";
			$query = "SELECT test1.id AS child_id, test1.title AS child_title, test2.title AS par_title FROM `pms_emp_test` AS test1 LEFT JOIN `pms_emp_test` AS test2 ON test1.par_id = test2.id ORDER BY test1.par_id";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmpTestValues[] = $this->fetchrow();
				}
			}
			return $arrEmpTestValues;
		}

		function fnGetAllRootEmpTest()
		{
			$arrEmpTestValues = array();
			$query = "SELECT id as test_id,title as test_title,par_id as test_parent_id FROM `pms_emp_test` where par_id = '0'";
			//echo $query = "SELECT * FROM `pms_emp_test` as emp_test left join pms_emp_test_details as details on  emp_test.id = details.test_id where emp_id = '$id'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmpTestValues[] = $this->fetchrow();
				}
			}
			return $arrEmpTestValues;
		}

		function fnGetEmpTestById($id)
		{
			$arrEmpTestValues = array();
			$query = "SELECT t1.id as t_id,t1.title as t_title,t1.par_id as parents_id,t2.title as par_title FROM `pms_emp_test` as t1 left join `pms_emp_test` as t2 on t1.par_id = t2.id WHERE t1.`id` = '".mysql_real_escape_string($id)."' ";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmpTestValues[] = $this->fetchrow();
				}
			}
			return $arrEmpTestValues;
		}
		

		function fnUpdateEmpTest($arrPost)
		{
			$this->updateArray('pms_emp_test',$arrPost);
			return true;
		}

		function fnUpdateEmpMarksTest($arrPost)
		{
			$this->updateArray('pms_emp_test_marks',$arrPost);
			return true;
		}

		function fnDeleteEmpTest($arrvalues)
		{
			
			if(isset($arrvalues[chk]))
			{
				foreach($arrvalues[chk] as $arrval)
				{
					$query = "DELETE FROM `pms_emp_test` WHERE `id` = '".mysql_real_escape_string($arrval)."'";
					$this->query($query);

					$query1 = "DELETE FROM `pms_emp_test_marks` WHERE `test_id` = '".mysql_real_escape_string($arrval)."'";
					$this->query($query1);
				}
				return true;
			}
			else
			{
				return false;
			}
		}
		
		
		function fnDeleteEmpMarksTest($arrvalues)
		{
			if(isset($arrvalues[chk]))
			{
				foreach($arrvalues[chk] as $arrval)
				{
					$query = "DELETE FROM `pms_emp_test_marks` WHERE `id` = '".mysql_real_escape_string($arrval)."'";
					$this->query($query);
				}
				return true;
			}
			else
			{
				return false;
			}
		}

		function fnCheckPrevOmStatus($id)
		{
			$reco_om = '';
			$query = "select recommend_om from `pms_user_registration` where id = '$id'";
			$this->query($query);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$reco_om = $this->f('recommend_om');
				}
			}
			return $reco_om;
		}

		function fnUpdateEmpTestDetailsById($arrPost)
		{
			$date = date('Y-m-d');
			//echo '<pre>'; print_r($arrPost);die;
			if($arrPost['recommend_om_round'] != '')
			{
				$checkPrevOm = $this->fnCheckPrevOmStatus($arrPost['id']);
				if($checkPrevOm == '' && $checkPrevOm == '0')
				{
					$arrPostData1 = array("id"=>$arrPost['id'],"recommend_om_round"=>$arrPost['recommend_om_round'],"recommend_om"=>$arrPost['recommend_om'],"test_hr_remarks"=>$arrPost['test_hr_remarks'],"recommend_om_round_by"=>$_SESSION['id'],"recommend_om_round_date"=>$date);
				}
				else
				{
					$arrPostData1 = array("id"=>$arrPost['id'],"recommend_om_round"=>$arrPost['recommend_om_round'],"recommend_om"=>$arrPost['recommend_om'],"test_hr_remarks"=>$arrPost['test_hr_remarks'],"recommend_om_round_by"=>$_SESSION['id'],"recommend_om_round_date"=>$date,"om_status" => '0');
				}
				$this->updateArray('pms_user_registration',$arrPostData1);
			}
			
			
			foreach($arrPost['crieteria'] as $key=> $value)
			{
				//echo '<pre>'; print_r($key); print_r($value);
				foreach($value as $key1=> $newValue)
				{
					//echo '<br>InHere<pre>'; print_r($key1); print_r($newValue);
					$checkExist = $this->fnGetMarksDetailById($arrPost['id'],$key1,$key);
				//echo 'hello'.$checkExist; die;
					if($checkExist != '0')
					{
						$arrPostData = array("id"=>$checkExist,"emp_id"=>$arrPost['id'],"test_id"=>$key1,"test_parent_id"=>$key,"marks"=>$newValue,"last_updated_date"=>$date);
						//echo '<br>InHere<pre>';  print_r($arrPostData);
						$this->updateArray('pms_emp_test_marks_details',$arrPostData);
					}
					else
					{
						$arrPostData = array("emp_id"=>$arrPost['id'],"test_id"=>$key1,"test_parent_id"=>$key,"marks"=>$newValue,"last_updated_date"=>$date);
						//echo '<br>InHere<pre>';  print_r($arrPostData);
						
						$this->insertArray('pms_emp_test_marks_details',$arrPostData);
					}
					
					
				}
				
				
			}
			//die;
			return true;
		}

		function fnGetMarksDetailById($emp_id,$test_id,$par_id)
		{
			$test_detail_id = '0';
			$query = "SELECT id FROM `pms_emp_test_marks_details` where emp_id = '$emp_id' and test_id = '$test_id' and  test_parent_id = '$par_id'";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$test_detail_id = $this->f('id');
				}
			}
			return $test_detail_id;
		}

		function fnGetTestMarks($eid,$testId)
		{
			$marks = '';
			$query = "SELECT marks FROM `pms_emp_test_marks_details` where test_id = '$testId' and emp_id = '$eid' ";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$marks = $this->f('marks');
				}
			}
			return $marks;
		}

		function fnGetAllEmpTestMarks()
		{
			$arrEmpTestValues = array();
			$query = "SELECT t1.*,t2.title as test_title FROM `pms_emp_test_marks` as t1 left join `pms_emp_test` as t2 on t1.test_id = t2.id ORDER BY test_id ASC";
			//echo $query = "SELECT * FROM `pms_emp_test` as emp inner join `pms_emp_test` as emp1 on emp.id = emp1.par_id ";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmpTestValues[] = $this->fetchrow();
				}
			}
			return $arrEmpTestValues;
		}
		
		function fnGetAllSubCategoryEmpTest()
		{
			$arrEmpTestValues = array();
			$query = "SELECT id as test_id,title as test_title,par_id as test_parent_id FROM `pms_emp_test` where par_id != '0'";
			//echo $query = "SELECT * FROM `pms_emp_test` as emp_test left join pms_emp_test_details as details on  emp_test.id = details.test_id where emp_id = '$id'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmpTestValues[] = $this->fetchrow();
				}
			}
			return $arrEmpTestValues;
		}

		function fnGetEmpTestMarksById($id)
		{
			$arrEmpTestValues = array();
			$query = "SELECT t1.id AS tm_id, t1.title AS tm_title, t1.test_id AS tm_test_id, t1.title_criteria AS tm_criteria, t2.title AS sub_title FROM `pms_emp_test_marks` AS t1 LEFT JOIN `pms_emp_test` AS t2 ON t1.test_id = t2.id WHERE t1.`id` = '".mysql_real_escape_string($id)."' ";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmpTestValues[] = $this->fetchrow();
				}
			}
			return $arrEmpTestValues;
		}

		function fnGetTestSubCategory($id)
		{
			$arrEmpTestValues = array();
			$query = "SELECT id as test_id,title as test_title,par_id as test_parent_id FROM `pms_emp_test` where par_id = '$id'";
			//echo $query = "SELECT * FROM `pms_emp_test` as emp_test left join pms_emp_test_details as details on  emp_test.id = details.test_id where emp_id = '$id'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmpTestValues[] = $this->fetchrow();
				}
			}
			return $arrEmpTestValues;
		}
		
		function fnGetTestCriteria($id)
		{
			$arrEmpTestValues = array();
			$query = "SELECT id as criteria_id,title as criteria_title,title_criteria as title_criteria FROM `pms_emp_test_marks` where test_id = '$id'";
			//echo $query = "SELECT * FROM `pms_emp_test` as emp_test left join pms_emp_test_details as details on  emp_test.id = details.test_id where emp_id = '$id'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrEmpTestValues[] = $this->fetchrow();
				}
			}
			return $arrEmpTestValues;
		}
		function fnGetTestMarksByChildParent($eid,$testParId,$test_id)
		{
			$marks = '';
			$query = "SELECT marks FROM `pms_emp_test_marks_details` where test_parent_id = '$testParId' and test_id = '$test_id' and emp_id = '$eid' ";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$marks = $this->f('marks');
				}
			}
			return $marks;
		}

		function fnGetTestMarksTitleByChildParent($eid,$testParId,$test_id)
		{
			$marks = '';
			$query = "SELECT t1.marks, t2.title, t2.title_criteria FROM `pms_emp_test_marks_details` AS t1 INNER JOIN `pms_emp_test_marks` AS t2 ON t1.marks = t2.id WHERE t1.test_parent_id = '$testParId' and t1.test_id = '$test_id' and t1.emp_id = '$eid' ";

			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$marks = $this->f('title').' - '.$this->f('title_criteria');
				}
			}
			return $marks;
		}
		
	}
?>
