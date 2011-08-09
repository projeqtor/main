// ============================================================================
// All specific ProjectOr functions and variables for Dialog Purpose
// This file is included in the main.php page, to be reachable in every context
// ============================================================================

//=============================================================================
//= Variables (global)
//=============================================================================

var filterType="";

//=============================================================================
//= Wait spinner
//=============================================================================

/** ============================================================================
 * Shows a wait spinner
 * @return void
 */
function showWait() {
	if (dojo.byId("wait")) {
		showField("wait");
	} else {
		showField("waitLogin");
	}
}

/** ============================================================================
 * Hides a wait spinner
 * @return void
 */
function hideWait() {
	hideField("wait");
	hideField("waitLogin");
}

//=============================================================================
//= Generic field visibility properties
//=============================================================================

/** ============================================================================
 * Setup the style properties of a field to set it visible (show it)
 * @param field the name of the field to be set 
 * @return void
 */
function showField(field) {
	var dest = dojo.byId(field);
  if (dijit.byId(field)) {
  	dest=dijit.byId(field).domNode;
  }
  if (dest) {
  	dojo.style(dest, {visibility:'visible'});
  	dojo.style(dest, {display:'inline'});
  	//dest.style.visibility = 'visible';
  	//dest.style.display = 'inline';
  }
}

/** ============================================================================
 * Setup the style properties of a field to set it invisible (hide it) 
 * @param field the name of the field to be set
 * @return void 
 */
function hideField(field) {
  var dest = dojo.byId(field);
  if (dijit.byId(field)) {
  	dest=dijit.byId(field).domNode;
  }
  if (dest) {
  	dojo.style(dest, {visibility:'hidden'});
  	dojo.style(dest, {display:'none'});
  	//dest.style.visibility = 'hidden';
  	//dest.style.display = 'none';
  }
}

//=============================================================================
//= Message boxes
//=============================================================================

/** ============================================================================
 * Display a Dialog Error Message Box
 * @param msg the message to display in the box 
 * @return void
 */
function showError (msg) {
	top.hideWait();
	top.dojo.byId("dialogErrorMessage").innerHTML=msg ;
	top.dijit.byId("dialogError").show();
}

/** ============================================================================
 * Display a Dialog Information Message Box
 * @param msg the message to display in the box 
 * @return void
 */
function showInfo (msg) {
	top.dojo.byId("dialogInfoMessage").innerHTML=msg ;
	top.dijit.byId("dialogInfo").show();
}

/** ============================================================================
 * Display a Dialog Alert Message Box
 * @param msg the message to display in the box 
 * @return void
 */
function showAlert (msg) {
	top.dojo.byId("dialogAlertMessage").innerHTML=msg ;
	top.dijit.byId("dialogAlert").show();
}

/** ============================================================================
 * Display a Dialog Question Message Box, with Yes/No buttons
 * @param msg the message to display in the box
 * @param actionYes the function to be executed if click on Yes button
 * @param actionNo the function to be executed if click on No button
 * @return void 
 */
function showQuestion (msg,actionYes, ActionNo) {
	dojo.byId("dialogQuestionMessage").innerHTML=msg ;
	dijit.byId("dialogQuestion").acceptCallbackYes=actionYes;
	dijit.byId("dialogQuestion").acceptCallbackNo=actionNo;
	dijit.byId("dialogQuestion").show();
}

/** ============================================================================
 * Display a Dialog Confirmation Message Box, with OK/Cancel buttons
 * NB : no action on Cancel click
 * @param msg the message to display in the box
 * @param actionOK the function to be executed if click on OK button
 * @return void 
 */
function showConfirm (msg, actionOK) {
	dojo.byId("dialogConfirmMessage").innerHTML=msg ;
	dijit.byId("dialogConfirm").acceptCallback=actionOK;
	dijit.byId("dialogConfirm").show();
}

/** ============================================================================
 * Display a About Box
 * @param msg the message of the about box (must be passed here because built in php)
 * @return void 
 */
function showAbout (msg) {
	showInfo(msg);
}

//=============================================================================
//= Print
//=============================================================================

/** ============================================================================
 * Display a Dialog Print Preview Box
 * @param page the page to display
 * @param forms the form containing the data to send to the page
 * @return void 
 */
function showPrint (page, context, comboName, outMode) {
	//dojo.byId('printFrame').style.width= 1000 + 'px';
	showWait();
	quitConfirmed=true;
	noDisconnect=true;
	if (! outMode) outMode='html';
	var printInNewWin=printInNewWindow;
	if (outMode=='pdf') {printInNewWin=true;}
	if ( ! printInNewWin) {
	  //window.frames['printFrame'].document.body.innerHTML='<i>' + i18n("messagePreview") + '</i>';
		//frames['printFrame'].location.href='../view/preparePreview.php';
		dijit.byId("dialogPrint").show();
	}
	if (dojo.byId('objectClass')) {
		cl=dojo.byId('objectClass').value;
	}
	if (dojo.byId('objectId')) {
		id=dojo.byId('objectId').value;
	}
	var params="";
	dojo.byId("sentToPrinterDiv").style.display='block';
	if (outMode) {
		params+="&outMode=" + outMode;
		if (outMode=='pdf') {
			dojo.byId("sentToPrinterDiv").style.display='none';
		}
	}
	if (context=='list') {
		if (dijit.byId("listShowIdle")) {
			if (dijit.byId("listShowIdle").get('checked')) {
			  params+="&idle=true";
			}
		}
		if (dijit.byId("listIdFilter")) {		
			if (dijit.byId("listIdFilter").get('value')) {
			  params+="&listIdFilter="+encodeURIComponent(dijit.byId("listIdFilter").get('value'));
			}
		}
		if (dijit.byId("listNameFilter")) {		
			if (dijit.byId("listNameFilter").get('value')) {
			  params+="&listNameFilter="+encodeURIComponent(dijit.byId("listNameFilter").get('value'));
			}
		}
		if (dijit.byId("listTypeFilter")) {		
			if (dijit.byId("listTypeFilter").get('value')) {
				params+="&objectType="+encodeURIComponent(dijit.byId("listTypeFilter").get('value'));
			}
		}
	} else if (context=='planning'){
		if (dijit.byId("startDatePlanView").get('value')) {
		  params+="&startDate="+encodeURIComponent(formatDate(dijit.byId("startDatePlanView").get("value")));
		  params+="&format="+g.getFormat();
		  if (dijit.byId('listShowIdle').get('checked')) {
		  	params+="&idle=true";
		  }
		  if (dijit.byId('showWBS').checked) { 
				params+="&showWBS=true";
		  }
		}
	} else if (context=='report'){
		var frm=dojo.byId('reportForm');
		frm.action="../view/print.php";
		if (outMode) {
			dojo.byId('outMode').value=outMode;
		} else {
			dojo.byId('outMode').value='';
		}
		if (printInNewWin) {
			frm.target='#';
		} else {
		  frm.target='printFrame';
		}
		frm.submit();
		hideWait();
		quitConfirmed=false;
		noDisconnect=false;
		return;
	} else if (context=='imputation'){
		var frm=dojo.byId('listForm');
		frm.action="../view/print.php";
		if (printInNewWin) {
			frm.target='#';
		} else {
		  frm.target='printFrame';
		}
		if (outMode) {
			dojo.byId('outMode').value=outMode;
		} else {
			dojo.byId('outMode').value='';
		}
		frm.submit();
		hideWait();
		quitConfirmed=false;
		noDisconnect=false;
		return;
	}
	var grid=dijit.byId('objectGrid');
	if (grid) {
		var sortWay=(grid.getSortAsc())?'asc':'desc';
		var sortIndex=grid.getSortIndex();
		if (sortIndex>=0) {
			params+="&sortIndex="+sortIndex;
			params+="&sortWay="+sortWay;
		}
		//alert("Sort on index "+sortIndex+" asc "+sortAsc);
	}
	if (printInNewWin) {
		var newWin=window.open("print.php?print=true&page="+page+"&objectClass="+cl+"&objectId="+id+params);
		hideWait();
	} else {
	  window.frames['printFrame'].location.href="print.php?print=true&page="+page+"&objectClass="+cl+"&objectId="+id+params;
	}
	quitConfirmed=false;
	noDisconnect=false;
	//document.getElementsByTagName('printFrame')[0].contentWindow.print();
}

function sendFrameToPrinter() {
	dojo.byId("sendToPrinter").blur();
  //printFrame.focus();
  //printFrame.print();
  window.frames['printFrame'].focus();
  window.frames['printFrame'].print();
  //var myRef = window.open(window.frames['printFrame'].location +"&directPrint=true",'mywin', 'left=20,top=20,width=500,height=500,toolbar=1,resizable=0');
  dijit.byId('dialogPrint').hide();        
}
//=============================================================================
//= Detail (from combo)
//=============================================================================

