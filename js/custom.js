function zerofill(number, length) {
    // Setup
    var result = number.toString();
    var pad = length - result.length;

    while(pad > 0) {
    	result = '0' + result;
    	pad--;
    }

    return result;
}

function checkblank($obj,$msg)
{
	if($.trim($obj.val()) == "")
	{
		alert($msg);
		$obj.focus();
		
		return false;
	}
	return true;
}

function checkfloat($obj, $msg){
	var n = $.trim($obj.val());
	if(isNaN(parseFloat(n)) || !isFinite(n))
	{
		alert($msg);
		$obj.focus();
		return false;
	}
	return true;
}

function hour2min($hrs)
{
	$hrs = $.trim($hrs);
	$totalMins = 0;
	if($hrs != "")
	{
		$arrTime = $hrs.split(":");
		
		$totalMins = ($arrTime[0] * 60) + parseInt($arrTime[1],10);
	}
	return $totalMins;
}

function h2m($hrs)
{
	$hrs = $.trim($hrs);
	$hrs = zerofill($hrs.replace(':', ''), 4);

	if($hrs != "")
	{
		$h = $hrs.substr(0,2);
		$m = $hrs.substr(2,2);

		$totalMins = ($h * 60) + parseInt($m,10);
	}
	return $totalMins;
}
jQuery.extend( jQuery.fn.dataTableExt.oSort,
{
	"date-uk-pre": function ( a )
	{
		var ukDatea = a.split('-');
		return (ukDatea[2] + ukDatea[1] + ukDatea[0]) * 1;
	},	"date-uk-asc": function ( a, b )
	{
		return ((a < b) ? -1 : ((a > b) ? 1 : 0));
	}, "date-uk-desc": function ( a, b )
	{
		return ((a < b) ? 1 : ((a > b) ? -1 : 0));
	}
});
