// ============================================================================
// All specific ProjectOr functions and variables
// This file is included in the main.php page, to be reachable in every context
// ============================================================================

//=============================================================================
//= global formating functions)
//=============================================================================

function formatDate(date) {
	var month=date.getMonth()+1;
	var year=date.getFullYear();
	var day=date.getDate()
	month=(month<10)?"0"+month:month;
	day=(day<10)?"0"+day:day;
	return year + "-" + month + "-" + day;
}

// ============================================================================
// = FORMATTERS (available for dojox.DataGrid formating)
// ============================================================================

/** ============================================================================
 * Format boolean to present a chechbox (checked or not depending on the value)
 * @param value the value of the boolean (true or false)
 * @return the formatted value as an image (html code)
 */
function booleanFormatter(value) {
  if (value==1) { 
  	return '<img src="img/checkedOK.png" width="12" height="12" />'; 
  } else { 
  	return '<img src="img/checkedKO.png" width="12" height="12" />'; 
  }
}

/** ============================================================================
 * Format value to present a color
 * @param value the value of the boolean (true or false)
 * @return the formatted value as an image (html code)
 */
function colorFormatter(value) {
  if (value) { 
  	return '<table width="100%"><tr><td style="background-color: ' + value + '; width: 100%;">&nbsp;</td></tr></table>'; 
  } else { 
  	return ''; 
  }
}

/** ============================================================================
 * Format value to present a name in a colored field
 * @param value the value of the boolean (true or false)
 * @return the formatted value as an image (html code)
 */
function colorNameFormatter(value) {
  if (value) {
  	var reg=new RegExp("(#split#)", "g");
  	var tab=value.split(reg);
  	if (tab.length>1) {
  		if (tab.length==3) { // just found : val #split# color
  			var val=tab[0];
  			var color=tab[2];
  			var order='';
  		} else if (tab.length==5) { // found : val #split# color #split# order
  			var val=tab[2];
  			var color=tab[4];
  			var order=tab[0];
  	  } else { // should not be found
  	  	return value;
  	  }
  		var foreColor='#000000';
  		if (color.length==7) {
  		  var red=color.substr(1,2);
  		  var green=color.substr(3,2);
  		  var blue=color.substr(5,2);
  		  var light=(0.3)*parseInt(red,16)+(0.6)*parseInt(green,16)+(0.1)*parseInt(blue,16);
  		  if (light<128) { foreColor='#FFFFFF'; }
  		}
  	  return '<span style="display:none;">' + order + '</span><table width="100%"><tr><td style="background-color: ' + color + '; color:' +foreColor + ';width: 100%;">' + val + '</td></tr></table>'; 
  	} else {
  		return value;
  	}
  } else { 
  	return ''; 
  }
}

/** ============================================================================
 * Format boolean to present a color
 * @param value the value of the boolean (true or false)
 * @return the formatted value as an image (html code)
 */
function translateFormatter(value, prefix) {
  if (value) { 
  	return i18n(value); 
  } else { 
  	return ''; 
  }
}

/** ============================================================================
 * Format percent value
 * @param value the value of the boolean (true or false)
 * @return the formatted value as an image (html code)
 */
function percentFormatter(value) {
  return value + ' %';
}

/** ============================================================================
 * Format numeric value (removes leading zeros)
 * @param value the value 
 * @return the formatted value 
 */
function numericFormatter(value) {
  return value.replace(/^0+/g,'');
}