function showDetailDependency() {
	var depType=dijit.byId('dependencyRefTypeDep').get("value");
	if (depType) {
		var dependable=dependableArray[depType];
		var canCreate=0;
		if (canCreateArray[dependable]=="YES") {
			canCreate=1;
	    }
		showDetail('dependencyRefIdDep',canCreate, dependable);
		
	} else {
		showInfo(i18n('messageMandatory', new Array(i18n('linkType'))));
	}
}

function showDetailLink() {
	var linkType=dijit.byId('linkRef2Type').get("value");
	if (linkType) {
		var linkable=linkableArray[linkType];
		var canCreate=0;
		if (canCreateArray[linkable]=="YES") {
			canCreate=1;
	    }
		showDetail('linkRef2Id',canCreate, linkable);
		
	} else {
		showInfo(i18n('messageMandatory', new Array(i18n('linkType'))));
	}
}

function showDetailOrigin() {
	var originType=dijit.byId('originOriginType').get("value");
	if (originType) {
		var originable=originableArray[originType];
		var canCreate=0;
		if (canCreateArray[originable]=="YES") {
			canCreate=1;
	    }
		showDetail('originOriginId',canCreate, originable);
		
	} else {
		showInfo(i18n('messageMandatory', new Array(i18n('originType'))));
	}
}

function showDetail (comboName, canCreate, objectClass) {
	var contentWidget = dijit.byId("comboDetailResult");
	dojo.byId("canCreateDetail").value=canCreate;
    if (contentWidget) {
      contentWidget.set('content','');
    }
    if (! objectClass) {
    	objectClass=comboName.substring(2);
    }
	dojo.byId('comboName').value=comboName;
	dojo.byId('comboClass').value=objectClass;
	var val=null;
	if (dijit.byId(comboName)) {
	  val=dijit.byId(comboName).get('value');
	} else {
	  val=dojo.byId(comboName).value;	
	}
	if (! val || val=="" || val==" ") {
		cl=objectClass;
		window.frames['comboDetailFrame'].document.body.innerHTML='<i>' + i18n("messagePreview") + '</i>';
		dijit.byId("dialogDetail").show();
		displaySearch(cl);
  } else {
		cl=objectClass;
	    id=val;
		window.frames['comboDetailFrame'].document.body.innerHTML='<i>' + i18n("messagePreview") + '</i>';
		dijit.byId("dialogDetail").show();
		displayDetail(cl,id);
  }
}

function displayDetail(objClass, objId) {
	showWait();
	showField('comboSearchButton');
	hideField('comboSelectButton');
	hideField('comboNewButton');
	hideField('comboSaveButton');
	showField('comboCloseButton');	
  frames['comboDetailFrame'].location.href="print.php?print=true&page=objectDetail.php&objectClass="+objClass+"&objectId="+objId;
}

function selectDetailItem(selectedValue) {
	if (selectedValue) {
		var idFldVal=selectedValue;
	} else {
		var idFld=frames['comboDetailFrame'].dojo.byId('comboDetailId');
		if (! idFld) {
			showError('error : comboDetailId not defined');
			return;
		}
		idFldVal=idFld.value;
		if (! idFldVal) {
			showAlert(i18n('noItemSelected'));
			return;
		}
	}
	var comboName=dojo.byId('comboName').value;
	var combo=dijit.byId(comboName);
	crit=null;
	critVal=null;
	if (comboName=='idActivity' || comboName=='idResource') {
		prj=dijit.byId('idProject');
		if (prj) {
		  crit='idProject';
		  critVal=prj.get("value");
		}		
	}
	if (comboName!='idStatus' && comboName!='idProject') {
	  // TODO : study if such restriction should be applied to idActivity
		if (combo) {
		  refreshList(comboName, crit, critVal, idFldVal);
		} else {
			if (comboName=='dependencyRefIdDep') {
				refreshDependencyList(idFldVal);
				setTimeout("dojo.byId('dependencyRefIdDep').focus()",1000);
				enableWidget('dialogDependencySubmit');
			} else if (comboName=='linkRef2Id') {
				refreshLinkList(idFldVal);
				setTimeout("dojo.byId('linkRef2Id').focus()",1000);
				enableWidget('dialogLinkSubmit');
			} else if (comboName=='originOriginId') {
				refreshOriginList(idFldVal);
				setTimeout("dojo.byId('originOriginId').focus()",1000);
				enableWidget('dialogOriginSubmit');
			}
		}			
	}
	if (combo) {
	  combo.set("value", idFldVal);
	}
		
  hideDetail();
}

function displaySearch(objClass) {
	if (! objClass) {
		// comboName=dojo.byId('comboName').value;
		objClass=dojo.byId('comboClass').value;
	}
	showWait();
	hideField('comboSearchButton');
	showField('comboSelectButton');
	if (dojo.byId("canCreateDetail").value=="1") { 
	  showField('comboNewButton');
	} else {
      hideField('comboNewButton');	
	}
	hideField('comboSaveButton');
	showField('comboCloseButton');	
    top.frames['comboDetailFrame'].location.href="comboSearch.php?objectClass="+objClass+"&mode=search";
    setTimeout(dijit.byId("dialogDetail").show(),10);
}

function newDetailItem() {
	//comboName=dojo.byId('comboName').value;
	var objClass=dojo.byId('comboClass').value;
	showWait();
	showField('comboSearchButton');
	hideField('comboSelectButton');
	hideField('comboNewButton');
	if (dojo.byId("canCreateDetail").value=="1") { 
	  showField('comboSaveButton');
	} else {
      hideField('comboSaveButton');	
	}
	showField('comboCloseButton');
	contentNode=frames['comboDetailFrame'].dojo.byId('body');
	destinationWidth=dojo.style(contentNode, "width");
	page="comboSearch.php";
	page+="?objectClass="+objClass;
	page+="&objectId=0";
	page+="&mode=new";
    page+="&destinationWidth="+destinationWidth;
	top.frames['comboDetailFrame'].location.href=page;
	setTimeout(dijit.byId("dialogDetail").show(),10);
}

function saveDetailItem() {
	var comboName=dojo.byId('comboName').value;
	var formVar = frames['comboDetailFrame'].dijit.byId("objectForm");
	if ( ! formVar) {
		showError(i18n("errorSubmitForm", new Array(page, destination, formName)));
		return;
	}
	// validate form Data
	if(formVar.validate()){
		frames['comboDetailFrame'].dojo.xhrPost({
		      url: "../tool/saveObject.php?comboDetail=true",
		      form: "objectForm",
		      handleAs: "text",
		      load: function(data,args){
				        var contentWidget = dijit.byId("comboDetailResult");
				        if (! contentWidget) {return};
				        contentWidget.set('content',data);
				        checkDestination("comboDetailResult");
				        var lastOperationStatus = top.dojo.byId('lastOperationStatusComboDetail');
				        var lastOperation = top.dojo.byId('lastOperationComboDetail');
				        var lastSaveId=top.dojo.byId('lastSaveIdComboDetail');
				        if (lastOperationStatus.value=="OK") {
				        	selectDetailItem(lastSaveId.value);
				        }
                    }
		});

  } else {
    showAlert(i18n("alertInvalidForm"));
  }
}

function hideDetail() {
	hideField('comboSearchButton');
	hideField('comboSelectButton');
	hideField('comboNewButton');
	hideField('comboSaveButton');
	hideField('comboCloseButton');
	frames['comboDetailFrame'].location.href="preparePreview.php";
	dijit.byId("dialogDetail").hide();
}

//=============================================================================
//= Notes
//=============================================================================

/**
 * Display a add note Box
 * 
 */
function addNote () {
	if (dijit.byId("noteToolTip")) {
		dijit.byId("noteToolTip").destroy();
		dijit.byId("noteNote").set("class","");
	}
	dojo.byId("noteId").value="";
	dojo.byId("noteRefType").value=dojo.byId("objectClass").value;
	dojo.byId("noteRefId").value=dojo.byId("objectId").value;
	dijit.byId("noteNote").reset();
	dijit.byId("dialogNote").set('title',i18n("dialogNote"));
	dijit.byId("dialogNote").show();
}

/**
 * Display a edit note Box
 * 
 */
