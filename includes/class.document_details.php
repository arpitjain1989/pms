<?php
include_once('db_mysql.php');
	class document_details extends DB_Sql
	{
		function __construct()
		{
		}
		function fnInsertRCTDivision($arrEmployee)
		{
			$arrNewRecords = array("title"=>$arrEmployee['title'],"description"=>$arrEmployee['description']);
			$this->insertArray('pms_rct_division',$arrNewRecords);
			return true;
		}
		function fnGetAllDocumentsDetails()
		{
			$arrDocumentDetails = array();
			$query = "SELECT doc.*,emp.name as emp_name,emp.id as emp_id FROM `pms_employee` as emp  LEFT JOIN pms_document_details as doc on emp.id = doc.userid where emp.status = '0'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrDocumentDetails[] = $this->fetchrow();
				}
			}
			return $arrDocumentDetails;
		}

		function fnGetDocumentDetailsById($id)
		{
			//$arrDocumentDetails = array();
			$query = "Select doc.*,emp.name as emp_name,emp.id as emp_id FROM `pms_employee` as emp  LEFT JOIN pms_document_details as doc on emp.id = doc.userid where emp.`id` = '$id'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrDocumentDetails = $this->fetchrow();
				}
			}
			return $arrDocumentDetails;
		}
		
		function fnGetCandidateDocumentDetailsById($id)
		{
			//$arrDocumentDetails = array();
			$query = "Select doc.*,emp.name as emp_name,emp.id as emp_id FROM `pms_user_registration` as emp  LEFT JOIN `pms_candidate_document_details` as doc on emp.id = doc.candid where emp.`id` = '$id'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrDocumentDetails = $this->fetchrow();
				}
			}
			return $arrDocumentDetails;
		}

		function fnUpdateDocumentDetails($arrPost,$file)
		{
			//echo '<pre>'; print_r($file); die;
			
			$photo_extension = '';
			$ssc_image_extension = '';
			$hsc_image_extension = '';
			$lc_image_extension = '';
			$degree_image_extension = '';
			$diploma_image_extension = '';
			$pg_image_extension = '';
			$add_cert_image_extension = '';
			$id_proof_image_extension = '';
			$address_proof_image_extension = '';
			$bgr_image_extension = '';
			$prv_comp_doc_image_extension = '';
			$mou_notary_image_extension = '';
			$pvf_image_extension = '';
			$pf_no_image_extension = '';
			$esic_no_image_extension = '';
			
			if($_FILES["photo"]["name"] != '')
			{
				$photo_original = explode('.', $_FILES["photo"]["name"]);
				$photo_extension = array_pop($photo_original);
				$photo_name = $arrPost['userid'].'.'.$photo_extension;
				move_uploaded_file($_FILES["photo"]["tmp_name"],"media/photos/" . $photo_name);
				$arrPost['photo_extension'] = $photo_extension;
			}
			if($_FILES["ssc_image"]["name"] != '')
			{
				$ssc_image_original = explode('.', $_FILES["ssc_image"]["name"]);
				$ssc_image_extension = array_pop($ssc_image_original);
				$photo_name = $arrPost['userid'].'.'.$ssc_image_extension;
				move_uploaded_file($_FILES["ssc_image"]["tmp_name"],"media/ssc/" . $photo_name);
				$arrPost['ssc_image_extension'] = $ssc_image_extension;
			}
			if($_FILES["hsc_image"]["name"] != '')
			{
				$hsc_image_original = explode('.', $_FILES["hsc_image"]["name"]);
				$hsc_image_extension = array_pop($hsc_image_original);
				$photo_name = $arrPost['userid'].'.'.$hsc_image_extension;
				move_uploaded_file($_FILES["hsc_image"]["tmp_name"],"media/hsc/" . $photo_name);
				$arrPost['hsc_image_extension'] = $hsc_image_extension;
			}
			if($_FILES["lc_image"]["name"] != '')
			{
				$lc_image_original = explode('.', $_FILES["lc_image"]["name"]);
				$lc_image_extension = array_pop($lc_image_original);
				$photo_name = $arrPost['userid'].'.'.$lc_image_extension;
				move_uploaded_file($_FILES["lc_image"]["tmp_name"],"media/lc/" . $photo_name);
				$arrPost['lc_image_extension'] = $lc_image_extension;
			}
			if($_FILES["degree_image"]["name"] != '')
			{
				$degree_image_original = explode('.', $_FILES["degree_image"]["name"]);
				$degree_image_extension = array_pop($degree_image_original);
				$photo_name = $arrPost['userid'].'.'.$degree_image_extension;
				move_uploaded_file($_FILES["degree_image"]["tmp_name"],"media/degree/" . $photo_name);
				$arrPost['degree_image_extension'] = $degree_image_extension;
			}
			if($_FILES["pg_image"]["name"] != '')
			{
				$pg_image_original = explode('.', $_FILES["pg_image"]["name"]);
				$pg_image_extension = array_pop($pg_image_original);
				$photo_name = $arrPost['userid'].'.'.$pg_image_extension;
				move_uploaded_file($_FILES["pg_image"]["tmp_name"],"media/pg/" . $photo_name);
				$arrPost['pg_image_extension'] = $pg_image_extension;
			}
			if($_FILES["add_cert_image"]["name"] != '')
			{
				$add_cert_image_original = explode('.', $_FILES["add_cert_image"]["name"]);
				$add_cert_image_extension = array_pop($add_cert_image_original);
				$photo_name = $arrPost['userid'].'.'.$add_cert_image_extension;
				move_uploaded_file($_FILES["add_cert_image"]["tmp_name"],"media/cert/" . $photo_name);
				$arrPost['add_cert_image_extension'] = $add_cert_image_extension;
			}
			if($_FILES["id_proof_image"]["name"] != '')
			{
				$id_proof_image_original = explode('.', $_FILES["id_proof_image"]["name"]);
				$id_proof_image_extension = array_pop($id_proof_image_original);
				$photo_name = $arrPost['userid'].'.'.$id_proof_image_extension;
				move_uploaded_file($_FILES["id_proof_image"]["tmp_name"],"media/idProof/" . $photo_name);
				$arrPost['id_proof_image_extension'] = $id_proof_image_extension;
			}
			if($_FILES["address_proof_image"]["name"] != '')
			{
				$address_proof_image_original = explode('.', $_FILES["address_proof_image"]["name"]);
				$address_proof_image_extension = array_pop($address_proof_image_original);
				$photo_name = $arrPost['userid'].'.'.$address_proof_image_extension;
				move_uploaded_file($_FILES["address_proof_image"]["tmp_name"],"media/address/" . $photo_name);
				$arrPost['address_proof_image_extension'] = $address_proof_image_extension;
			}
			if($_FILES["bgr_image"]["name"] != '')
			{
				$bgr_image_original = explode('.', $_FILES["bgr_image"]["name"]);
				$bgr_image_extension = array_pop($bgr_image_original);
				$photo_name = $arrPost['userid'].'.'.$bgr_image_extension;
				move_uploaded_file($_FILES["bgr_image"]["tmp_name"],"media/bgr/" . $photo_name);
				$arrPost['bgr_image_extension'] = $bgr_image_extension;
			}
			if($_FILES["prv_comp_doc_image"]["name"] != '')
			{
				$prv_comp_doc_image_original = explode('.', $_FILES["prv_comp_doc_image"]["name"]);
				$prv_comp_doc_image_extension = array_pop($prv_comp_doc_image_original);
				$photo_name = $arrPost['userid'].'.'.$prv_comp_doc_image_extension;
				move_uploaded_file($_FILES["prv_comp_doc_image"]["tmp_name"],"media/pvr_com_doc/" . $photo_name);
				$arrPost['prv_comp_doc_image_extension'] = $prv_comp_doc_image_extension;
			}
			if($_FILES["mou_notary_image"]["name"] != '')
			{
				$mou_notary_image_original = explode('.', $_FILES["mou_notary_image"]["name"]);
				$mou_notary_image_extension = array_pop($mou_notary_image_original);
				$photo_name = $arrPost['userid'].'.'.$mou_notary_image_extension;
				move_uploaded_file($_FILES["mou_notary_image"]["tmp_name"],"media/mou_notary/" . $photo_name);
			}
			if($_FILES["pvf_image"]["name"] != '')
			{
				$pvf_image_original = explode('.', $_FILES["pvf_image"]["name"]);
				$pvf_image_extension = array_pop($pvf_image_original);
				$photo_name = $arrPost['userid'].'.'.$pvf_image_extension;
				move_uploaded_file($_FILES["pvf_image"]["tmp_name"],"media/pvf/" . $photo_name);
			}
			if($_FILES["pf_no_image"]["name"] != '')
			{
				$pf_no_image_original = explode('.', $_FILES["pf_no_image"]["name"]);
				$pf_no_image_extension = array_pop($pf_no_image_original);
				$photo_name = $arrPost['userid'].'.'.$pf_no_image_extension;
				move_uploaded_file($_FILES["pf_no_image"]["tmp_name"],"media/pf_no/" . $photo_name);
			}
			if($_FILES["esic_no_image"]["name"] != '')
			{
				$esic_no_image_original = explode('.', $_FILES["esic_no_image"]["name"]);
				$esic_no_image_extension = array_pop($esic_no_image_original);
				$photo_name = $arrPost['userid'].'.'.$esic_no_image_extension;
				move_uploaded_file($_FILES["esic_no_image"]["tmp_name"],"media/esic_no/" . $photo_name);
			}
			
			$query = "select id from pms_document_details where userid = '".$arrPost['userid']."'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$id = $this->f('id');
				}
			};
			
			if($id != '')
			{
				$arrPost['id'] = $id;
				//echo '<pre>'; print_r($arrPost); die;
				$this->updateArray('pms_document_details',$arrPost);
				return true;
			}
			else
			{
				//echo 'hello1'; die;
				$this->insertArray('pms_document_details',$arrPost);
				return true;
			}
		}
		function fnUpdateCadidateDocumentDetails($arrPost)
		{
			//echo '<pre>'; print_r($arrPost); die;
			$arrPost['candid'] = $arrPost['userid'];

			$checkUserExistanceInDocumentTable = $this->fnCheckUserExistanceInDocumentTable($arrPost['candid']);
			if($checkUserExistanceInDocumentTable != '0')
			{
				$arrPost['id'] = $checkUserExistanceInDocumentTable;
				$this->updateArray('pms_candidate_document_details',$arrPost);
			}
			else
			{
				$this->insertArray('pms_candidate_document_details',$arrPost);
			}
			return true;
		}

		function fnCheckUserExistanceInDocumentTable($id)
		{
			$count = '0';
			$query = "select id from pms_candidate_document_details where candid = $id";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$count = $this->f('id');
				}
			}
			
			return $count;
		}

		function fnGetAllPendingDocumentsDetails()
		{
			$arrDocumentDetails = array();
			//echo $query = "SELECT doc.*,emp.name as emp_name,emp.id as emp_id FROM `pms_employee` as emp  LEFT JOIN pms_document_details as doc on emp.id = doc.userid where emp.designation NOT IN(17)";
			$query = "SELECT doc.*,emp.name as emp_name,emp.id as emp_id,doc.photos as doc_photos FROM `pms_employee` as emp  LEFT JOIN pms_document_details as doc on emp.id = doc.userid where (doc.photos IS NULL or doc.ssc IS NULL or doc.hsc IS NULL or doc.lc IS NULL or doc.degree IS NULL or doc.diploma IS NULL or doc.pg IS NULL or doc.additional_cert IS NULL  or doc.id_proof IS NULL or doc.address_proof IS NULL or doc.bgr IS NULL or doc.prv_comp_doc IS NULL or doc.mou_notary IS NULL or doc.pvf IS NULL or doc.pf_no IS NULL or doc.esic_no IS NULL or doc.photos NOT IN(1,3,4) or doc.hsc NOT IN(1,3,4) or doc.lc NOT IN(1,3,4) or doc.additional_cert NOT IN(1,3,4) or doc.id_proof NOT IN(1,3,4) or doc.address_proof NOT IN(1,3,4) or doc.bgr NOT IN(1,3,4) or doc.prv_comp_doc NOT IN(1,3,4) or doc.mou_notary NOT IN(1,3,4) or doc.pvf NOT IN(1,3,4) or doc.pf_no NOT IN(1,3,4) or doc.esic_no NOT IN(1,3,4)) and emp.status = '0'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrDocumentDetails[] = $this->fetchrow();
				}
			}
			return $arrDocumentDetails;
		}

		function fnGetAllGraduation()
		{
			$arrAllGraduations = array();
			$query = "SELECT id,title FROM `pms_graduation`";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAllGraduations[] = $this->fetchrow();
				}
			}
			return $arrAllGraduations;
		}
		
		function fnGetAllPostGraduation()
		{
			$arrAllPostGraduations = array();
			$query = "SELECT id,title FROM `pms_post_graduation`";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAllPostGraduations[] = $this->fetchrow();
				}
			}
			return $arrAllPostGraduations;
		}

		function fnGetAllAddressProof()
		{
			$arrAllAddressProof = array();
			$query = "SELECT id,title FROM `pms_proof` where type = '2'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAllAddressProof[] = $this->fetchrow();
				}
			}
			return $arrAllAddressProof;
		}

		function fnGetAllIdProof()
		{
			$arrAllIdProof = array();
			$query = "SELECT id,title FROM `pms_proof` where type = '1'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAllIdProof[] = $this->fetchrow();
				}
			}
			return $arrAllIdProof;
		}
		function fnGetDegreeName($id)
		{
			$degreeName = '';
			$query = "SELECT id,title from  `pms_graduation` where id = '$id'";
			$this->query($query);
			
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$degreeName = $this->f('title');
				}
			}
			return $degreeName;
		}
		
		function fnGetPGDegreeName($id)
		{
			$degreeName = '';
			$query = "SELECT id,title from  `pms_post_graduation` where id = '$id'";
			$this->query($query);
			
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$degreeName = $this->f('title');
				}
			}
			return $degreeName;
		}
		
		function fnGetIdProofName($id)
		{
			$degreeName = '';
			$query = "SELECT id,title from `pms_proof` where id = '$id'";
			$this->query($query);
			
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$degreeName = $this->f('title');
				}
			}
			return $degreeName;
		}
		
	}
?>
