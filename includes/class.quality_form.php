<?php 
	include_once('db_mysql.php');
	class quality_form extends DB_Sql
	{
		function __construct()
		{
		}
		
		function getForm()
		{
			$arrData = array();
			$query = "SELECT id as form_id ,type as form_type FROM pms_qa_form where isdeleted='0'";
			$this->query($query);
			if($this->num_rows() > 0)
			{	
				while($this->next_record())
				{
					$arrData[] = $this->fetchrow();
				}
			}
			return $arrData;
		}
		
		function fnSaveQualityFormType($arrQualityForm)
		{
			if(isset($arrQualityForm["id"]) && trim($arrQualityForm["id"]) == "")
			{
				if($this->fnValidateQualityFormType($arrQualityForm["type"]))
					$this->insertArray("pms_qa_form",$arrQualityForm);
				else
					return false;
			}
			else
			{
				if($this->fnValidateQualityFormType($arrQualityForm["type"], $arrQualityForm["id"]))
					$this->updateArray("pms_qa_form",$arrQualityForm);
				else
					return false;
			}
			return true;
		}
		
		function fnDeleteQualityFormType($arrIds)
		{
			$noRecErr = $err = 0;
			if(count($arrIds) > 0)
			{
				foreach($arrIds as $curId)
				{
					$sSQL = "select * from pms_qa_form where id='".mysql_real_escape_string($curId)."' and isdeleted='0'";
					$this->query($sSQL);
					if($this->num_rows())
					{
						if($this->next_record())
						{
							$sSQL = "select * from pms_qa_parameter where form_id='".mysql_real_escape_string($curId)."' and isdeleted='0'";
							$this->query($sSQL);
							if($this->num_rows() == 0)
							{
								$updateInfo["id"] = $curId;
								$updateInfo["isdeleted"] = '1';
								
								$this->updateArray("pms_qa_form",$updateInfo);
							}
							else
							{
								$err++;
							}
						}
						else
						{
							$noRecErr++;
						}
					}
					else
					{
						$noRecErr++;
					}
				}
			}

			return array("noRecErr"=>$noRecErr, "err"=>$err);
		}
		
		function fnValidateQualityFormType($quality_form, $id = 0)
		{
			$cond = "";
			if($id != 0)
				$cond = " and id!='$id'";

			$sSQL = "select * from pms_qa_form where type='".mysql_real_escape_string($quality_form)."' and isdeleted='0' $cond";
			$this->query($sSQL);
			if($this->num_rows() == 0)
				return true;
			else
				return false;
		}
		
		function fnGetQualityFormTypeById($id)
		{
			$arrFormType = array();

			$sSQL = "select * from pms_qa_form where id='".mysql_real_escape_string($id)."' and isdeleted='0'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrFormType = $this->fetchRow();
				}
			}

			return $arrFormType;
		}
		
		function getFormNameById($id)
		{
			$formName = '';
			$query = "SELECT `type` FROM `pms_qa_form` WHERE `id`='$id' and isdeleted='0'";
			$this->query($query);
			if($this->next_record())
			{
				$formName = $this->f('type');
			}
			return $formName;
		}
		
		function fnGetAllQualityParameters()
		{
			$arrParameter = array();
			
			$sSQL = "select p.id, p.title as parameter_title, f.type as form_type, f.id as form_type_id, p.isactive from pms_qa_parameter p INNER JOIN pms_qa_form f ON f.id = p.form_id and p.isdeleted='0'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrParameter[] = $this->fetchRow();
				}
			}
			
			return $arrParameter;
		}
		
		function fnDeleteQualityParameter($arrIds)
		{
			$noRecErr = $err = 0;
			if(count($arrIds) > 0)
			{
				foreach($arrIds as $curId)
				{
					$sSQL = "select * from pms_qa_parameter where id='".mysql_real_escape_string($curId)."' and isdeleted='0'";
					$this->query($sSQL);
					if($this->num_rows())
					{
						if($this->next_record())
						{
							$sSQL = "select * from pms_qa_afd where parameterid='".mysql_real_escape_string($curId)."' and isdeleted='0'";
							$this->query($sSQL);
							if($this->num_rows() == 0)
							{
								$updateInfo["id"] = $curId;
								$updateInfo["isdeleted"] = '1';
								
								$this->updateArray("pms_qa_parameter",$updateInfo);
							}
							else
							{
								$err++;
							}
						}
						else
						{
							$noRecErr++;
						}
					}
					else
					{
						$noRecErr++;
					}
				}
			}

			return array("noRecErr"=>$noRecErr, "err"=>$err);
		}
		
		function fnGetParameterById($id)
		{
			$arrParameter = array();
			
			$sSQL = "select p.*, f.type as form_type from pms_qa_parameter p INNER JOIN pms_qa_form f ON f.id = p.form_id where p.id='".mysql_real_escape_string($id)."' and p.isdeleted='0'";
			$this->query($sSQL);
			if($this->num_rows())
			{
				if($this->next_record())
				{
					$arrParameter = $this->fetchRow();
				}
			}
			
			return $arrParameter;
		}
		
		function fnSaveQualityParameter($arrQualityParameter)
		{
			if(isset($arrQualityParameter["id"]) && trim($arrQualityParameter["id"]) == "")
			{
				if($this->fnValidateQualityParameter($arrQualityParameter["form_id"], $arrQualityParameter["title"]))
					$this->insertArray("pms_qa_parameter",$arrQualityParameter);
				else
					return false;
			}
			else
			{
				if($this->fnValidateQualityParameter($arrQualityParameter["form_id"], $arrQualityParameter["title"], $arrQualityParameter["id"]))
					$this->updateArray("pms_qa_parameter",$arrQualityParameter);
				else
					return false;
			}
			return true;
		}
		
		function fnValidateQualityParameter($quality_form_id, $quality_parameter, $id = 0)
		{
			$cond = "";
			if($id != 0)
				$cond = " and id!='$id'";

			$sSQL = "select * from pms_qa_parameter where title='".mysql_real_escape_string($quality_parameter)."' and form_id='".mysql_real_escape_string($quality_form_id)."' and isdeleted='0' $cond";
			$this->query($sSQL);
			if($this->num_rows() == 0)
				return true;
			else
				return false;
		}
		
		function getParameterByFormId($id)
		{
			$arrParameter = array();
			$query = "SELECT `id` AS para_id,`title` AS paratitle FROM `pms_qa_parameter` WHERE `form_id`='$id' and isdeleted='0'";
			$this->query($query);
			if($this->num_rows() >0)
			{
				while($this->next_record())
				{
					$arrParameter[] = $this->fetchrow();
				}
			}
			return $arrParameter;
		}
		
		function fnGetAllQualityAFD()
		{
			$arrAfds = array();
			
			$sSQL = "select a.*, p.title as parameter_title, f.type as form_type from pms_qa_afd a INNER JOIN pms_qa_parameter p ON p.id = a.parameterid INNER JOIN pms_qa_form f ON f.id = p.form_id and a.isdeleted='0'";
			$this->query($sSQL);
			if($this->num_rows())
			{
				while($this->next_record())
				{
					$arrAfds[] = $this->fetchRow();
				}
			}
			
			return $arrAfds;
		}
		
		function fnDeleteQualityAfd($arrIds)
		{
			$noRecErr = 0;
			if(count($arrIds) > 0)
			{
				foreach($arrIds as $curId)
				{
					$sSQL = "select * from pms_qa_afd where id='".mysql_real_escape_string($curId)."' and isdeleted='0'";
					$this->query($sSQL);
					if($this->num_rows())
					{
						if($this->next_record())
						{
							$updateInfo["id"] = $curId;
							$updateInfo["isdeleted"] = '1';
							
							$this->updateArray("pms_qa_afd",$updateInfo);
						}
						else
						{
							$noRecErr++;
						}
					}
					else
					{
						$noRecErr++;
					}
				}
			}

			return $noRecErr;
		}
		
		function fnSaveQualityAfd($arrQualityAfd)
		{
			if(isset($arrQualityAfd["id"]) && trim($arrQualityAfd["id"]) == "")
			{
				if($this->fnValidateQualityAfd($arrQualityAfd["parameterid"], $arrQualityAfd["title"]))
					$this->insertArray("pms_qa_afd",$arrQualityAfd);
				else
					return false;
			}
			else
			{
				if($this->fnValidateQualityAfd($arrQualityAfd["parameterid"], $arrQualityAfd["title"], $arrQualityAfd["id"]))
					$this->updateArray("pms_qa_afd",$arrQualityAfd);
				else
					return false;
			}
			return true;
		}
		
		function fnValidateQualityAfd($quality_form_id, $quality_afd, $id = 0)
		{
			$cond = "";
			if($id != 0)
				$cond = " and id!='$id'";

			$sSQL = "select * from pms_qa_afd where title='".mysql_real_escape_string($quality_afd)."' and parameterid='".mysql_real_escape_string($quality_form_id)."' and isdeleted='0' $cond";
			$this->query($sSQL);
			if($this->num_rows() == 0)
				return true;
			else
				return false;
		}
		
		function fnGetAfdById($id)
		{
			$arrQualityAFD = array();
			
			$sSQL = "select a.*, p.title as parameter_title, f.type as form_type from pms_qa_afd a INNER JOIN pms_qa_parameter p ON a.parameterid = p.id INNER JOIN pms_qa_form f ON f.id = p.form_id where a.id='".mysql_real_escape_string($id)."' and a.isdeleted='0'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrQualityAFD = $this->fetchRow();
				}
			}
			
			return $arrQualityAFD;
		}
		
		function getAllAfdNames($id)
		{
			$arrAfds = array();
			$query = "SELECT `id` AS afdid,`title` AS afdtitle,`parameterid` AS para_id FROM `pms_qa_afd` WHERE `parameterid` ='$id' and isdeleted='0'";
			$this->query($query);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAfds[] = $this->fetchrow();
				}
			}
			return $arrAfds;
		}
		
		function insertFormData($data)
		{
			if($this->fnCheckLevelingEntryEnabled())
			{
				$noOfRecords = $this->searchRecordById($data['recordid']);
				if($noOfRecords > 0)
				{
					return "0"; 
				}
				else
				{
					$masterId = $this->fnFetchLastMaster();
					$arrRecords = array("userid"=>$_SESSION['id'],"recordid"=>$data['recordid'],"form_id"=>$_SESSION["qa_leveling"]["qa_form_id"],"master_id"=>$masterId);

					$lastid = $this->insertArray('pms_qa_formdata',$arrRecords);
					
					foreach($data['paraid'] as $data1)
					{
						$arrNewRecords = array("formdata_id"=>$lastid,"haserror"=>$data['para'][$data1],"afd_id"=>$data['afd'][$data1],"para_id"=>$data1,"comment"=>$data['comment'][$data1]);
						$this->insertArray('pms_qa_formdata_details',$arrNewRecords); 
					}
					return "1";
				}
			}
			else
			{
				return "-1";
			}
		}

		function updateMasterFormData($data)
		{
			$sSQL = "select * from pms_qa_formdata where recordid = '".mysql_real_escape_string($data["recordid"])."' and id='".mysql_real_escape_string($data["id"])."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrRecords = array("id"=>$this->f("id"),"recordid"=>$data["recordid"],"insert_date"=>$data["insert_date"]);

					$lastid = $this->updateArray('pms_qa_formdata',$arrRecords);

					foreach($data['paraid'] as $data1)
					{
						$arrNewRecords = array("formdata_id"=>$lastid,"haserror"=>$data['para'][$data1],"afd_id"=>$data['afd'][$data1],"para_id"=>$data1,"id"=>$data['formDetailId'][$data1],"comment"=>$data['comment'][$data1]);
						$this->updateArray('pms_qa_formdata_details',$arrNewRecords);
					}
					return true;
				}
				else
					return false;
			}
			else
				return false;
		}
		
		function searchRecordById($recordId, $entryId = 0)
		{
			$cond = "";
			if($entryId != 0)
				$cond = " and id != '".mysql_real_escape_string($entryId)."'";
			$query = "SELECT * FROM `pms_qa_formdata` WHERE `userid` = '".$_SESSION['id']."' AND `recordid`='$recordId'".$cond;
			$this->query($query);
			return($this->num_rows());
		}
		
		function getFormDates()
		{
			$arrFormDate = array();
			$query = "SELECT DISTINCT Date_format( `insert_date` , '%Y-%m-%d' ) as formdate FROM `pms_qa_formdata` order by insert_date desc";
			$this->query($query);
			
			if($this->num_rows() > 0)
			{
				while($this->next_record())				
				{
					$arrFormDate[] = $this->fetchrow();
				}
			}
			return $arrFormDate;
		}
		
		function getReportData($date,$ftype)
		{
			$arrReportData = array();
			$query = "SELECT DISTINCT recordid as recordid FROM `pms_qa_formdata` WHERE date_format(`insert_date`,'%Y-%m-%d')='$date' AND `form_id` = '$ftype'";
			$this->query($query);
			
			if($this->num_rows() >0)
			{
				while($this->next_record())
				{
					$arrReportData[] = $this->fetchrow();
				}
			}
			return $arrReportData;
		}
		
		function getParameterInfo($id)
		{
			$arrParameterInfo = array();
			$query = "SELECT * FROM `pms_qa_parameter` WHERE `form_id`='$id' and isdeleted='0'";
			$this->query($query);
			
			if($this->num_rows() >0)
			{
				while($this->next_record())
				{
					$arrParameterInfo[] = $this->fetchrow();
				}
			}
			return $arrParameterInfo;
		}
		
		function getAfdsDetailsData($date,$ptype)
		{
			$masterId = $this->fnFetchMasterForDateAndForm($date, $ptype);
			
			$arrAfdsDatailInfo = array();
			$query = "SELECT * FROM pms_qa_formdata AS formdata INNER JOIN pms_qa_formdata_details AS formdetail ON formdata.id = formdetail.formdata_id LEFT JOIN pms_qa_afd AS afd ON formdetail.afd_id = afd.id WHERE formdata.form_id = '$ptype' AND formdata.userid = '".mysql_real_escape_string($masterId)."' AND date_format( formdata.`insert_date` , '%Y-%m-%d' ) = '$date'";
			$this->query($query);
			
			if($this->num_rows() >0)
			{
				while($this->next_record())
				{
					$tmparr = $this->fetchrow();
					 if($tmparr["haserror"] == '2') { $tmparr["haserror"] ='No'; $tmparr["haserrorinfo"] ='Correct : No';}
					 else if($tmparr["haserror"] == '1') { $tmparr["haserror"] ='Yes'; $tmparr["haserrorinfo"] ='Correct : Yes'; }

					//$arrAfdsDatailInfo[$tmparr["recordid"]][$tmparr["para_id"]] = array("afdid" => $tmparr["afd_id"],"haserror" => $tmparr["haserror"], "haserrorinfo" => $tmparr["haserrorinfo"],"title" => $tmparr["title"],"points" => $tmparr["points"],"afdtitle" => $tmparr["afdtitle"],"para_id" => $tmparr["para_id"],"comment" => $tmparr["comment"]);
					$arrAfdsDatailInfo[$tmparr["recordid"]][$tmparr["para_id"]] = array("afdid" => $tmparr["afd_id"],"haserror" => $tmparr["haserror"], "haserrorinfo" => $tmparr["haserrorinfo"],"title" => $tmparr["title"],"para_id" => $tmparr["para_id"],"comment" => $tmparr["comment"]);
					
				}
			}
			return $arrAfdsDatailInfo;
		}
		
		function getAllMemberAfdValues($paraid,$recid,$date)
		{
			$arrMemberAfdValues = array();

			$formType = $this->fnGetFormTypeByRecordId($recid);

			$masterId = $this->fnFetchMasterForDateAndForm($date, $formType);

			$query = "SELECT * FROM pms_qa_formdata AS formdata INNER JOIN pms_qa_formdata_details AS formdetail ON formdata.id = formdetail.formdata_id INNER JOIN pms_employee AS employee ON formdata.userid = employee.id LEFT JOIN pms_qa_afd AS afd ON formdetail.afd_id = afd.id WHERE formdata.recordid = '$recid' AND date_format(formdata.insert_date,'%Y-%m-%d') ='$date' AND formdetail.`para_id` = '$paraid' AND formdata.userid != '".mysql_real_escape_string($masterId)."'";
			$this->query($query);

			if($this->num_rows() >0)
			{
				while($this->next_record())
				{
					$arrMemberAfdValues[] = $this->fetchrow();
				}
			}
			return $arrMemberAfdValues;
		}
		
		/*function getAllMembers()
		{
			$arrMembers = array();
			$query = "SELECT * FROM `pms_qa_employee`";
			$this->query($query);
			
			if($this->num_rows() > 0 )
			{
				while($this->next_record())
				{
					$arrMembers[] = $this->fetchrow();
				}
			}
			return $arrMembers;
		}*/
		
		function getAllMembers($date,$ftype)
		{
			$arrMembers = array();
			
			$sSQL = "select distinct e.id, e.name from pms_qa_formdata f INNER JOIN pms_employee e ON e.id = f.userid WHERE date_format(f.insert_date,'%Y-%m-%d') ='".mysql_real_escape_string($date)."' and f.form_id = '".mysql_real_escape_string($ftype)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrMembers[] = $this->fetchRow();
				}
			}
			
			return $arrMembers;
		}
		
		function fnGetMasterData($date,$ftype)
		{
			$arrMaster = array();
			$masterId = $this->fnFetchMasterForDateAndForm($date, $ftype);
			
			$query = "SELECT * FROM pms_qa_formdata AS formdata INNER JOIN pms_qa_formdata_details AS formdetail ON formdata.id = formdetail.formdata_id LEFT JOIN pms_qa_afd AS afd ON formdetail.afd_id = afd.id WHERE date_format(formdata.insert_date,'%Y-%m-%d') ='$date' AND formdata.userid = '".mysql_real_escape_string($masterId)."' AND formdata.form_id = '$ftype'";
			$this->query($query);
			
			if($this->num_rows() >0)
			{
				while($this->next_record())
				{
					$tmparr = $this->fetchrow();
					 if($tmparr["haserror"] == '2') { $tmparr["haserror"] ='No'; $tmparr["haserrorinfo"] ='Correct : No';}
					 else if($tmparr["haserror"] == '1') { $tmparr["haserror"] ='Yes'; $tmparr["haserrorinfo"] ='Correct : Yes'; }
					 
					//$arrMaster[$tmparr["recordid"]][$tmparr["para_id"]] = array("afdid" => $tmparr["afd_id"],"haserror" => $tmparr["haserror"], "haserrorinfo" => $tmparr["haserrorinfo"],"title" => $tmparr["title"],"points" => $tmparr["points"],"afdtitle" => $tmparr["afdtitle"],"para_id" => $tmparr["para_id"]);
					$arrMaster[$tmparr["recordid"]][$tmparr["para_id"]] = array("afdid" => $tmparr["afd_id"],"haserror" => $tmparr["haserror"], "haserrorinfo" => $tmparr["haserrorinfo"],"title" => $tmparr["title"],"para_id" => $tmparr["para_id"]);
				}
			}
			return $arrMaster;
		}
		
		function fnGetMemberData($memberid,$date,$ftype)
		{
			$arrMemberMaster = array();
			$query = $query = "SELECT * FROM pms_qa_formdata AS formdata INNER JOIN pms_qa_formdata_details AS formdetail ON formdata.id = formdetail.formdata_id LEFT JOIN pms_qa_afd AS afd ON formdetail.afd_id = afd.id WHERE date_format(formdata.insert_date,'%Y-%m-%d') ='$date' AND formdata.userid = '$memberid' AND formdata.form_id = '$ftype'";
			$this->query($query);
			
			if($this->num_rows() >0)
			{
				while($this->next_record())
				{			
					$tmparr = $this->fetchrow();
					 if($tmparr["haserror"] == '2') { $tmparr["haserror"] ='No'; $tmparr["haserrorinfo"] ='Correct : No';}
					 else if($tmparr["haserror"] == '1') { $tmparr["haserror"] ='Yes'; $tmparr["haserrorinfo"] ='Correct : Yes'; }
					 
					$arrMemberMaster[$tmparr["recordid"]][$tmparr["para_id"]] = array("afdid" => $tmparr["afd_id"],"haserror" => $tmparr["haserror"], "haserrorinfo" => $tmparr["haserrorinfo"],"title" => $tmparr["title"],"para_id" => $tmparr["para_id"]);
				}
			}
			return $arrMemberMaster;
		}
		
		function fnGetParaCount($formid)
		{
			$query = "SELECT `id` FROM `pms_qa_parameter` WHERE `form_id` = '$formid' and isdeleted='0'";
			$this->query($query);
			return($this->num_rows());
		}
		
		function fnGetAllRecoredId($date,$ftype)
		{
			$arrAllRecord = array();
			$query = "SELECT DISTINCT `formdata`.`recordid` FROM pms_qa_formdata AS formdata WHERE date_format( formdata.insert_date, '%Y-%m-%d' ) = '".mysql_real_escape_string($date)."' AND formdata.form_id = '".mysql_real_escape_string($ftype)."'"; 
			$this->query($query);
			
			if($this->num_rows() > 0 )
			{
				while($this->next_record())
				{
					$arrAllRecord[] = $this->fetchrow();
				}
			}
			return $arrAllRecord;
		}
		
		function fnGetAllParameters($formid)
		{
			$arrAllPara = array();
			$query = "SELECT id FROM pms_qa_parameter WHERE `form_id` = '$formid' and isdeleted='0'"; 
			$this->query($query);
			
			if($this->num_rows() > 0 )
			{
				while($this->next_record())
				{
					$arrAllPara[] = $this->fetchrow();
				}
			}
			return $arrAllPara;
		}
		function fnGetRecordData($date,$ftype,$empid)
		{
			$arrReportData = array();
			$query = "SELECT DISTINCT recordid as recordid FROM `pms_qa_formdata` WHERE date_format(`insert_date`,'%Y-%m-%d')='$date' AND `form_id` = '$ftype' AND `userid` = '$empid'";
			$this->query($query);
			
			if($this->num_rows() >0)
			{
				while($this->next_record())
				{
					$arrReportData[] = $this->fetchrow();
				}
			}
			return $arrReportData;
		}
		
		function getAfdsDetais($date,$ptype,$eid)
		{
			$arrAfdsDatailInfo = array();
			$query = "SELECT *,formdata.id as formdataid FROM pms_qa_formdata AS formdata INNER JOIN pms_qa_formdata_details AS formdetail ON formdata.id = formdetail.formdata_id LEFT JOIN pms_qa_afd AS afd ON formdetail.afd_id = afd.id WHERE formdata.form_id = '$ptype' AND formdata.userid = '$eid' AND date_format( formdata.`insert_date` , '%Y-%m-%d' ) = '$date'";
			$this->query($query);
			
			if($this->num_rows() >0)
			{
				while($this->next_record())
				{
					$tmparr = $this->fetchrow();
					 if($tmparr["haserror"] == '2') { $tmparr["haserror"] ='No'; $tmparr["haserrorinfo"] ='Correct : No';}
					 else if($tmparr["haserror"] == '1') { $tmparr["haserror"] ='Yes'; $tmparr["haserrorinfo"] ='Correct : Yes'; }

					$arrAfdsDatailInfo[$tmparr["recordid"]][$tmparr["para_id"]] = array("afdid" => $tmparr["afd_id"],"haserror" => $tmparr["haserror"], "haserrorinfo" => $tmparr["haserrorinfo"],"title" => $tmparr["title"],"afdtitle" => $tmparr["title"],"para_id" => $tmparr["para_id"],"formdataid"=>$tmparr["formdataid"],"comment"=>$tmparr["comment"]);
					
				}
			}
			return $arrAfdsDatailInfo;
		}
		
		function fnGetRecordById($recid,$date,$ftype)
		{
			$arrAfdsDatailInfo = array();
			$query = "SELECT *,formdata.id as formdataid,formdetail.id as newafdid FROM pms_qa_formdata AS formdata INNER JOIN pms_qa_formdata_details AS formdetail ON formdata.id = formdetail.formdata_id LEFT JOIN pms_qa_afd AS afd ON formdetail.afd_id = afd.id WHERE formdata.form_id = '$ftype' AND formdata.userid = '".$_SESSION["id"]."' AND date_format( formdata.`insert_date` , '%Y-%m-%d' ) = '$date' AND formdata.id = '$recid'";
			 
			//echo "<br />".$query = "SELECT * FROM pms_qa_formdata AS formdata INNER JOIN pms_qa_formdata_details AS formdetail ON formdata.id = formdetail.formdata_id WHERE formdata.form_id = '$ftype' AND formdata.userid = '".$_SESSION["id"]."' AND date_format( formdata.`insert_date` , '%Y-%m-%d' ) = '$date' AND formdata.id = '$recid'"; 
			 
			$this->query($query);
			
			if($this->num_rows() >0)
			{
				while($this->next_record())
				{
					$tmparr = $this->fetchrow();
					
					 if($tmparr["haserror"] == '2') { $tmparr["haserror"] ='No'; $tmparr["haserrorinfo"] ='Correct : No';}
					 else if($tmparr["haserror"] == '1') { $tmparr["haserror"] ='Yes'; $tmparr["haserrorinfo"] ='Correct : Yes'; }
					 
					//$arrAfdsDatailInfo[$tmparr["para_id"]] = array("afdid" => $tmparr["afd_id"],"haserror" => $tmparr["haserror"], "haserrorinfo" => $tmparr["haserrorinfo"],"title" => $tmparr["title"],"points" => $tmparr["points"],"afdtitle" => $tmparr["afdtitle"],"para_id" => $tmparr["para_id"],"formdataid" => $tmparr["formdataid"],"newafdid" => $tmparr["newafdid"],"recordid"=>$tmparr["recordid"],"comment"=>$tmparr["comment"]);

					$arrAfdsDatailInfo[$tmparr["para_id"]] = array("afdid" => $tmparr["afd_id"],"haserror" => $tmparr["haserror"], "haserrorinfo" => $tmparr["haserrorinfo"],"title" => $tmparr["title"],"para_id" => $tmparr["para_id"],"formdataid" => $tmparr["formdataid"],"newafdid" => $tmparr["newafdid"],"recordid"=>$tmparr["recordid"],"comment"=>$tmparr["comment"]);
				}
			}
			return $arrAfdsDatailInfo;
		}
		
		function fnGetRecordByIdForMasterEdit($recid,$date,$ftype)
		{
			$arrAfdsDatailInfo = array();
			$query = "SELECT *,formdata.id as formdataid,formdetail.id as newafdid, date_format(formdata.insert_date,'%Y-%m-%d') as insert_date FROM pms_qa_formdata AS formdata INNER JOIN pms_qa_formdata_details AS formdetail ON formdata.id = formdetail.formdata_id LEFT JOIN pms_qa_afd AS afd ON formdetail.afd_id = afd.id WHERE formdata.form_id = '$ftype' AND date_format( formdata.`insert_date` , '%Y-%m-%d' ) = '$date' AND formdata.id = '$recid'";

			$this->query($query);
			
			if($this->num_rows() >0)
			{
				while($this->next_record())
				{
					$tmparr = $this->fetchrow();
					
					 if($tmparr["haserror"] == '2') { $tmparr["haserror"] ='No'; $tmparr["haserrorinfo"] ='Correct : No';}
					 else if($tmparr["haserror"] == '1') { $tmparr["haserror"] ='Yes'; $tmparr["haserrorinfo"] ='Correct : Yes'; }

					$arrAfdsDatailInfo[$tmparr["para_id"]] = array("afdid" => $tmparr["afd_id"],"haserror" => $tmparr["haserror"], "haserrorinfo" => $tmparr["haserrorinfo"],"title" => $tmparr["title"],"para_id" => $tmparr["para_id"],"formdataid" => $tmparr["formdataid"],"newafdid" => $tmparr["newafdid"],"recordid"=>$tmparr["recordid"],"comment"=>$tmparr["comment"],"insert_date"=>$tmparr["insert_date"]);
				}
			}
			return $arrAfdsDatailInfo;
		}
		
		function updateFormData($data)
		{
			if($this->fnCheckLevelingEntryEnabled())
			{
				$noOfRecords = $this->searchRecordById($data['recordid'], $data['id']);
				if($noOfRecords > 0)
				{
					return "-1"; 
				}
				else
				{
					$arrRecords = array("id"=>$data['id'],"userid"=>$_SESSION['id'],"recordid"=>$data['recordid'],"form_id"=>$_SESSION['form_id']);
					$this->updateArray('pms_qa_formdata',$arrRecords);
					foreach($data['paraid'] as $data1)
					{
						$arrNewRecords = array("formdata_id"=>$lastid,"haserror"=>$data['para'][$data1],"afd_id"=>$data['afd'][$data1],"para_id"=>$data1,"id"=>$data['formDetailId'][$data1],"comment"=>$data['comment'][$data1]);
						$this->updateArray('pms_qa_formdata_details',$arrNewRecords); 
					}
					return "1";
				}
			}
			else
			{
				return "0";
			}
		}

		function fnSaveQualitySettings($masterId, $levelingStatus)
		{
			$sSQL = "select * from pms_quality_settings where master_id='".mysql_real_escape_string($masterId)."' and date_format(added_date,'%Y-%m-%d') = '".Date('Y-m-d')."' order by id desc limit 0,1";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					if($this->f("leveling_status") == '0')
					{
						/* Close leveling record if open */
						$updateInfo["id"] = $this->f("id");
						$updateInfo["leveling_status"] = $levelingStatus;

						$this->updateArray("pms_quality_settings",$updateInfo);

						if($levelingStatus == "1")
						{
							/* Update leveling records to stop editing */
							$sSQL = "update pms_qa_formdata set leveling_status='1' where date_format(insert_date, '%Y-%m-%d') = '".Date('Y-m-d')."'";
							$this->query($sSQL);
						}
					}
					else
					{
						/* Insert new leveling settings */
						$insertInfo["master_id"] = $masterId;
						$insertInfo["added_date"] = Date('Y-m-d H:i:s');
						$insertInfo["added_by"] = $_SESSION["id"];
						$insertInfo["leveling_status"] = $levelingStatus;

						$this->insertArray("pms_quality_settings",$insertInfo);
					}
				}
				else
				{
					/* Insert new leveling settings */
					$insertInfo["master_id"] = $masterId;
					$insertInfo["added_date"] = Date('Y-m-d H:i:s');
					$insertInfo["added_by"] = $_SESSION["id"];
					$insertInfo["leveling_status"] = $levelingStatus;

					$this->insertArray("pms_quality_settings",$insertInfo);
				}
			}
			else
			{
				/* Insert new leveling settings */
				$insertInfo["master_id"] = $masterId;
				$insertInfo["added_date"] = Date('Y-m-d H:i:s');
				$insertInfo["added_by"] = $_SESSION["id"];
				$insertInfo["leveling_status"] = $levelingStatus;

				$this->insertArray("pms_quality_settings",$insertInfo);
			}
		}
		
		function fnFetchLastMaster()
		{
			$masterId = 0;
			
			$sSQL = "select master_id from pms_quality_settings order by id desc limit 0,1";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$masterId = $this->f("master_id");
				}
			}
			
			return $masterId;
		}
		
		function fnFetchMasterForDateAndForm($workDate, $formType)
		{
			$masterId = 0;
			
			$sSQL = "SELECT DISTINCT master_id as masterid FROM `pms_qa_formdata` WHERE date_format(`insert_date`,'%Y-%m-%d')='".mysql_real_escape_string($workDate)."' AND `form_id` = '".mysql_real_escape_string($formType)."'";
			$this->query($sSQL);
			if($this->num_rows())
			{
				if($this->next_record())
				{
					$masterId = $this->f("masterid");
				}
			}
			
			return $masterId;
		}
		
		function fnGetFormTypeByRecordId($recordId)
		{
			$formTypeId = 0;

			$sSQL = "select form_id from pms_qa_formdata where recordid='".mysql_real_escape_string($recordId)."'";
			$this->query($sSQL);
			if($this->num_rows())
			{
				if($this->next_record())
				{
					$formTypeId = $this->f("form_id");
				}
			}

			return $formTypeId;
		}

		function fnGetLevelingData($date, $ftype)
		{
			$arrLevelingData = array();
			$sSQL = "select f.recordid, e.name, f.id as formdetail_id, f.form_id, date_format(insert_date,'%Y-%m-%d') as form_date from pms_qa_formdata f INNER JOIN pms_employee e ON e.id = f.userid where date_format(insert_date,'%Y-%m-%d') = '".mysql_real_escape_string($date)."' and form_id = '".mysql_real_escape_string($ftype)."'";
			$this->query($sSQL);
			
			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{			
					$arrLevelingData[] = $this->fetchrow();
				}
			}
			return $arrLevelingData;
		}
		
		function fnCheckIfLevelingPerformedByDate($date)
		{
			$sSQL = "select * from pms_qa_formdata where date_format(insert_date, '%Y-%m-%d') = '".mysql_real_escape_string($date)."' limit 0,1";
			$this->query($sSQL);
			if($this->num_rows() == 0)
				return false;
			else
				return true;
		}
		
		function fnCheckLevelingEntryEnabled()
		{
			$flag = false;
			
			$sSQL = "select leveling_status from pms_quality_settings order by id desc limit 0,1";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					if($this->f("leveling_status") == '0')
						$flag = true;
				}
			}
			
			return $flag;
		}
		
		function fnCheckLevelingEntryEnabledById($id)
		{
			$flag = false;
			$sSQL = "select leveling_status from pms_qa_formdata where id='".mysql_real_escape_string($id)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					if($this->f("leveling_status") == '0' || $this->f("leveling_status") == '')
						$flag = true;
				}
			}

			return $flag;
		}
	}

?>
