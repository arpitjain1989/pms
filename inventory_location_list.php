<?php

	include('common.php');
	$tpl = new Template($app_path);

	/* Load template */
	$tpl->load_file('template.html','main');
	$tpl->load_file('inventory_location_list.html','main_container');

	/* Rights management */
	$PageIdentifier = "InventoryLocation";
	include_once('userrights.php');
	
	/* Set heading */
	$tpl->set_var("mainheading","Manage Inventory Location");
	
	/* Set breadcrumb */
	$breadcrumb = '<li class="active">Manage Inventory Location</li>';
	$tpl->set_var("mainbreadcrumb",$breadcrumb);

	include_once('includes/class.inventory_location.php');
	$objInventoryLocation = new inventory_location();

	/* Display message block */
	$tpl->set_var("DisplayMessageBlock","");
	if(isset($_REQUEST["info"]))
	{
		switch($_REQUEST["info"])
		{
			case 'success':
				$messageClass = "alert-success";
				$message = "Inventory Location added successfully";
				break;
			case 'err':
				$messageClass = "alert-error";
				$message = "Inventory Location already added. Cannot add again.";
				break;
			case 'norec':
				$messageClass = "alert-error";
				$message = "No records found in uploaded CSV.";
				break;
			case 'invalid':
				$messageClass = "alert-error";
				$message = "Invalid file type. Cannot read the data.";
				break;
			case 'upload':
				if(isset($_REQUEST["err"]) && trim($_REQUEST["err"]) != "" && trim($_REQUEST["err"]) != "0")
				{
					$messageClass = "alert-error";
					$message = $_REQUEST["err"]." Inventory Location already added. Cannot add again.";
				}
				else
				{
					$messageClass = "alert-success";
					$message = "Inventory Location uploaded successfully.";
				}
				break;
		}

		if($message != "")
		{
			$tpl->set_var("message",$message);
			$tpl->set_var("message_class",$messageClass);

			$tpl->parse("DisplayMessageBlock",false);	
		}
	}
	
	if(isset($_POST["action"]) && trim($_POST["action"]) == "UploadLocationCsv")
	{
		$filename = $_FILES["location_csv"]["name"];
		if($filename != "")
		{
			$arrfilename = explode(".", $filename);
			$ext = array_pop($arrfilename);
	
			if($ext == "csv")
			{
				$row = 0;
				$errcnt = 0;
				
				if (($handle = fopen($_FILES["location_csv"]["tmp_name"], "r")) !== FALSE) 
				{
					while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
					{
						$arr = array();
						if($row > 0)
						{
							$arr["location_name"] = strtoupper(trim($data[0]));
							$arr["description"] = trim($data[1]);
							if(!$objInventoryLocation->fnSaveInventoryLocation($arr))
								$errcnt++;
						}
						$row++;
					}
					fclose($handle);
				}

				if($row > 1)
				{
					header("Location: inventory_location_list.php?info=upload&err=$errcnt");
					exit;
				}
				else
				{
					header("Location: inventory_location_list.php?info=norec");
					exit;
				}
			}
			else
			{
				header("Location: inventory_location_list.php?info=invalid");
				exit;
			}
		}
	}

	$arrInventoryLocation = $objInventoryLocation->fnGetAllInventoryLocation();

	/* Display list */
	$tpl->set_var("FillInventoryLocationList","");
	if(count($arrInventoryLocation) >0)
	{
		foreach($arrInventoryLocation as $curInventoryLocation)
		{
			$tpl->SetAllValues($curInventoryLocation);
			$tpl->parse("FillInventoryLocationList",true);
		}
	}

	$tpl->pparse('main',false);

?>
