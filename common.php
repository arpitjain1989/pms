<?php
/*********************************************************************************
 *       Filename: common.php
 *       Generated with CodeCharge 1.1.19
 *       PHP & Templates build 03/28/2001
 *********************************************************************************/
//header("Location: under_maintenance.php");die;

session_start();

error_reporting(0);

set_time_limit(0);

include("template.php");

date_default_timezone_set('Asia/Kolkata');

$ServerURL = "http://localhost/pms/";

define("SERVERURL","http://" . $_SERVER['SERVER_NAME'] . "/pms/");
define("SITENAME","TransForm Solution : Intranet");
define("SITEADMINISTRATOR","TSPL");

// Database Parameters


/*
// Database Initialize
$db = new DB_Sql();
$db->Database = DATABASE_NAME;
$db->User     = DATABASE_USER;
$db->Password = DATABASE_PASSWORD;
$db->Host     = DATABASE_HOST;

$mb = new DB_Sql();
$mb->Database = DATABASE_NAME;
$mb->User     = DATABASE_USER;
$mb->Password = DATABASE_PASSWORD;
$mb->Host     = DATABASE_HOST;

$mb1 = new DB_Sql();
$mb1->Database = DATABASE_NAME;
$mb1->User     = DATABASE_USER;
$mb1->Password = DATABASE_PASSWORD;
$mb1->Host     = DATABASE_HOST;


$qrydb = new DB_Sql();
$qrydb->Database = DATABASE_NAME;
$qrydb->User     = DATABASE_USER;
$qrydb->Password = DATABASE_PASSWORD;
$qrydb->Host     = DATABASE_HOST;*/

$app_path = "./template";

function tohtml($strValue)
{
  return htmlspecialchars($strValue);
}

function tourl($strValue)
{
  return urlencode($strValue);
}

function get_param($param_name)
{
  global $HTTP_POST_VARS;
  global $HTTP_GET_VARS;

  $param_value = "";
  if(isset($HTTP_POST_VARS[$param_name]))
    $param_value = $HTTP_POST_VARS[$param_name];
  else if(isset($HTTP_GET_VARS[$param_name]))
    $param_value = $HTTP_GET_VARS[$param_name];

  return $param_value;
}

function get_session($param_name)
{
  global $HTTP_POST_VARS;
  global $HTTP_GET_VARS;
  global ${$param_name};

  $param_value = "";
  if(!isset($HTTP_POST_VARS[$param_name]) && !isset($HTTP_GET_VARS[$param_name]) && session_is_registered($param_name))
    $param_value = ${$param_name};

  return $param_value;
}

function set_session($param_name, $param_value)
{
  global ${$param_name};
  if(session_is_registered($param_name))
    session_unregister($param_name);
  ${$param_name} = $param_value;
  session_register($param_name);
}

function is_number($string_value)
{
  if(is_numeric($string_value) || !strlen($string_value))
    return true;
  else
    return false;
}

function tosql($value, $type)
{
  if($value == "")
    return "NULL";
  else
    if($type == "Number")
      return doubleval($value);
    else
    {
      if(get_magic_quotes_gpc() == 0)
      {
        $value = str_replace("'","''",$value);
        $value = str_replace("\\","\\\\",$value);
      }
      else
      {
        $value = str_replace("\\'","''",$value);
        $value = str_replace("\\\"","\"",$value);
      }

      return "'" . $value . "'";
    }
}

function strip($value)
{
  if(get_magic_quotes_gpc() == 0)
    return $value;
  else
    return stripslashes($value);
}

function db_fill_array($sql_query)
{
  $db_fill = new DB_Sql();
  $db_fill->Database = DATABASE_NAME;
  $db_fill->User     = DATABASE_USER;
  $db_fill->Password = DATABASE_PASSWORD;
  $db_fill->Host     = DATABASE_HOST;

  $db_fill->query($sql_query);
  if ($db_fill->next_record())
  {
    do
    {
      $ar_lookup[$db_fill->f(0)] = $db_fill->f(1);
    } while ($db_fill->next_record());
    return $ar_lookup;
  }
  else
    return false;

}

function dlookup($table_name, $field_name, $where_condition)
{
  $db_look = new DB_Sql();
  $db_look->Database = DATABASE_NAME;
  $db_look->User     = DATABASE_USER;
  $db_look->Password = DATABASE_PASSWORD;
  $db_look->Host     = DATABASE_HOST;

  $db_look->query("SELECT " . $field_name . " FROM " . $table_name . " WHERE " . $where_condition);
  if($db_look->next_record())
    return $db_look->f(0);
  else
    return "";
}