function editNote (noteId) {
	if (dijit.byId("noteToolTip")) {
		dijit.byId("noteToolTip").destroy();
		dijit.byId("noteNote").set("class","");
	}
	dojo.byId("noteId").value=noteId;
	dojo.byId("noteRefType").value=dojo.byId("objectClass").value;
	dojo.byId("noteRefId").value=dojo.byId("objectId").value;
	dijit.byId("noteNote").set("value",dojo.byId("note_"+noteId).value);
	dijit.byId("dialogNote").set('title',i18n("dialogNote") + " #" + noteId);
	dijit.byId("dialogNote").show();
}

/**
 * save a note (after addNote or editNote)
 * 
 */
function saveNote() {
	if (dijit.byId("noteNote").getValue()=='') {
		dijit.byId("noteNote").set("class","dijitError");
		//dijit.byId("noteNote").blur();
		var msg=i18n('messageMandatory', new Array(i18n('Note')));
		new dijit.Tooltip({
			id : "noteToolTip",
      connectId: ["noteNote"],
      label: msg,
      showDelay: 0
    });
		dijit.byId("noteNote").focus();
	} else {
		loadContent("../tool/saveNote.php", "resultDiv", "noteForm", true, 'note');
		dijit.byId('dialogNote').hide();
	}
}

/**
 * Display a delete note Box
 * 
 */
function removeNote (noteId) {
	dojo.byId("noteId").value=noteId;
	dojo.byId("noteRefType").value=dojo.byId("objectClass").value;
	dojo.byId("noteRefId").value=dojo.byId("objectId").value;
	actionOK=function() {loadContent("../tool/removeNote.php", "resultDiv", "noteForm", true, 'note');};
	msg=i18n('confirmDelete',new Array(i18n('Note'), noteId));
	showConfirm (msg, actionOK);
}


//=============================================================================
//= Attachements
//=============================================================================

/**
 * Display an add attachement Box
 * 
 */
function addAttachement () {
	dojo.byId("attachementId").value="";
	dojo.byId("attachementRefType").value=dojo.byId("objectClass").value;
	dojo.byId("attachementRefId").value=dojo.byId("objectId").value;
	if (dijit.byId("attachementFile")) {
		dijit.byId("attachementFile").reset();
	}
	dijit.byId("attachementDescription").reset();
	dijit.byId("dialogAttachement").set('title',i18n("dialogAttachement"));
	dijit.byId("dialogAttachement").show();
}

/**
 * save an Attachement
 * 
 */
function saveAttachement() {
	showWait();
	dijit.byId('dialogAttachement').hide();
	return true;
}

/**
 * Acknoledge the attachment save
 * @return void
 */
function saveAttachementAck() {
	resultFrame=document.getElementById("resultPost");
	resultText=resultPost.document.body.innerHTML;
	dojo.byId('resultAck').value=resultText;
	loadContent("../tool/ack.php", "resultDiv", "attachementForm", true, 'attachement');
}

/**
 * Display a delete Attachement Box
 * 
 */
function removeAttachement (attachementId) {
	dojo.byId("attachementId").value=attachementId;
	dojo.byId("attachementRefType").value=dojo.byId("objectClass").value;
	dojo.byId("attachementRefId").value=dojo.byId("objectId").value;
	actionOK=function() {loadContent("../tool/removeAttachement.php", "resultDiv", "attachementForm", true, 'attachement');};
	msg=i18n('confirmDelete',new Array(i18n('Attachement'), attachementId));
	showConfirm (msg, actionOK);
}

//=============================================================================
//= Links
//=============================================================================

/**
 * Display a add link Box
 * 
 */
var noRefreshLink=false;
function addLink (classLink) {
	noRefreshLink=true;
	if (formChangeInProgress) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	var objectClass=dojo.byId("objectClass").value;
	var objectId=dojo.byId("objectId").value;
    var message=i18n("dialogLink");
	dojo.byId("linkId").value="";
	dojo.byId("linkRef1Type").value=objectClass;
	dojo.byId("linkRef1Id").value=objectId;
	if (classLink) {
	    dojo.byId("linkFixedClass").value=classLink;
	  	message = i18n("dialogLinkRestricted", new Array(i18n(objectClass), objectId, i18n(classLink)));
	  	dijit.byId("linkRef2Type").setDisplayedValue(i18n(classLink));
	  	lockWidget("linkRef2Type");
	  	//var url="../tool/dynamicListLink.php" 
	  	//	+ "?linkRef2Type="+dojo.byId("linkRef2Type").value
	  	//  + "&linkRef1Type="+objectClass
	  	//	+ "&linkRef1Id="+objectId;
	  	//loadContent(url, "dialogLinkList", null, false);
	  	noRefreshLink=false;
	  	refreshLinkList();
	} else {
	  	dojo.byId("linkFixedClass").value="";
	  	message = i18n("dialogLinkExtended", new Array(i18n(objectClass), objectId.value));
	  	unlockWidget("linkRef2Type");
	  	noRefreshLink=false;
	  	refreshLinkList();
    }

	//dojo.byId("linkRef2Id").value='';
	dijit.byId("dialogLink").set('title', message);
	dijit.byId("dialogLink").show();
	disableWidget('dialogLinkSubmit');
}

/**
 * Refresh the link list (after update)
 */
function refreshLinkList(selected) {
	if (noRefreshLink) return;
	disableWidget('dialogLinkSubmit');
	var url='../tool/dynamicListLink.php';
	if (selected) {
	  url+='?selected='+selected;	
	}
	loadContent(url, 'dialogLinkList', 'linkForm', false);
}

/**
 * save a link (after addLink)
 * 
 */
function saveLink() {
  loadContent("../tool/saveLink.php", "resultDiv", "linkForm", true,'link');
	dijit.byId('dialogLink').hide();
}

/**
 * Display a delete Link Box
 * 
 */
function removeLink (linkId, refType, refId) {
	if (formChangeInProgress) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}	
	dojo.byId("linkId").value=linkId;
	dojo.byId("linkRef1Type").value=dojo.byId("objectClass").value;
	dojo.byId("linkRef1Id").value=dojo.byId("objectId").value;
	dojo.byId("linkRef2Type").value=refType;
	dojo.byId("linkRef2Id").value=refId;
	actionOK=function() {loadContent("../tool/removeLink.php", "resultDiv", "linkForm", true,'link');};
	msg=i18n('confirmDeleteLink',new Array(i18n(refType),refId));
	showConfirm (msg, actionOK);
}

//=============================================================================
//= Origin
//=============================================================================

/**
* Display a add origin Box
* 
*/
function addOrigin () {
	if (formChangeInProgress) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	var objectClass=dojo.byId("objectClass").value;
	var objectId=dojo.byId("objectId").value;
	dijit.byId("originOriginType").reset();
	refreshOriginList();
	dojo.byId("originId").value="";
	dojo.byId("originRefType").value=objectClass;
	dojo.byId("originRefId").value=objectId;
	dijit.byId("dialogOrigin").show();
	disableWidget('dialogOriginSubmit');
}

/**
* Refresh the origin list (after update)
*/
function refreshOriginList(selected) {
	disableWidget('dialogOriginSubmit');
	var url='../tool/dynamicListOrigin.php';
	if (selected) {
	  url+='?selected='+selected;	
    }
	loadContent(url, 'dialogOriginList', 'originForm', false);
}

/**
* save a link (after addLink)
* 
*/
function saveOrigin() {
	loadContent("../tool/saveOrigin.php", "resultDiv", "originForm", true,'origin');
	dijit.byId('dialogOrigin').hide();
}

/**
* Display a delete Link Box
* 
*/
function removeOrigin (id, origType, origId) {
	if (formChangeInProgress) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}	
	dojo.byId("originId").value=id;
	dojo.byId("originRefType").value=dojo.byId("objectClass").value;
	dojo.byId("originRefId").value=dojo.byId("objectId").value;
	dojo.byId("originOriginType").value=origType;
	dojo.byId("originOriginId").value=origId;
	actionOK=function() {loadContent("../tool/removeOrigin.php", "resultDiv", "originForm", true,'origin');};
	msg=i18n('confirmDeleteOrigin',new Array(i18n(origType),origId));
	showConfirm (msg, actionOK);
}

//=============================================================================
//= Assignments
//=============================================================================

/**
 * Display a add Assignment Box
 * 
 */
