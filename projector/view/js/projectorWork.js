// ============================================================================
// All specific ProjectOr functions for work management
// This file is included in the main.php page, to be reachable in every context
// ============================================================================

/**
 * Open / Close Group : hide sub-lines
 */
function workOpenCloseLine(line) {
	var nbLines=dojo.byId('nbLines').value;
	var wbsLine=dojo.byId('wbs_'+line).value;
	var action=(dojo.byId('status_'+line).value=='opened')?"close":"open";
	if (action=="close") {
		dojo.byId('group_' + line).className="ganttExpandClosed";
		dojo.byId('status_'+line).value="closed";
	} else {
		dojo.byId('group_' + line).className="ganttExpandOpened";
		dojo.byId('status_'+line).value="opened";
	}
	for (i=line+1; i<=nbLines; i++) {
		var wbs=dojo.byId('wbs_'+i).value;
		if (wbs.length <= wbsLine.length ) {
			break;
		} 
		if (action=="close") {
		  dojo.byId('line_' + i).style.display = "none";
		} else {
			dojo.byId('line_' + i).style.display = "";
			var status=dojo.byId('status_'+i).value;
			if (status=='closed') {
				var wbsClosed=dojo.byId('wbs_'+i).value;
				for (j=i+1; j<=nbLines; j++) {
					var wbsSub=dojo.byId('wbs_'+j).value;
					if (wbsSub.indexOf(wbsClosed)==-1) {
						break;
					}
				}
				i=j-1;
			}
		}
	}	
}

/**
 * Refresh the imputation list
 * @return
 */
function refreshImputationList() {
	if (formChangeInProgress) {
		showAlert(i18n('alertOngoingChange'));
		return false;
	}
	formInitialize();
	dojo.byId('userId').value=dijit.byId('userName').value;
	dojo.byId('idle').checked=dojo.byId('listShowIdle').checked;
	dojo.byId('showPlannedWork').checked=dojo.byId('listShowPlannedWork').checked;
	loadContent('../view/refreshImputationList.php', 'workDiv', 'listForm', false);
	return true;
}

/**
 * Refresh the imputation list after period update (check format first)
 * @return
 */
function refreshImputationPeriod() {
	if (formChangeInProgress) {
		showAlert(i18n('alertOngoingChange'));
		return false;
	}
	var year=dijit.byId('yearSpinner').value;
	var week=dijit.byId('weekSpinner').value + '';
	if (week.length==1) {
		week='0' + week;
	}
	dojo.byId('rangeValue').value='' + year + week;
	refreshImputationList();
	return true;
}

/**
 * Dispatch updates for a work value : to column sum, real work, left work and planned work
 * @param rowId
 * @param colId
 * @return
 */
function dispatchWorkValueChange(rowId, colId) {
	var oldWorkValue=dojo.byId('workOldValue_' + rowId + '_' + colId).value;
	if (oldWorkValue==null || oldWorkValue=='') {oldWorkValue=0;}		
	var newWorkValue=dijit.byId('workValue_' + rowId + '_' + colId).attr('value');
	if (isNaN(newWorkValue)) {
		newWorkValue=0;
	}
	var diff=newWorkValue-oldWorkValue;
	// Update sum for column
	var oldSum=dijit.byId('colSumWork_' + colId).value;
	var newSum=oldSum + diff;
	newSum=Math.round(newSum*10)/10;
	dijit.byId('colSumWork_' + colId).setValue(newSum);
  //Update real work
	var oldReal=dijit.byId('realWork_' + rowId).value;
	var newReal=oldReal + diff;
	dijit.byId('realWork_' + rowId).setValue(newReal);
  //Update left work
	var oldLeft=dijit.byId('leftWork_' + rowId).value;
	var newLeft=oldLeft - diff;
	newLeft=(newLeft<0)?0:newLeft;
	dijit.byId('leftWork_' + rowId).setValue(newLeft);
  //Update planned work
	var newPlanned=newReal+newLeft;
	dijit.byId('plannedWork_' + rowId).setValue(newPlanned);
	// store new value for next calculation...
	dojo.byId('workOldValue_' + rowId + '_' + colId).value=newWorkValue;
	formChanged();
}

/**
 * Dispatch updates for left work : re-calculate planned work 
 */
function dispatchLeftWorkValueChange(rowId) {
	var newLeft=dijit.byId('leftWork_' + rowId).value;
	var newReal=dijit.byId('realWork_' + rowId).value;
	var newPlanned=newReal+newLeft;
	dijit.byId('plannedWork_' + rowId).setValue(newPlanned);
	formChanged();
}