function get_checkbox_value($value, $checked_value, $unchecked_value, $type)
{
  if(!strlen($value))
    return tosql($unchecked_value, $type);
  else
    return tosql($checked_value, $type);
}

function get_lov_value($value, $array)
{
  $return_result = "";

  if(sizeof($array) % 2 != 0)
    $array_length = sizeof($array) - 1;
  else
    $array_length = sizeof($array);
  reset($array);

  for($i = 0; $i < $array_length; $i = $i + 2)
  {
    if($value == $array[$i]) $return_result = $array[$i+1];
  }

  return $return_result;
}

function check_security($security_level)
{
  global $UserRights;
  if(!session_is_registered("UserID"))
    header ("Location: Login.php?querystring=" . tourl(getenv("QUERY_STRING")) . "&ret_page=" . tourl(getenv("REQUEST_URI")));
  else
    if(!session_is_registered("UserRights") || $UserRights < $security_level)
      header ("Location: Login.php?querystring=" . tourl(getenv("QUERY_STRING")) . "&ret_page=" . tourl(getenv("REQUEST_URI")));
}

/*
 * Generates a globally unique id
 *  */
function guid()
{

	$charid = strtoupper(md5(uniqid(rand(), true)));
	$hyphen = chr(45);// "-"
	$uuid = md5(substr($charid, 0, 8)).$hyphen
			.md5(substr($charid, 8, 4)).$hyphen
			.md5(substr($charid,12, 4)).$hyphen
			.md5(substr($charid,16, 4)).$hyphen
			.md5(substr($charid,20,12));

	return $uuid;

}

/* Generate a unique code for team leader and manager for approval through mail */
function leaveform_uid()
{
	$db = new DB_Sql();
	
	$uniqId = uniqid();
	$sSQL = "select * from pms_leave_form where tlapprovalcode='".mysql_real_escape_string($uniqId)."' or managerapprovalcode='".mysql_real_escape_string($uniqId)."' or delegatedtlapprovalcode='".mysql_real_escape_string($uniqId)."' or delegatedmanagerapprovalcode='".mysql_real_escape_string($uniqId)."'";
	$db->query($sSQL);
	if($db->num_rows() > 0)
	{
		return leaveform_uid();
	}
	else
	{
		return $uniqId;
	}
}

/* Generate a unique code for team leader and manager for approval through mail */
function halfdayleaveform_uid()
{
	$db = new DB_Sql();
	
	$uniqId = uniqid();
	$sSQL = "select * from pms_half_leave_form where tlapprovalcode='".mysql_real_escape_string($uniqId)."' or managerapprovalcode='".mysql_real_escape_string($uniqId)."' or delegatedtlapprovalcode='".mysql_real_escape_string($uniqId)."' or delegatedmanagerapprovalcode='".mysql_real_escape_string($uniqId)."'";
	$db->query($sSQL);
	if($db->num_rows() > 0)
	{
		return halfdayleaveform_uid();
	}
	else
	{
		return $uniqId;
	}
}

/* Generate a unique code for team leader and manager for approval through mail */
function shiftmovementform_uid()
{
	$db = new DB_Sql();
	
	$uniqId = uniqid();
	$sSQL = "select * from pms_shift_movement where tlapprovalcode='".mysql_real_escape_string($uniqId)."' or managerapprovalcode='".mysql_real_escape_string($uniqId)."' or delegatedtlapprovalcode='".mysql_real_escape_string($uniqId)."' or delegatedmanagerapprovalcode='".mysql_real_escape_string($uniqId)."'";
	$db->query($sSQL);
	if($db->num_rows() > 0)
	{
		return shiftmovementform_uid();
	}
	else
	{
		return $uniqId;
	}
}

/* Generate a unique code for team leader for approval through mail */
function shiftmovementcompensationform_uid()
{
	$db = new DB_Sql();
	
	$uniqId = uniqid();
	$sSQL = "select * from pms_shift_movement_compensation where tlapprovalcode='".mysql_real_escape_string($uniqId)."' or delegatedtlapprovalcode='".mysql_real_escape_string($uniqId)."'";
	$db->query($sSQL);
	if($db->num_rows() > 0)
	{
		return shiftmovementcompensationform_uid();
	}
	else
	{
		return $uniqId;
	}
}