function addAssignment () {
	if (formChangeInProgress) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}	
	var prj=dijit.byId('idProject').get('value');
	dijit.byId('assignmentIdResource').store = new dojo.data.ItemFileReadStore({
		       url: '../tool/jsonList.php?listType=listResourceProject&idProject='+prj });
	dijit.byId('assignmentIdResource').store.fetch();
	dojo.byId("assignmentId").value="";
	dojo.byId("assignmentRefType").value=dojo.byId("objectClass").value;
	dojo.byId("assignmentRefId").value=dojo.byId("objectId").value;
	dijit.byId("assignmentIdResource").reset();
	dijit.byId("assignmentIdRole").reset();
	dijit.byId("assignmentDailyCost").reset();
	dojo.byId("assignmentRate").value='100';
	dojo.byId("assignmentAssignedWork").value='0';
	dojo.byId("assignmentAssignedWorkInit").value='0';
	dojo.byId("assignmentRealWork").value='0';
	dojo.byId("assignmentLeftWork").value='0';
	dojo.byId("assignmentLeftWorkInit").value='0';
	dojo.byId("assignmentPlannedWork").value='0';
	dojo.byId("assignmentComment").value='';
	dijit.byId("dialogAssignment").set('title',i18n("dialogAssignment"));
	dijit.byId("assignmentIdResource").set('disabled',false);
	dijit.byId("assignmentIdRole").set('disabled',false);
	dijit.byId("dialogAssignment").show();
}

/**
 * Display a edit Assignment Box
 * 
 */
var editAssignmentLoading=false;
function editAssignment (assignmentId, idResource, idRole, cost, rate, assignedWork, realWork, leftWork, comment) {
	if (formChangeInProgress) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	editAssignmentLoading=true;
	var prj=dijit.byId('idProject').get('value');
	
	dijit.byId('assignmentIdResource').store = new dojo.data.ItemFileReadStore({
		       url: '../tool/jsonList.php?listType=listResourceProject&idProject='+prj
		       +'&selected=' + idResource});
	dijit.byId('assignmentIdResource').store.fetch();
	dijit.byId("assignmentIdResource").set("value",idResource);
	
	//dijit.byId('assignmentIdRole').store = new dojo.data.ItemFileReadStore({
  //  url: '../tool/jsonList.php?listType=listRoleResource&idResource='+idRole});
	//dijit.byId('assignmentIdRole').store.fetch();
	dijit.byId("assignmentIdRole").set("value",idRole);
	
	dojo.byId("assignmentId").value=assignmentId;
	dojo.byId("assignmentRefType").value=dojo.byId("objectClass").value;
	dojo.byId("assignmentRefId").value=dojo.byId("objectId").value;
	dijit.byId("assignmentDailyCost").set('value',cost);
	dojo.byId("assignmentRate").value=rate;
	dojo.byId("assignmentAssignedWork").value=assignedWork;
	dojo.byId("assignmentAssignedWorkInit").value=dojo.number.parse(assignedWork);
	dojo.byId("assignmentRealWork").value=realWork;
	dojo.byId("assignmentLeftWork").value=leftWork;
	dojo.byId("assignmentComment").value=comment;
	dojo.byId("assignmentLeftWorkInit").value=dojo.number.parse(leftWork);
	assignmentUpdatePlannedWork('assignment');
	dijit.byId("dialogAssignment").set('title',i18n("dialogAssignment") + " #" + assignmentId);
	dijit.byId("dialogAssignment").show();
	if (dojo.number.parse(realWork)==0) {
		dijit.byId("assignmentIdResource").set('readOnly',false);
		dijit.byId("assignmentIdRole").set('readOnly',false);
	} else {
		dijit.byId("assignmentIdResource").set('readOnly',true);
		dijit.byId("assignmentIdRole").set('readOnly',true);
	}
	setTimeout("editAssignmentLoading=false",1000);
}

/**
 * Update the left work on assignment update
 * @param prefix
 * @return
 */
function assignmentUpdateLeftWork(prefix) {
	var initAssigned = dojo.byId(prefix + "AssignedWorkInit"); 
  var initLeft =  dojo.byId(prefix + "LeftWorkInit");
  var assigned =  dojo.byId(prefix + "AssignedWork"); 
  var newAssigned=dojo.number.parse(assigned.value);
  if (newAssigned==null || isNaN(newAssigned)) {
  	newAssigned=0;
  	assigned.value=dojo.number.format(newAssigned);
	}  
  var left = dojo.byId(prefix + "LeftWork");
  var real = dojo.byId(prefix + "RealWork"); 
  var planned = dojo.byId(prefix + "PlannedWork");
	diff=dojo.number.parse(assigned.value)-initAssigned.value;
	newLeft=parseFloat(initLeft.value) + diff;
	if (newLeft<0 || isNaN(newLeft)) { newLeft=0;}
	left.value=dojo.number.format(newLeft);
	assignmentUpdatePlannedWork(prefix);
}

/**
 * Update the planned work on assignment update
 * @param prefix
 * @return
 */
function assignmentUpdatePlannedWork(prefix) {
  var left = dojo.byId(prefix + "LeftWork");
  var newLeft=dojo.number.parse(left.value);
  if (newLeft==null || isNaN(newLeft)) {
  	newLeft=0;
  	left.value=dojo.number.format(newLeft);
	}
  var real = dojo.byId(prefix + "RealWork"); 
  var planned = dojo.byId(prefix + "PlannedWork");
	newPlanned=dojo.number.parse(real.value)+dojo.number.parse(left.value);
	planned.value=dojo.number.format(newPlanned);
}

/**
 * save an Assignment (after addAssignment or editAssignment)
 * 
 */
function saveAssignment() {
	/*if (! dijit.byId('assignmentIdResource').get('value')) {
		showAlert(i18n('messageMandatory',new Array(i18n('colIdResource'))));
		return;
	}
	if (! dijit.byId('assignmentIdResource').get('value')) {
		showAlert(i18n('messageMandatory',new Array(i18n('colIdResource'))));
		return;
	}	*/
	var formVar = dijit.byId('assignmentForm');
  if(formVar.validate()){		
	  dijit.byId("assignmentPlannedWork").focus();
	  dijit.byId("assignmentLeftWork").focus();
	  loadContent("../tool/saveAssignment.php", "resultDiv", "assignmentForm", true, 'assignment');
	  dijit.byId('dialogAssignment').hide();
  } else {
    showAlert(i18n("alertInvalidForm"));
  }
}
 
/**
 * Display a delete Assignment Box
 * 
 */
function removeAssignment (assignmentId, realWork, resource) {
	if (formChangeInProgress) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	if (parseFloat(realWork)) {
		msg=i18n('msgUnableToDeleteRealWork');
		showAlert (msg);
		return;
	}
	dojo.byId("assignmentId").value=assignmentId;
	dojo.byId("assignmentRefType").value=dojo.byId("objectClass").value;
	dojo.byId("assignmentRefId").value=dojo.byId("objectId").value;
	actionOK=function() {loadContent("../tool/removeAssignment.php", "resultDiv", "assignmentForm", true, 'assignment');};
	msg=i18n('confirmDeleteAssignment',new Array(resource));
	showConfirm (msg, actionOK);
}

/* function assignmentChangeResource() {
	return;
	if (editAssignmentLoading) return;
	var idR=dijit.byId("assignmentIdResource").get("value");
	if (! idR) {
		dijit.byId('assignmentIdResource').store = new dojo.data.ItemFileReadStore({
	    url: '../tool/jsonList.php?listType=empty'});
		return;
	}
	dijit.byId('assignmentIdRole').store = new dojo.data.ItemFileReadStore({
    url: '../tool/jsonList.php?listType=listRoleResource&idResource='+idR});
	dijit.byId('assignmentIdRole').store.fetch();
} */
function assignmentChangeResource() {
	if (editAssignmentLoading) return;
	var idResource=dijit.byId("assignmentIdResource").get("value");
	if (! idResource) return;
	dijit.byId('assignmentDailyCost').reset();
	dojo.xhrGet({
		url: '../tool/getSingleData.php?dataType=resourceRole&idResource=' + idResource,
		handleAs: "text",
		load: function (data) {dijit.byId('assignmentIdRole').set('value',data);}
	});
}

function assignmentChangeRole() {
	if (editAssignmentLoading) return;
	var idResource=dijit.byId("assignmentIdResource").get("value");
	var idRole=dijit.byId("assignmentIdRole").get("value");
	if (! idResource || ! idRole ) return;
	dojo.xhrGet({
		url: '../tool/getSingleData.php?dataType=resourceCost&idResource=' + idResource + '&idRole=' + idRole,
		handleAs: "text",
		load: function (data) {
		  // #303
		  //dijit.byId('assignmentDailyCost').set('value',data);
		  dijit.byId('assignmentDailyCost').set('value',dojo.number.format(data));
		}
	});
}

//=============================================================================
//= ExpenseDetail
//=============================================================================

