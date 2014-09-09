<?php
include_once('db_mysql.php');
	class roles extends DB_Sql
	{
		function __construct()
		{
		}
		function fnInsertRoles($arrRoles)
		{
			//$arrInsertRoles = array("id"=>$arrRoles['id'],"title"=>$arrRoles['title'],"description"=>$arrRoles['description']);
			$lastinsertid = $this->insertArray('pms_roles',$arrRoles);
			return $lastinsertid;
		}

		function fnInsertRoleDetails($id,$arrayData)
		{
			foreach($arrayData as $arrayDatavalue)
			{
			$arrInsertRoles = array("role_id"=>$id,"modules"=>$arrayDatavalue);
			$lastinsertid = $this->insertArray('pms_role_details',$arrInsertRoles);
			}
			return true;
		}

		function fnGetAllRoles()
		{
			$arrRolesValues = array();
			//$query = "SELECT * ,roles.id AS emp_id,designation.id as des_id, designation.title as des_title,departments.title as dep_title FROM `pms_roles` AS roles INNER JOIN pms_departments AS departments ON roles.department =departments.id INNER JOIN pms_designation AS designation ON roles.designation =designation.id ";
			$query = "SELECT * FROM `pms_roles`";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRolesValues[] = $this->fetchrow();
				}
			}
			return $arrRolesValues;
		}

		function fnGetRolesById($id)
		{
			$arrRolesValues = array();
			//$query = "SELECT * ,roles.id AS emp_id,designation.id as des_id,departments.id as dep_id, designation.title as des_title,departments.title as dep_title FROM `pms_roles` AS roles LEFT JOIN pms_departments AS departments ON roles.department =departments.id LEFT JOIN pms_designation AS designation ON roles.designation =designation.id WHERE roles.id ='".mysql_real_escape_string($id)."'";
			$query = "SELECT *,pms_roles.id as role_id, pms_roles.title as rols_title,pms_roles.description as rols_description FROM `pms_roles` WHERE `id` = '".mysql_real_escape_string($id)."' ";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrRolesValues[] = $this->fetchrow();
				}
			}
			return $arrRolesValues;
		}


		function fnUpdateRoles($arrPost)
		{
			$arrInsertRoles = array("id"=>$arrPost['id'],"title"=>$arrPost['title'],"description"=>$arrPost['description']);
			$this->updateArray('pms_roles',$arrInsertRoles);
			return true;
		}

		function fnUpdateRolesDetails($arrPost)
		{
			$query = "DELETE FROM `pms_role_details` WHERE `role_id` = '".mysql_real_escape_string($arrPost['id'])."'";
			$this->query($query);

			foreach($arrPost['modules'] as $arrayDatavalue)
			{
			$arrInsertRoles = array("role_id"=>$arrPost['id'],"modules"=>$arrayDatavalue);
			$this->insertArray('pms_role_details',$arrInsertRoles);
			}
			return true;
		}

		function fnDeleteRoles($arrvalues)
		{
			if(isset($arrvalues[chk]))
			{
				foreach($arrvalues[chk] as $arrval)
				{
					$query = "DELETE FROM `pms_roles` WHERE `id` = '".mysql_real_escape_string($arrval)."'";
					$this->query($query);
				}
				return true;
			}
			else
			{
				return false;
			}
		}

		function fnGetAllModules()
		{
			$arrAllModules = array();
			$query = "SELECT * FROM  `pms_module`";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAllModules[] = $this->fetchrow();
				}
			}
			return $arrAllModules;
		}

		function fnGetAllRoleDetails($id)
		{
			$arrAllRoleDetails = array();
			$query = "SELECT `modules` FROM  `pms_role_details` WHERE `role_id` = '$id'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAllRoleDetails[] = $this->fetchrow();
				}
			}
			foreach($arrAllRoleDetails as $roles)
			{
				$modules[] = $roles['modules'];
			}
			return $modules;
		}

		function fnGetAllModulesById($id)
		{
			$arrAllModules = array();
			$query = "SELECT *,module.title as mod_title FROM  `pms_role_details` as role INNER JOIN `pms_module` as module ON role.modules = module.id  WHERE role_id = '$id'";
			$this->query($query);

			if($this->num_rows() > 0)
			{
				while($this->next_record())
				{
					$arrAllModules[] = $this->fetchrow();
				}
			}
			return $arrAllModules;
		}

		function fnGetRoleIdByName($RoleName)
		{
			$RoleId = 0;
			$sSQL = "select id from pms_roles where title='".mysql_real_escape_string($RoleName)."'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$RoleId = $this->f("id");
				}
			}
			
			return $RoleId;
		}
		
		function fnGetRoleForQualityMasterSelection()
		{
			$arrRolesIds = array();
			
			$sSQL = "select r.id from pms_roles r INNER JOIN pms_role_details rd ON r.id = rd.role_id INNER JOIN pms_module m ON m.id = rd.modules where m.title='Quality Leveling Form'";
			$this->query($sSQL);
			if($this->num_rows() > 0)
			{
				if($this->next_record())
				{
					$arrRolesIds[] = $this->f("id");
				}
			}
			
			return $arrRolesIds;
		}
}
?>