/* Generate a unique code for team leader for approval through mail */
function compensationform_uid()
{
	$db = new DB_Sql();
	
	$uniqId = uniqid();
	$sSQL = "select * from pms_exceed_compensation where tlapprovalcode='".mysql_real_escape_string($uniqId)."' or delegatedtlapprovalcode='".mysql_real_escape_string($uniqId)."'";
	$db->query($sSQL);
	if($db->num_rows() > 0)
	{
		return compensationform_uid();
	}
	else
	{
		return $uniqId;
	}
}

/* Generate a unique code for attrition for approval through mail */
function attrition_uid()
{
	$db = new DB_Sql();
	
	$uniqId = uniqid();
	$sSQL = "select * from pms_attrition_process where tlapprovalcode='".mysql_real_escape_string($uniqId)."' or managerapprovalcode='".mysql_real_escape_string($uniqId)."' or hrapprovalcode='".mysql_real_escape_string($uniqId)."' or adminapprovalcode='".mysql_real_escape_string($uniqId)."'";
	$db->query($sSQL);
	if($db->num_rows() > 0)
	{
		return attrition_uid();
	}
	else
	{
		return $uniqId;
	}
}


/* Generate a unique code for team leader and manager for approval through mail */
function requisition_uid()
{
	$db = new DB_Sql();
	
	$uniqId = uniqid();
	$sSQL = "select * from pms_requisition where approvalcode='".mysql_real_escape_string($uniqId)."' or delegated_reporting_head_approvalcode='".mysql_real_escape_string($uniqId)."'";
	$db->query($sSQL);
	if($db->num_rows() > 0)
	{
		return requisition_uid();
	}
	else
	{
		return $uniqId;
	}
}


/*
 * Sends Mail
 * */
function sendmail_($MailTo, $Subject, $content)
{
	
	//$MailTo = "chandni.patel@transformsolution.net";
	
	/*$Headers = "MIME-Version: 1.0" . "\r\n";
	$Headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
	$Headers .= "From: Transform<transform.pms@gmail.com>";

	if(@mail($MailTo,$Subject,$MailContent,$Headers))
		return true;
	else
		return false;*/

	//echo $content;

	include_once('includes/class.phpmailer.php');

	try {
		$mail = new PHPMailer(true); //New instance, with exceptions enabled

		//$body = file_get_contents('contents.html');
		$body = preg_replace('/\\\\/','', $content); //Strip backslashes

		$mail->IsSMTP();  // telling the class to use SMTP
		$mail->Mailer = "smtp";
		$mail->Host = "ssl://smtp.gmail.com";
		$mail->Port = 465;
		$mail->SMTPAuth = true; // turn on SMTP authentication
		$mail->Username = "transform.pms@gmail.com"; // SMTP username
		$mail->Password = "Transform@123"; // SMTP password

		$mail->FromName     = SITENAME;
		$mail->From     = $mail->Username;

		$mail->AddAddress($MailTo);

		$mail->Subject  = $Subject;
		//$mail->Body     = $content;
		//$mail->WordWrap = 50;

		$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!";

		$mail->MsgHTML($body);

		$mail->IsHTML(true); // send as HTML

		$mail->Send();

		/*if($mail->Send())
			echo "sent";
		else
			echo "not sent";*/


	} catch (phpmailerException $e) {
		echo $e->errorMessage();
	}
}

/*
 * Sends Mail With Attachments (as string attachments)
 * */
function sendmail($MailTo, $Subject, $content, $attachments = array())
{
	
	//$MailTo = "chandni.patel@transformsolution.net";
	$MailTo = explode(',',$MailTo);
	/*$Headers = "MIME-Version: 1.0" . "\r\n";
	$Headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
	$Headers .= "From: Transform<transform.pms@gmail.com>";

	if(@mail($MailTo,$Subject,$MailContent,$Headers))
		return true;
	else
		return false;*/

	//echo $content;

	include_once('includes/class.phpmailer.php');

	try {
		$mail = new PHPMailer(true); //New instance, with exceptions enabled

		//$body = file_get_contents('contents.html');
		$body = preg_replace('/\\\\/','', $content); //Strip backslashes

		$mail->IsSMTP();  // telling the class to use SMTP
		$mail->Mailer = "smtp";
		$mail->Host = "ssl://smtp.gmail.com";
		$mail->Port = 465;
		$mail->SMTPAuth = true; // turn on SMTP authentication
		$mail->Username = "transform.pms@gmail.com"; // SMTP username
		$mail->Password = "Transform@123"; // SMTP password

		$mail->FromName     = SITENAME;
		$mail->From     = $mail->Username;

		//$mail->AddAddress($MailTo);
		if(count($MailTo) > 0)
		{
			foreach($MailTo as $key => $mailT)
			{
				$mail->AddAddress($mailT);
			}
		}

		$mail->Subject  = $Subject;
		//$mail->Body     = $content;
		//$mail->WordWrap = 50;

		$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!";

		$mail->MsgHTML($body);

		$mail->IsHTML(true); // send as HTML

		if(count($attachments) > 0)
		{
			foreach($attachments as $curAttachment)
			{
				$mail->AddStringAttachment($curAttachment["file_string"], $curAttachment["file_name"], $curAttachment["file_encoding"], $curAttachment["file_mime_type"]);
			}
		}

		$mail->Send();

		/*if($mail->Send())
			echo "sent";
		else
			echo "not sent";*/


	} catch (phpmailerException $e) {
		echo $e->errorMessage();
	}
}