/**
* Display a add Assignment Box
* 
*/
function addExpenseDetail () {
	if (formChangeInProgress) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}	
	dojo.byId("expenseDetailId").value="";
	dojo.byId("idExpense").value=dojo.byId("objectId").value;
	dijit.byId("expenseDetailName").reset();
	dijit.byId("expenseDetailDate").reset();
	dijit.byId("expenseDetailType").reset();
	dojo.byId("expenseDetailDiv").innerHtml="";
	dijit.byId("expenseDetailAmount").reset();
	//dijit.byId("dialogExpenseDetail").set('title',i18n("dialogExpenseDetail"));
	dijit.byId("dialogExpenseDetail").show();
}

/**
* Display a edit Assignment Box
* 
*/
var expenseDetailLoad=false;
function editExpenseDetail (id, idExpense, type, expenseDate, amount) {
	expenseDetailLoad=true;
	if (formChangeInProgress) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	dojo.byId("expenseDetailId").value=id;
	dojo.byId("idExpense").value=idExpense;	
	dijit.byId("expenseDetailName").set("value",dojo.byId('expenseDetail_'+id).value);
  	dijit.byId("expenseDetailDate").set("value",getDate(expenseDate));
	dijit.byId("expenseDetailAmount").set("value",dojo.number.parse(amount));
	dijit.byId("dialogExpenseDetail").set('title',i18n("dialogExpenseDetail") + " #" + id);
	dijit.byId("expenseDetailType").set("value",type);
	expenseDetailLoad=false;
	expenseDetailTypeChange(id);
	expenseDetailLoad=true;
	setTimeout('expenseDetailLoad=false;',500);
	dijit.byId("dialogExpenseDetail").show();
}

/**
* save an Assignment (after addAssignment or editAssignment)
* 
*/
function saveExpenseDetail() {
	expenseDetailRecalculate();
	if (! dijit.byId('expenseDetailName').get('value')) {
		showAlert(i18n('messageMandatory',new Array(i18n('colName'))));
		return;
	}
	if (! dijit.byId('expenseDetailDate').get('value')) {
		showAlert(i18n('messageMandatory',new Array(i18n('colDate'))));
		return;
	}
	if (! dijit.byId('expenseDetailType').get('value')) {
		showAlert(i18n('messageMandatory',new Array(i18n('colType'))));
		return;
	}
	if (! dijit.byId('expenseDetailAmount').get('value')) {
		showAlert(i18n('messageMandatory',new Array(i18n('colAmount'))));
		return;
	}
	var formVar = dijit.byId('expenseDetailForm');
    if(formVar.validate()){		
	  dijit.byId("expenseDetailName").focus();
	  dijit.byId("expenseDetailAmount").focus();
	  loadContent("../tool/saveExpenseDetail.php", "resultDiv", "expenseDetailForm", true, 'expenseDetail');
	  dijit.byId('dialogExpenseDetail').hide();
    } else {
    	showAlert(i18n("alertInvalidForm"));
    }
}

/**
* Display a delete Assignment Box
* 
*/
function removeExpenseDetail (expenseDetailId) {
	if (formChangeInProgress) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	dojo.byId("expenseDetailId").value=expenseDetailId;
	actionOK=function() {loadContent("../tool/removeExpenseDetail.php", "resultDiv", "expenseDetailForm", true, 'expenseDetail');};
	msg=i18n('confirmDeleteExpenseDetail',new Array(dojo.byId('expenseDetail_'+expenseDetailId).value));
	showConfirm (msg, actionOK);
}

function expenseDetailTypeChange(expenseDetailId) {
	if (expenseDetailLoad) return;
	var idType=dijit.byId("expenseDetailType").get("value");
	var url='../tool/expenseDetailDiv.php?idType='+idType;
	if (expenseDetailId) {
	  url+='&expenseDetailId='+expenseDetailId;
	}
	loadContent(url, 'expenseDetailDiv', null, false);
}

function expenseDetailRecalculate() {
	val=false;
	if (dijit.byId('expenseDetailValue01')) {
		val01=dijit.byId('expenseDetailValue01').get("value");
	} else {
		val01=dojo.byId('expenseDetailValue01').value;
	}
	if (dijit.byId('expenseDetailValue02')) {
		val02=dijit.byId('expenseDetailValue02').get("value");
	} else {
		val02=dojo.byId('expenseDetailValue02').value;
	}
	if (dijit.byId('expenseDetailValue03')) {
		val03=dijit.byId('expenseDetailValue03').get("value");
	} else {
		val03=dojo.byId('expenseDetailValue03').value;
	}	
	total=1;
	if (dojo.byId('expenseDetailUnit01').value) {
		total=total*val01;
		val=true;
	}
	if (dojo.byId('expenseDetailUnit02').value) {
		total=total*val02;
		val=true;
	}
	if (dojo.byId('expenseDetailUnit03').value) {
		total=total*val03;
		val=true;
	}
	if (val) {
	  dijit.byId("expenseDetailAmount").set('value',total);
	  lockWidget("expenseDetailAmount");
	} else {
      unlockWidget("expenseDetailAmount")
	}
}

//=============================================================================
//= Dependency
//=============================================================================

/**
* Display a add Dependency Box
* 
*/
function addDependency (depType) {
if (formChangeInProgress) {
	showAlert(i18n('alertOngoingChange'));
	return;
}
var objectClass=dojo.byId("objectClass").value;
var objectId=dojo.byId("objectId").value;
var message=i18n("dialogDependency");
if (depType) {
	dojo.byId("dependencyType").value=depType;
	message = i18n("dialogDependencyRestricted", new Array(i18n(objectClass), objectId, i18n(depType)));
} else {
	dojo.byId("dependencyType").value=null;
	message = i18n("dialogDependencyExtended", new Array(i18n(objectClass), objectId.value));
}
dijit.byId("dependencyRefTypeDep").reset();
refreshDependencyList();
//var url="../tool/dynamicListDependency.php" 
//	+ "?dependencyType="+depType
//  + "&dependencyRefType="+objectClass
//	+ "&dependencyRefId="+objectId
//	+ "&dependencyRefTypeDep="+dojo.byId("dependencyRefTypeDep").value;
//loadContent(url, "dialogDependencyList", null, false);
dojo.byId("dependencyId").value="";
dojo.byId("dependencyRefType").value=objectClass;
dojo.byId("dependencyRefId").value=objectId;
dijit.byId("dialogDependency").set('title', message);
dijit.byId("dialogDependency").show();
disableWidget('dialogDependencySubmit');
}

/**
* Refresh the Dependency list (after update)
*/
function refreshDependencyList(selected) {
disableWidget('dialogDependencySubmit');
var url='../tool/dynamicListDependency.php';
if (selected) {
	url+='?selected='+selected;
}
loadContent(url, 'dialogDependencyList', 'dependencyForm', false);
}

/**
* save a Dependency (after addLink)
* 
*/
function saveDependency() {
if (dojo.byId("dependencyRefIdDep").value=="") return;
loadContent("../tool/saveDependency.php", "resultDiv", "dependencyForm", true,'dependency');
dijit.byId('dialogDependency').hide();
}

/**
* Display a delete Dependency Box
* 
*/
function removeDependency (dependencyId, refType, refId) {
//alert("Not implemented");
//return;
if (formChangeInProgress) {
	showAlert(i18n('alertOngoingChange'));
	return;
}	
dojo.byId("dependencyId").value=dependencyId;
actionOK=function() {loadContent("../tool/removeDependency.php", "resultDiv", "dependencyForm", true,'dependency');};
msg=i18n('confirmDeleteLink',new Array(i18n(refType),refId));
showConfirm (msg, actionOK);
}

//=============================================================================
//= Import
//=============================================================================

/**
 * Display an import Data Box
 * (Not used, for an eventual improvement)
 * 
 */
function importData() {
	var controls=controlImportData();
	if (controls) {	showWait();}
	return controls;
}

function showHelpImportData() {
	var controls=controlImportData();
	if (controls) {	
		showWait();
		var url='../tool/importHelp.php?elementType='+dijit.byId('elementType').get('value');
		url+='&fileType='+dijit.byId('fileType').get('value');
		frames['resultImportData'].location.href=url;
  }
}

function controlImportData() {
	var elementType=dijit.byId('elementType').get('value');
	if (! elementType ) {
		showAlert(i18n('messageMandatory',new Array(i18n('colImportElementType'))));
		return false;
	}
	var fileType=dijit.byId('fileType').get('value');
	if (! fileType ) {
		showAlert(i18n('messageMandatory',new Array(i18n('colImportFileType'))));
		return false;
	}
	return true;
}
//=============================================================================
//= Plan
//=============================================================================

/**
 * Display a planning Box
 * 
 */
