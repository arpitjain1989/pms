<?php

	include_once("includes/class.stock_register.php");
	
	$objStockRegister = new stock_register();

	if(isset($_REQUEST["action"]))
	{
		if($_REQUEST["action"] == "fillInventory" && isset($_REQUEST["id"]) && trim($_REQUEST["id"]) != '')
		{
			$typeId = $objStockRegister->fnGetStockTypeByStockId(trim($_REQUEST["id"]));
			
			$stockArr = $objStockRegister->fnGetInStockInventoryByType($typeId);
			
			echo "<select name='replace_with' id='replace_with'>";
			echo "<option value=''>Please Select</option>";
			
			if(count($stockArr) > 0)
			{
				foreach($stockArr as $curStock)
				{
					echo "<option value='".$curStock["id"]."'>".$curStock["uniqueid"]."</option>";
				}
			}
			
			echo "</select>";
		}
	}

?>