function sendmail1($MailTo, $Subject, $content)
{
	/*$Headers = "MIME-Version: 1.0" . "\r\n";
	$Headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
	$Headers .= "From: Transform<transform.pms@gmail.com>";

	if(@mail($MailTo,$Subject,$MailContent,$Headers))
		return true;
	else
		return false;*/

	//echo $content;

	//$MailTo = "chandni.patel@transformsolution.net";

	include_once('includes/class.phpmailer.php');

	try {
		$mail = new PHPMailer(true); //New instance, with exceptions enabled

		//$body = file_get_contents('contents.html');
		$body = preg_replace('/\\\\/','', $content); //Strip backslashes

		$mail->IsSMTP();  // telling the class to use SMTP
		$mail->Mailer = "smtp";
		$mail->Host = "ssl://smtp.gmail.com";
		$mail->Port = 465;
		$mail->SMTPAuth = true; // turn on SMTP authentication
		$mail->Username = "transform.pms@gmail.com"; // SMTP username
		$mail->Password = "Transform@123"; // SMTP password

		$mail->FromName     = SITENAME;
		$mail->From     = $mail->Username;

		$mail->AddAddress($MailTo);

		$mail->Subject  = $Subject;
		//$mail->Body     = $content;
		//$mail->WordWrap = 50;

		$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!";

		$mail->MsgHTML($body);

		$mail->IsHTML(true); // send as HTML

		//$mail->Send();

		/*if($mail->Send())
			echo "sent";
		else
			echo "not sent";*/


	} catch (phpmailerException $e) {
		echo $e->errorMessage();
	}
}

	function bytesToSize($bytes, $precision = 2)
	{
		$kilobyte = 1024;
		$megabyte = $kilobyte * 1024;
		$gigabyte = $megabyte * 1024;
		$terabyte = $gigabyte * 1024;

		if (($bytes >= 0) && ($bytes < $kilobyte)) {
			return $bytes . ' B';

		} elseif (($bytes >= $kilobyte) && ($bytes < $megabyte)) {
			return round($bytes / $kilobyte, $precision) . ' KB';

		} elseif (($bytes >= $megabyte) && ($bytes < $gigabyte)) {
			return round($bytes / $megabyte, $precision) . ' MB';

		} elseif (($bytes >= $gigabyte) && ($bytes < $terabyte)) {
			return round($bytes / $gigabyte, $precision) . ' GB';

		} elseif ($bytes >= $terabyte) {
			return round($bytes / $terabyte, $precision) . ' TB';
		} else {
			return $bytes . ' B';
		}
	}

	function fnRedirectUrl($strUrl)
	{
		if(!headers_sent())
		{
			header("Location: $strUrl");
			exit();
		}
		else
		{
			?>
			<script type="text/javascript">
				location.href = "<?php echo $strUrl;?>";
			</script>
			<?php

		}
	}
	
	/* Functions needed to generate excel file */

	function xlsBOF()
    {
		echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
		return;
    }
    function xlsEOF()
    {
		echo pack("ss", 0x0A, 0x00);
		return;
    }
    function xlsWriteNumber($Row, $Col, $Value)
    {
		echo pack("sssss", 0x203, 14, $Row, $Col, 0x0);
		echo pack("d", $Value);
		return;
    }
    function xlsWriteLabel($Row, $Col, $Value )
    {
		$L = strlen($Value);
		echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
		echo $Value;
		return;
    }

?>