function showPlanParam (selectedProject) {
	if (formChangeInProgress) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}	
	dijit.byId("dialogPlan").show();
}

/**
 * Run planning
 * 
 */
function plan() {
	loadContent("../tool/plan.php", "planResultDiv", "dialogPlanForm", true,null);
	dijit.byId("dialogPlan").hide();
}

//=============================================================================
//= Filter
//=============================================================================

/**
 * Display a Filter Box
 * 
 */
function showFilterDialog () {
	if (formChangeInProgress) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	dojo.style(dijit.byId('idFilterOperator').domNode, {visibility:'hidden'});
	dojo.style(dijit.byId('filterValue').domNode, {display:'none'});
	dojo.style(dijit.byId('filterValueList').domNode, {display:'none'});
	dojo.style(dijit.byId('filterValueCheckbox').domNode, {display:'none'});
	dojo.style(dijit.byId('filterValueDate').domNode, {display:'none'});
	dojo.style(dijit.byId('filterSortValueList').domNode, {display:'none'});
	dijit.byId('idFilterAttribute').reset();
	dojo.byId('filterObjectClass').value= dojo.byId('objectClass').value;
	filterType="";
	dojo.xhrPost({url: "../tool/backupFilter.php?filterObjectClass=" + dojo.byId('filterObjectClass').value});
	loadContent("../tool/displayFilterClause.php", "listFilterClauses", "dialogFilterForm", false);
	loadContent("../tool/displayFilterList.php", "listStoredFilters", "dialogFilterForm", false);
	dijit.byId('idFilterAttribute').store = new dojo.data.ItemFileReadStore({url: '../tool/jsonList.php?listType=object&objectClass=' + dojo.byId("objectClass").value});
	dijit.byId("dialogFilter").show();
}

/**
 * Select attribute : refresh depedant lists box
 * 
 */
function filterSelectAtribute(value) {
	if (value) {
	  dijit.byId('idFilterAttribute').store.fetchItemByIdentity({
	    identity : value, 
	    onItem : function(item) { 
	  	  var dataType = dijit.byId('idFilterAttribute').store.getValue(item, "dataType", "inconnu");
	  	  dijit.byId('idFilterOperator').store = new dojo.data.ItemFileReadStore({url: '../tool/jsonList.php?listType=operator&dataType=' + dataType});	  		
	  	  dijit.byId('idFilterOperator').store.fetch({
	  	  	query : {id : "*"},
	  	  	count : 1,
	  	  	onItem : function(item) { 
	  	  		dijit.byId('idFilterOperator').set("value",dijit.byId('idFilterOperator').store.getValue(item,"id",""));
	  	  	},  
          onError : function(err) { 
            console.info(err.message) ;  
          }
	  	  });	      
	  	  dojo.style(dijit.byId('idFilterOperator').domNode, {visibility:'visible'});
	  		dojo.byId('filterDataType').value=dataType;
	  		if (dataType=="bool") {
	  			filterType="bool";
	  			dojo.style(dijit.byId('filterValue').domNode, {display:'none'});
	  			dojo.style(dijit.byId('filterValueList').domNode, {display:'none'});
	  			dojo.style(dijit.byId('filterValueCheckbox').domNode, {display:'block'});
	  			dijit.byId('filterValueCheckbox').set('checked','');
	  			dojo.style(dijit.byId('filterValueDate').domNode, {display:'none'});
	  		} else if (dataType=="list") {
	  			filterType="list";
	  			var tmpStore = new dojo.data.ItemFileReadStore({url: '../tool/jsonList.php?listType=list&dataType=' + value});
	  			var mySelect=dojo.byId("filterValueList");
	  			mySelect.options.length=0;
	  			var nbVal=0;
	  			tmpStore.fetch({
		  	  	query : {id : "*"},
		  	  	onItem : function(item) {
		  	  		mySelect.options[mySelect.length] = new Option(tmpStore.getValue(item,"name",""),tmpStore.getValue(item,"id",""));
		  	  		nbVal++;
		  	  	},  
	          onError : function(err) { 
	            console.info(err.message) ;  
	          }
		  	  });
	  			mySelect.size=(nbVal>0)?10:nbVal;
	  			dojo.style(dijit.byId('filterValue').domNode, {display:'none'});
	  			dojo.style(dijit.byId('filterValueList').domNode, {display:'block'});
	  			dijit.byId('filterValueList').reset();
	  			dojo.style(dijit.byId('filterValueCheckbox').domNode, {display:'none'});
	  			dojo.style(dijit.byId('filterValueDate').domNode, {display:'none'});
	  		} else if (dataType=="date") {
	  			filterType="date";
	  			dojo.style(dijit.byId('filterValue').domNode, {display:'none'});
	  			dojo.style(dijit.byId('filterValueList').domNode, {display:'none'});
	  			dojo.style(dijit.byId('filterValueCheckbox').domNode, {display:'none'});
	  			dojo.style(dijit.byId('filterValueDate').domNode, {display:'block'});
	  			dijit.byId('filterValueDate').reset();
	  		} else {
	  	  	filterType="text";
	  			dojo.style(dijit.byId('filterValue').domNode, {display:'block'});
	  			dijit.byId('filterValue').reset();
	  			dojo.style(dijit.byId('filterValueList').domNode, {display:'none'});
	  			dojo.style(dijit.byId('filterValueCheckbox').domNode, {display:'none'});
	  			dojo.style(dijit.byId('filterValueDate').domNode, {display:'none'});
	  		}
	  	},
	    onError : function(err) {
	  		dojo.style(dijit.byId('idFilterOperator').domNode, {visibility:'hidden'});
  			dojo.style(dijit.byId('filterValue').domNode, {display:'none'});
  			dojo.style(dijit.byId('filterValueList').domNode, {display:'none'});
  			dojo.style(dijit.byId('filterValueCheckbox').domNode, {display:'none'});
  			dojo.style(dijit.byId('filterValueDate').domNode, {display:'none'});
	  		//hideWait();
	    }
    }) ;
	  dijit.byId('filterValue').reset();
		dijit.byId('filterValueList').reset();
		dijit.byId('filterValueCheckbox').reset();
		dijit.byId('filterValueDate').reset();
	} else {
		dojo.style(dijit.byId('idFilterOperator').domNode, {visibility:'hidden'});
		dojo.style(dijit.byId('filterValue').domNode, {display:'none'});
		dojo.style(dijit.byId('filterValueList').domNode, {display:'none'});
		dojo.style(dijit.byId('filterValueCheckbox').domNode, {display:'none'});
		dojo.style(dijit.byId('filterValueDate').domNode, {display:'none'});
	}
}

function filterSelectOperator(operator) {
	if (operator=="SORT") {
		filterType="SORT";
		dojo.style(dijit.byId('filterValue').domNode, {display:'none'});
		dojo.style(dijit.byId('filterValueList').domNode, {display:'none'});
		dojo.style(dijit.byId('filterValueCheckbox').domNode, {display:'none'});
		dojo.style(dijit.byId('filterValueDate').domNode, {display:'none'});
		dojo.style(dijit.byId('filterSortValueList').domNode, {display:'block'});
	} else {
		dataType=dojo.byId('filterDataType').value;
		dojo.style(dijit.byId('filterSortValueList').domNode, {display:'none'});
		if (dataType=="bool") {
			filterType="bool";
			dojo.style(dijit.byId('filterValueCheckbox').domNode, {display:'block'});
		} else if (dataType=="list") {
			filterType="list";
			dojo.style(dijit.byId('filterValueList').domNode, {display:'block'});
		} else if (dataType=="date") {
			filterType="date";
			dojo.style(dijit.byId('filterValueDate').domNode, {display:'block'});
		} else {
	  	filterType="text";
			dojo.style(dijit.byId('filterValue').domNode, {display:'block'});
		}
	}
}

/**
 * Save filter clause
 * 
 */
function addfilterClause() {
	if (dijit.byId('filterNameDisplay')) {
		dojo.byId('filterName').value=dijit.byId('filterNameDisplay').get('value');
	}
	if (filterType=="") { 
		showAlert(i18n('attributeNotSelected')); 
		exit;
	}
	if (filterType=="list" && dijit.byId('filterValueList').get('value')=='') {
		showAlert(i18n('valueNotSelected')); 
		exit;
	}
	if (filterType=="date" && ! dijit.byId('filterValueDate').get('value')) {
		showAlert(i18n('valueNotSelected')); 
		exit;
	}		
	if (filterType=="text" && ! dijit.byId('filterValue').get('value')) {
		showAlert(i18n('valueNotSelected')); 
		exit;
	}		
	// Add controls on operator and value
	loadContent("../tool/addFilterClause.php", "listFilterClauses", "dialogFilterForm", false);
}

/**
 * Remove a filter clause
 * 
 */
function removefilterClause(id) {
	if (dijit.byId('filterNameDisplay')) {
		dojo.byId('filterName').value=dijit.byId('filterNameDisplay').get('value');
	}
	// Add controls on operator and value
	dojo.byId("filterClauseId").value=id;
	loadContent("../tool/removeFilterClause.php", "listFilterClauses", "dialogFilterForm", false);
}

/**
 * Action on OK for filter
 * 
 */
function selectFilter() {
	if (dijit.byId('filterNameDisplay')) {
		dojo.byId('filterName').value=dijit.byId('filterNameDisplay').get('value');
	}
	dojo.xhrPost({url: "../tool/backupFilter.php?valid=true",
		form: dojo.byId('dialogFilterForm')
	});
	if (dojo.byId("nbFilterCirteria").value>0) {
		dijit.byId("listFilterFilter").set("iconClass","iconActiveFilter16");
	} else {
		dijit.byId("listFilterFilter").set("iconClass","iconFilter16");
	}
	refreshJsonList(dojo.byId('objectClass').value, dojo.byId('listShowIdle').checked);
	dijit.byId("dialogFilter").hide();
}

/**
 * Action on Cancel for filter
 * 
 */
function cancelFilter() {
	dojo.xhrPost({url: "../tool/backupFilter.php?cancel=true",
		form: dojo.byId('dialogFilterForm')
	});
		dijit.byId('dialogFilter').hide();
}

/**
 * Action on Clear for filter
 * 
 */
function clearFilter() {
	if (dijit.byId('filterNameDisplay')) {
		dijit.byId('filterNameDisplay').reset();
	}
	dojo.byId('filterName').value="";
	removefilterClause('all');	
	//setTimeout("selectFilter();dijit.byId('listFilterFilter').set('iconClass','iconFilter16');",100);
	dijit.byId('listFilterFilter').set('iconClass','iconFilter16');
}

/**
 * Action on Default for filter
 * 
 */
function defaultFilter() {
	if (dijit.byId('filterNameDisplay')) {
		//if (dijit.byId('filterNameDisplay').get('value')=="") {
		//	showAlert(i18n("messageMandatory", new Array(i18n("filterName")) ));
		//	exit;
		//}
		dojo.byId('filterName').value=dijit.byId('filterNameDisplay').get('value');
	}
	loadContent("../tool/defaultFilter.php", "listStoredFilters", "dialogFilterForm", false);
}

/**
 * Save a filter as a stored filter
 * 
 */
function saveFilter() {
	if (dijit.byId('filterNameDisplay')) {
		if (dijit.byId('filterNameDisplay').get('value')=="") {
			showAlert(i18n("messageMandatory", new Array(i18n("filterName")) ));
			exit;
		}
		dojo.byId('filterName').value=dijit.byId('filterNameDisplay').get('value');
	}
	loadContent("../tool/saveFilter.php", "listStoredFilters", "dialogFilterForm", false);
}

/**
 * Select a stored filter in the list and fetch criteria
 * 
 */
function selectStoredFilter(idFilter) {
	loadContent("../tool/selectStoredFilter.php?idFilter="+idFilter, "listFilterClauses", "dialogFilterForm", false);
}

/**
 * Removes a stored filter from the list
 * 
 */
function removeStoredFilter(idFilter, nameFilter) {
  var action=function() {
  	loadContent("../tool/removeFilter.php?idFilter="+idFilter, "listStoredFilters", "dialogFilterForm", false);;
  };
  showConfirm(i18n("confirmRemoveFilter",new Array(nameFilter)),action);
}

//=============================================================================
//= Reports
//=============================================================================

function reportSelectCategory(idCateg) {
	loadContent("../view/reportsParameters.php?idReport=", "reportParametersDiv", null, false);
	var tmpStore = new dojo.data.ItemFileReadStore({url: '../tool/jsonList.php?required=true&listType=list&dataType=idReport&critField=idReportCategory&critValue='+idCateg});
	var mySelect=dojo.byId("reportsList");
	//dijit.byId("reportsList").reset();
	mySelect.options.length=0;
	var nbVal=0;
	tmpStore.fetch({
  	query : {id : "*"},
  	onItem : function(item) {
  		mySelect.options[mySelect.length] = new Option(tmpStore.getValue(item,"name",""),tmpStore.getValue(item,"id",""));
  		nbVal++;
  	},  
    onError : function(err) { 
      console.info(err.message) ;  
    }
  });
}

function reportSelectReport(idReport) {
	loadContent("../view/reportsParameters.php?idReport="+idReport, "reportParametersDiv", null, false);
}

//=============================================================================
//= Resource Cost
//=============================================================================

function addResourceCost(idResource, idRole, funcList) {
	if (formChangeInProgress) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}	
	dojo.byId("resourceCostId").value="";
	dojo.byId("resourceCostIdResource").value=idResource;
	dojo.byId("resourceCostFunctionList").value=funcList;
	dijit.byId("resourceCostIdRole").set('disabled',false);
	dijit.byId("resourceCostIdRole").set('value',((idRole)?idRole:null));
	dijit.byId("resourceCostValue").reset();
	dijit.byId("resourceCostStartDate").reset();
	resourceCostUpdateRole();
	dijit.byId("dialogResourceCost").show();
}

function removeResourceCost(id, idRole, nameRole, startDate) {
	if (formChangeInProgress) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}
	dojo.byId("resourceCostId").value=id;
	actionOK=function() {loadContent("../tool/removeResourceCost.php", "resultDiv", "resourceCostForm", true, 'resourceCost');};
	msg=i18n('confirmDeleteResourceCost',new Array(nameRole, startDate));
	showConfirm (msg, actionOK);
} 

reourceCostLoad=false;
function editResourceCost(id, idResource,idRole,cost,startDate,endDate) {
	if (formChangeInProgress) {
		showAlert(i18n('alertOngoingChange'));
		return;
	}	
	dojo.byId("resourceCostId").value=id;
	dojo.byId("resourceCostIdResource").value=idResource;
	dijit.byId("resourceCostIdRole").setDisabled(true);
	dijit.byId("resourceCostValue").set('value',cost);
	var dateStartDate=getDate(startDate);
	dijit.byId("resourceCostStartDate").set('value',dateStartDate);
	dijit.byId("resourceCostStartDate").set('disabled',true);
	dijit.byId("resourceCostStartDate").set('required','false');
	reourceCostLoad=true;
	dijit.byId("resourceCostIdRole").set('value',idRole);
	setTimeout('reourceCostLoad=false;',300);
	dijit.byId("dialogResourceCost").show();  	
}

function saveResourceCost() {
	var formVar = dijit.byId('resourceCostForm');
  if(formVar.validate()){		
  	loadContent("../tool/saveResourceCost.php", "resultDiv", "resourceCostForm", true,'resourceCost');
  	dijit.byId('dialogResourceCost').hide();
  } else {
    showAlert(i18n("alertInvalidForm"));
  }
}

function resourceCostUpdateRole() {
	if (reourceCostLoad) {return;}
	var funcList=dojo.byId('resourceCostFunctionList').value;
	$key='#' + dijit.byId("resourceCostIdRole").get('value') + '#';
	if (funcList.indexOf($key)>=0) {
		dijit.byId("resourceCostStartDate").set('disabled',false);
		dijit.byId("resourceCostStartDate").set('required','true');
	} else {
		dijit.byId("resourceCostStartDate").set('disabled',true);
		dijit.byId("resourceCostStartDate").reset();
		dijit.byId("resourceCostStartDate").set('required','false');
	}
}


//=============================================================================
//= Misceallanous
//=============================================================================

//var manualWindow=null;
function showHelp() {
	var objectClass=dojo.byId('objectClass');
	var objectClassManual=dojo.byId('objectClassManual');
	var section='';
	if (objectClassManual) {
		section=objectClassManual.value;
	} else if (objectClass) {
		section=objectClass.value;
	}
	var url='../manual/manual.php?section=' + section;
	var name="Manual";
	var attributes='toolbar=no, titlebar=no, menubar=no, status=no, scrollbars=no, directories=no, location=no, resizable=no,'
		 +'height=650, width=1024, top=0, left=0';
	manualWindow=window.open(url, name , attributes);
	manualWindow.focus();
	//manualWindow.window.focus();
	
	return false;
} 


/**
 * Refresh a list (after update)
 */
function refreshList(field, param, paramVal, selected, destination, required) {
	var urlList='../tool/jsonList.php?listType=list&dataType=' + field;
	if (param) {
	  urlList+='&critField='+param;
	  urlList+='&critValue='+paramVal;
	}
	if (selected) {
		urlList+='&selected='+selected;
	}
	if (required) {
		urlList+='&required=true';
	}
	var tmpStore = new dojo.data.ItemFileReadStore({url: urlList});
	if (destination) {
	  var mySelect=dijit.byId(destination);	
	} else {
	  var mySelect=dijit.byId(field);
	}
	mySelect.store=tmpStore;
}

var menuHidden=false;
var menuActualStatus='visible';
var menuDivSize=0; 
var menuShowMode='CLICK';
/**
 * Hide or show the Menu (left part of the screen
 */
function hideShowMenu() {
	if (! dijit.byId("leftDiv")) {
		return;
	}
	if (menuActualStatus=='visible' || ! menuHidden) {
		dojo.byId('menuBarShow').style.display='block';
		menuDivSize=dojo.byId("leftDiv").offsetWidth;
		if (menuDivSize<2) {
			menuDivSize=dojo.byId("mainDiv").offsetWidth*.2;
		}
		dojo.byId('leftDiv_splitter').style.display='none';
		dijit.byId("leftDiv").resize({w: 20});
		dijit.byId("buttonHideMenu").set('label',i18n('buttonShowMenu'));
		menuHidden=true;
		menuActualStatus='hidden';
	} else {
		dojo.byId('menuBarShow').style.display='none';
		dojo.byId('leftDiv_splitter').style.display='block';
		if (menuDivSize<20) {
			menuDivSize=dojo.byId("mainDiv").offsetWidth*.2;
		}
		dijit.byId("leftDiv").resize({w: menuDivSize});
		dijit.byId("buttonHideMenu").set('label',i18n('buttonHideMenu'));
		menuHidden=false;
		menuActualStatus='visible';
	}
	dijit.byId("globalContainer").resize();	
}
function tempShowMenu(mode) {
	if (mode=='mouse' && menuShowMode=='CLICK') return;
	hideShowMenu();
	menuHidden=true;
}
function menuClick() {
	if (menuHidden) {
		menuHidden=false;
		hideShowMenu();
		menuHidden=true;
	}
}

var switchedMode=false;
var listDivSize=0;
var switchedVisible='';
var switchListMode='CLICK';
function switchMode(){
	if (! switchedMode) {
		switchedMode=true;
		dijit.byId("buttonSwitchMode").set('label',i18n('buttonStandardMode'));
		if (! dojo.byId("listDiv")) {
			if (listDivSize==0) {
			  listDivSize=dojo.byId("centerDiv").offsetHeight*.4;
			}
			return;
		} else {
		  listDivSize=dojo.byId("listDiv").offsetHeight;
		}
		if (dojo.byId('listDiv_splitter')) {
			dojo.byId('listDiv_splitter').style.display='none';
		}
		hideList();
	} else {
		switchedMode=false;
		dijit.byId("buttonSwitchMode").set('label',i18n('buttonSwitchedMode'));
		if (! dojo.byId("listDiv")) {
			return;
		}
		if (dojo.byId('listBarShow')) {
		  dojo.byId('listBarShow').style.display='none';
		}
		if (dojo.byId('detailBarShow')) {
		  dojo.byId('detailBarShow').style.display='none';
		}
		if (dojo.byId('listDiv_splitter')) {
			dojo.byId('listDiv_splitter').style.display='block';
		}
		if (listDivSize==0) {
		  listDivSize=dojo.byId("centerDiv").offsetHeight*.4;
		}
		dijit.byId("listDiv").resize({h: listDivSize});		
		dijit.byId("mainDivContainer").resize();
	}
}

function showList(mode) {
	//alert("showList");
	if (mode=='mouse' && switchListMode=='CLICK') return;
	if (! switchedMode) {
		return;
	}
	if (! dijit.byId("listDiv") || ! dijit.byId("mainDivContainer") ) {
		return;
	}
	if (dojo.byId('listDiv_splitter')) {
		dojo.byId('listDiv_splitter').style.display='none';
	}
	if (dojo.byId('listBarShow')) {
	  dojo.byId('listBarShow').style.display='none';
	}
	if (dojo.byId('detailBarShow')) {
	  dojo.byId('detailBarShow').style.display='block';
	}
	fullSize=dojo.byId("listDiv").offsetHeight+dojo.byId("detailDiv").offsetHeight-20;
	dijit.byId("listDiv").resize({h: fullSize});
	dijit.byId("mainDivContainer").resize();
	switchedVisible='list';
}
function hideList(mode) {
	//alert("hideList");
	if (mode=='mouse' && switchListMode=='CLICK') return;
	if (! switchedMode) {
		return;
	}
	if (! dijit.byId("listDiv") || ! dijit.byId("mainDivContainer") ) {
		return;
	}
	if (dojo.byId('listDiv_splitter')) {
		dojo.byId('listDiv_splitter').style.display='none';
	}
	if (dojo.byId('listBarShow')) {
	  dojo.byId('listBarShow').style.display='block';
	}
	if (dojo.byId('detailBarShow')) {
	  dojo.byId('detailBarShow').style.display='none';
	}
	dijit.byId("listDiv").resize({h: 20});
	dijit.byId("mainDivContainer").resize();
	switchedVisible='detail';
}

function listClick() {
	if (! switchedMode) {
		return;
	}
	hideList();
}

// Stock id and name, to 
// => avoid filterJsonList to reduce visibility => clear this data on open
// => retrieve data before close to retrieve the previous visibility
var quickSearchStockId=null;
var quickSearchStockName=null;
var quickSearchIsOpen=false;

function quickSearchOpen() {
  dojo.style("quickSearchDiv","display","block");
  if (dijit.byId("listTypeFilter")) {
	  dojo.style("listTypeFilter","display","none");	  
  }
  quickSearchStockId=dijit.byId('listIdFilter').get("value");
  if (dijit.byId('listNameFilter')) {
    quickSearchStockName=dijit.byId('listNameFilter').get("value");
    dojo.style("listNameFilter","display","none");
    dijit.byId('listNameFilter').reset();
  }  
  dijit.byId('listIdFilter').reset(); 
  dojo.style("listIdFilter","display","none");	
  dijit.byId("quickSearchValue").reset();
  dijit.byId("quickSearchValue").focus();
  quickSearchIsOpen=true;
}

function quickSearchClose() {
  quickSearchIsOpen=false;
  dojo.style("quickSearchDiv","display","none");
  if (dijit.byId("listTypeFilter")) {
	  dojo.style("listTypeFilter","display","block");	  
  }
  dojo.style("listIdFilter","display","block");
  if (dijit.byId('listNameFilter')) {
    dojo.style("listNameFilter","display","block");
    dijit.byId('listNameFilter').set("value",quickSearchStockName);
  }
  dijit.byId("quickSearchValue").reset();
  dijit.byId('listIdFilter').set("value",quickSearchStockId);  
  var objClass=dojo.byId('objectClass').value;
  refreshJsonList(objClass);
}

function quickSearchExecute() {
  if (! quickSearchIsOpen){
	  return;
  }
  if (! dijit.byId("quickSearchValue").get("value")) {
	showInfo(i18n('messageMandatory', new Array(i18n('quickSearch'))));
    return;
  }	
  var objClass=dojo.byId('objectClass').value;
  refreshJsonList(objClass);
}

/* ==========================================
 * Copy functions
 */
function copyObject(objectClass) {
  dojo.byId("copyButton").blur();
  action=function(){
    unselectAllRows('objectGrid');
    loadContent("../tool/copyObject.php", "resultDiv", 'objectForm', true);
  };
  showConfirm(i18n("confirmCopy", new Array(i18n(objectClass),dojo.byId('id').value)) ,action);
}

function copyObjectTo(objectClass) {
  dojo.byId('copyClass').value=dojo.byId("objectClass").value;
  dojo.byId('copyId').value=dojo.byId("objectId").value;
  dijit.byId('copyToClass').set('displayedValue',i18n(objectClass));
  dijit.byId('copyToName').set('value',dijit.byId('name').get('value'));
  dijit.byId('copyToOrigin').set('checked','checked');
  dijit.byId('dialogCopy').show();	
}

function copyObjectToSubmit(objectClass) {
  unselectAllRows('objectGrid');
  loadContent("../tool/copyObjectTo.php", "resultDiv", 'copyForm', true, 'copyTo');
  dijit.byId('dialogCopy').hide();
  dojo.byId('objectClass').value=copyableArray[dijit.byId('copyToClass').get('value')];
}