<?php
/* ============================================================================
 * Main page of application.
 * This page includes Frame definitions and framework requirements.
 * All the other pages are included into this one, in divs, using Ajax.
 * 
 *  Remarks for deployment :
 *    - set isDebug:false in djConfig
 */
require_once "../tool/projector.php";
header ('Content-Type: text/html; charset=UTF-8');
scriptLog('   ->/view/main.php');
if (Sql::getDbVersion()!=$version) {
	//Here difference of version is an important issue => disconnect and get back to login page.
	session_destroy();
	include_once 'login.php';
	exit;
}

checkVersion(); ?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" 
  "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>   
  <title><?php echo i18n("applicationTitle");?></title>
  <link rel="stylesheet" type="text/css" href="css/jsgantt.css" />
  <link rel="stylesheet" type="text/css" href="css/projector.css" />
  <link rel="shortcut icon" href="img/logo.ico" type="image/x-icon" />
  <link rel="icon" href="img/logo.ico" type="image/x-icon" />
  <script type="text/javascript" src="js/projector.js" ></script>
  <script type="text/javascript" src="js/jsgantt.js"></script>
  <script type="text/javascript" src="js/projectorWork.js" ></script>
  <script type="text/javascript" src="js/projectorDialog.js" ></script>
  <script type="text/javascript" src="js/projectorFormatter.js" ></script>
  <script type="text/javascript" src="../external/dojo/dojo.js"
    djConfig='modulePaths: {"i18n":"../../tool/i18n"},
              parseOnLoad: true, 
              isDebug: <?php echo getBooleanValueAsString($paramDebugMode);?>'></script>
  <script type="text/javascript" src="../external/dojo/projectorDojo.js"></script>
  <script type="text/javascript"> 
    dojo.require("dojo.data.ItemFileWriteStore");
    dojo.require("dojo.date");
    dojo.require("dojo.i18n");
    dojo.require("dojo.parser");
    dojo.require("dijit.Dialog"); 
    dojo.require("dijit.Tooltip");
    dojo.require("dijit.layout.BorderContainer");
    dojo.require("dijit.layout.ContentPane");
    dojo.require("dijit.layout.AccordionContainer");
    dojo.require("dijit.Menu"); 
    dojo.require("dijit.MenuBar"); 
    dojo.require("dijit.MenuBarItem"); 
    dojo.require("dijit.PopupMenuBarItem");
    dojo.require("dijit.form.ValidationTextBox");
    dojo.require("dijit.form.Textarea");
    dojo.require("dijit.form.ComboBox");
    dojo.require("dijit.form.CheckBox");
    dojo.require("dijit.form.RadioButton");
    dojo.require("dijit.form.DateTextBox");
    dojo.require("dijit.form.TimeTextBox");
    dojo.require("dijit.form.TextBox");
    dojo.require("dijit.form.NumberTextBox");
    dojo.require("dijit.form.Button");
    dojo.require("dijit.ColorPalette");
    dojo.require("dijit.form.Form");
    dojo.require("dijit.form.FilteringSelect");
    dojo.require("dijit.form.MultiSelect");
    dojo.require("dijit.form.NumberSpinner");
    dojo.require("dijit.Tree"); 
    dojo.require("dijit.TitlePane");
    dojo.require("dojox.grid.DataGrid");
    dojo.require("dojox.form.FileInput");
    dojo.require("dojo.dnd.Container");
    dojo.require("dojo.dnd.Manager");
    dojo.require("dojo.dnd.Source");
    dojo.subscribe("/dnd/drop", function(source, nodes, copy, target){
       if (nodes.length>0 && nodes[0] && target) {
         var idFrom = nodes[0].id;
         var idTo = target.current.id; 
         showWait();
         setTimeout('moveTask("' + idFrom + '", "' + idTo + '")',100); 
       }
    });
    var historyTable=new Array();
    var historyPosition=-1;    
    var fadeLoading=<?php echo getBooleanValueAsString($paramFadeLoadingMode);?>;
    //var refreshUpdates="<?php echo (array_key_exists('refreshUpdates',$_SESSION))?$_SESSION['refreshUpdates']:'YES';?>";
    var refreshUpdates="YES";
    var printInNewWindow=<?php echo (getPrintInNewWindow())?'true':'false';?>;
    var pdfInNewWindow=<?php echo (getPrintInNewWindow('pdf'))?'true':'false';?>;
    var alertCheckTime='<?php echo Parameter::getGlobalParameter('alertCheckTime');?>';
    var offDayList='<?php echo Calendar::getOffDayList();?>';
    var workDayList='<?php echo Calendar::getWorkDayList();?>';
    var draftSeparator='<?php echo Parameter::getGlobalParameter('draftSeparator');?>';
    dojo.addOnLoad(function(){
      currentLocale="<?php echo $currentLocale;?>";
      <?php 
      if (isset($_SESSION['hideMenu'])) {
        if ($_SESSION['hideMenu']!='NO') {
          echo "menuHidden=true;";
          echo "menuShowMode='" . $_SESSION['hideMenu'] . "';";
        }
      }
      if (isset($_SESSION['switchedMode'])) {
        if ($_SESSION['switchedMode']!='NO') {
          echo "switchedMode=true;";
          echo "switchListMode='" . $_SESSION['switchedMode'] . "';";
        }
      }
      ?>
      dijit.Tooltip.defaultPosition=["below", "right"];
      addMessage("<?php echo i18n('welcomeMessage');?>");
      //dojo.byId('body').className='<?php echo getTheme();?>';
      saveResolutionToSession();
      saveBrowserLocaleToSession();
      // Relaunch Cron (if stopped, any connexion will restart it)
      adminCronRelaunch();
      var onKeyPressFunc = function(event) {
        if(event.ctrlKey && event.keyChar == 's'){
          event.preventDefault();
        	globalSave();
        } else if (event.keyCode==dojo.keys.F1 && ! event.keyChar) {
        	event.preventDefault();
        	showHelp();
        }  
      };
      dojo.connect(document, "onkeypress", this, onKeyPressFunc);
      <?php 
      $firstPage="today.php";
      if (array_key_exists("directAccessPage",$_REQUEST)) {
        $firstPage=$_REQUEST['directAccessPage'];
        if (array_key_exists("menuActualStatus",$_REQUEST)) {
          $menuActualStatus=$_REQUEST['menuActualStatus'];
          if ($menuActualStatus!='visible') {
            echo 'hideShowMenu();';
          }
        }  
        for ($i=1;$i<=9;$i++) {
          $pName='p'.$i.'name';
          $pValue='p'.$i.'value';
          if (array_key_exists($pName,$_REQUEST) and array_key_exists($pValue,$_REQUEST) ) {
            $firstPage.=($i==1)?'?':'&';
            $firstPage.=$_REQUEST[$pName]."=".$_REQUEST[$pValue];
          } else {
            break;
          }
        }
      }
      ?>
      dojo.byId("loadingDiv").style.visibility="hidden";
      dojo.byId("loadingDiv").style.display="none";
      dojo.byId("mainDiv").style.visibility="visible";        
      loadContent("<?php echo $firstPage;?>","centerDiv");
      <?php if (! $showTopMenu) {
          echo "dijit.byId('toolBarDiv').resize({h: 0});;";
          echo "dijit.byId('globalContainer').resize();";
       } ?>
       setTimeout('checkAlert();',5000); //first check at 5 seco 
    }); 
    var cronSleepTime=<?php echo Cron::getSleepTime();?>;
    var canCreateArray=new Array();
    var dependableArray=new Array();
    var linkableArray=new Array();
    var originableArray=new Array();
    var copyableArray=new Array();
    var indicatorableArray=new Array();
    <?php 
      $list=SqlList::getListNotTranslated('Dependable');
      foreach ($list as $id=>$name) {
      	$right=securityGetAccessRightYesNo('menu' . $name,'create');
      	echo "canCreateArray['" . $name . "']='" . $right . "';";
      	echo "dependableArray['" . $id . "']='" . $name . "';";
      }
      $list=SqlList::getListNotTranslated('Linkable');
      foreach ($list as $id=>$name) {
        $right=securityGetAccessRightYesNo('menu' . $name,'create');
        echo "canCreateArray['" . $name . "']='" . $right . "';";
        echo "linkableArray['" . $id . "']='" . $name . "';";
      }    
      $list=SqlList::getListNotTranslated('Originable');
      foreach ($list as $id=>$name) {
        $right=securityGetAccessRightYesNo('menu' . $name,'create');
        echo "canCreateArray['" . $name . "']='" . $right . "';";
        echo "originableArray['" . $id . "']='" . $name . "';";
      }
      $list=SqlList::getListNotTranslated('Copyable');
      foreach ($list as $id=>$name) {
        echo "copyableArray['" . $id . "']='" . $name . "';";
      }
      $list=SqlList::getListNotTranslated('Indicatorable');
      foreach ($list as $id=>$name) {
        echo "indicatorableArray['" . $id . "']='" . $name . "';";
      }                
      ?>
    //window.onbeforeunload = function (evt){ return beforequit();};
  </script>
</head>

<body id="body" class="<?php echo getTheme();?>" onBeforeUnload="return beforequit();" onUnload="quit();">
<div id="loadingDiv" class="<?php echo getTheme();?> background" 
 style="position:relative; visibility: visible; display:block; width:100%; height:100%; ">
 <table style="width:100%; height:100%; text-align:center; vertical-align:middle;"><tr><td>Loading ...</td></tr></table>
</div>
<div id="mainDiv" style="visibility: hidden;">
  <div id="wait" >
  </div>
  <div id="globalContainer" class="container" dojoType="dijit.layout.BorderContainer" liveSplitters="false">    
    <div id="leftDiv" dojoType="dijit.layout.ContentPane" region="left" splitter="true">
     <div id="menuBarShow" onMouseover="tempShowMenu('mouse');" onClick="tempShowMenu('click');"><div id="menuBarIcon" valign="middle"></div></div>       
      <div class="container" dojoType="dijit.layout.BorderContainer" liveSplitters="false">
        <div id="logoDiv" dojoType="dijit.layout.ContentPane" region="top">
          <script> 
            aboutMessage="<?php echo $aboutMessage;?>";
            aboutMessage+='Dojo '+dojo.version+'<br/><br/>';
          </script>
          <?php 
            $width=300;
            if (array_key_exists('screenWidth',$_SESSION)) {
              $width = $_SESSION['screenWidth'] * 0.2;
            }
            $zoom=round($width/300*100, 0);  
          ?>
          <div id="logoTitleDiv" onclick="showAbout(aboutMessage);" title="<?php echo i18n('aboutMessage');?>" > 
          </div>
          <div style="position:absolute; right:0;" id="help" style="text-align:right"; onclick="showHelp();"><img width="32px" height="32px" src='../view/img/help.png' title="<?php echo i18n('help');?>" onclick="showHelp();" /></div>
        </div>
        <div id="mapDiv" dojoType="dijit.layout.ContentPane" region="center" style="padding: 0px; margin:0px">
          <div dojoType="dijit.layout.AccordionContainer" style="height: 300px;" >
            <div dojoType="dijit.layout.ContentPane" title="<?php echo i18n('menu');?>" style="overflow: hidden !important;" selected="true">
              <?php include "menuTree.php"; ?>
            </div>
            <div dojoType="dijit.layout.ContentPane" title="<?php echo i18n('document');?>">
              <div dojoType="dojo.data.ItemFileReadStore" id="directoryStore" jsId="directoryStore" url="../tool/jsonDirectory.php">
              <div style="position: absolute; float:right; right: 5px; cursor:pointer;"
                title="<?php echo i18n("menuDocumentDirectory");?>"
                onclick="loadContent('objectMain.php?objectClass=DocumentDirectory','centerDiv');"
                class="iconDocumentDirectory22">
              </div>
              </div>
              <div dojoType="dijit.tree.ForestStoreModel" id="directoryModel" jsId="directoryModel" store="directoryStore"
               query="{id:'*'}" rootId="directoryRoot" rootLabel="Documents"
               childrenAttrs="children">
              </div>             
              <div dojoType="dijit.Tree" id="documentDirectoryTree" model="directoryModel" openOnClick="false" showRoot='false'>
                <script type="dojo/method" event="onClick" args="item">
                  loadContent("objectMain.php?objectClass=Document&Directory="+directoryStore.getValue(item, "id"),"centerDiv");
                  //alert("Execute of node " + directoryStore.getLabel(item) + ", population=" +);
                </script>
              </div>
            </div>
          </div>
        </div>
        <div id="messageDiv" dojoType="dijit.layout.ContentPane" region="bottom" splitter="true">
        </div>
      </div> 
    </div>
    <div id="toolBarDiv" dojoType="dijit.layout.ContentPane" region="top"  >
      <?php include "menuBar.php";?>
    </div>
    <div id="centerDiv" dojoType="dijit.layout.ContentPane" region="center" >      
    </div>
    <div id="statusBarDiv" dojoType="dijit.layout.ContentPane" region="bottom">
      <table width="100%">
        <tr>
          <td width="20%" title="<?php echo i18n('disconnectMessage');?>" onclick="disconnect();" style="vertical-align: middle; text-align: left; cursor: pointer; ">
            <table>
              <tr>
                <td>
                  <img src="img/disconnect.gif" />
                </td>
                <td>
                  &nbsp;<?php echo i18n('disconnect') . '&nbsp;[' . $_SESSION["user"]->name . ']'; ?>
                </td>
              </tr>
            </table>         
          </td>
          <td width="30%" >
            <div id="statusBarProgressDiv" style="text-align: left;color: #000000"> 
              <button id="buttonHideMenu" style="font-size: 90%;" dojoType="dijit.form.Button" onclick="hideShowMenu();">
                <?php echo i18n("buttonHideMenu");?>
              </button>
              <button id="buttonSwitchMode" style="font-size: 90%;" dojoType="dijit.form.Button" onclick="switchMode();">
                <?php 
                  if (isset($_SESSION['switchedMode']) and $_SESSION['switchedMode']!='NO') {
                    echo i18n("buttonStandardMode");
                  } else {
                    echo i18n("buttonSwitchedMode");
                  }?>
              </button>                
            </div>
          </td>
          <td width="30%" >
            <div id="statusBarMessageDiv" style="text-align: left">
              <?php htmlDisplayDatabaseInfos();?>
            </div>
          </td>
          <td width="20%" title="<?php echo i18n('infoMessage');?>" > 
            <div width="100%" id="statusBarInfoDiv" style="text-align: right;">
              <?php htmlDisplayInfos();?>
            </div>
          </td>
        </tr>
      </table>  
    </div>    
  </div>
</div>
<div id="dialogReminder" >
 <div id="reminderDiv" style="width:100%;height: 75%"></div>
  <div style="width:100%; height:15%; text-align:right">
    <?php echo i18n("remindMeIn");?>
   <input type="input" dojoType="dijit.form.TextBox" id="remindAlertTime" name="remindAletTime" value="15" style="width:25px" />
    <?php echo i18n("shortMinute");?>
   <button dojoType="dijit.form.Button" onclick="setAlertRemindMessage();">
            <?php echo i18n("remind");?>
   </button>
 </div>
 <div style="width:100%; height:10%; text-align:right">
	 <button dojoType="dijit.form.Button" onclick="setAlertReadMessage();">
	          <?php echo i18n("markAsRead");?>
	 </button>
 </div>
</div>
<div id="dialogInfo" dojoType="dijit.Dialog" title="<?php echo i18n("dialogInformation");?>">
  <table>
    <tr>
      <td width="50px">
        <img src="img/info.png" />
      </td>
      <td>
        <div id="dialogInfoMessage">
        </div>
      </td>
    </tr>
    <tr>
      <td colspan="2" align="center">
        <br/>
        <button dojoType="dijit.form.Button" type="submit" onclick="dijit.byId('dialogInfo').hide();">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>

<div id="dialogError" dojoType="dijit.Dialog" title="<?php echo i18n("dialogError");?>">
  <table>
    <tr>
      <td width="50px">
        <img src="img/error.png" />
      </td>
      <td>
        <div id="dialogErrorMessage" class="messageERROR">
        </div>
      </td>
    </tr>
    <tr height="50px">
      <td colspan="2" align="center">
        <?php echo i18n("contactAdministrator");?>
      </td>
    </tr>
    <tr><td colspan="2" align="center">&nbsp;</td></tr>
    <tr>
      <td colspan="2" align="center">
        <button dojoType="dijit.form.Button" type="submit" onclick="dijit.byId('dialogError').hide();">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>

<div id="dialogAlert" dojoType="dijit.Dialog" title="<?php echo i18n("dialogAlert");?>">
  <table>
    <tr>
      <td width="50px">
        <img src="img/alert.png" />
      </td>
      <td>
        <div id="dialogAlertMessage">
        </div>
      </td>
    </tr>
    <tr><td colspan="2" align="center">&nbsp;</td></tr>
    <tr>
      <td colspan="2" align="center">
        <button dojoType="dijit.form.Button" type="submit" onclick="dijit.byId('dialogAlert').hide();">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>

<div id="dialogQuestion" dojoType="dijit.Dialog" title="<?php echo i18n("dialogQuestion");?>">
  <table>
    <tr>
      <td width="50px">
        <img src="img/confirm.png" />
      </td>
      <td>
        <div id="dialogQuestionMessage"></div>
      </td>
    </tr>
    <tr><td colspan="2" align="center">&nbsp;</td></tr>
    <tr>
      <td colspan="2" align="center">
        <button dojoType="dijit.form.Button" onclick="dijit.byId('dialogQuestion').acceptCallbackNo();dijit.byId('dialogQuestion').hide();">
          <?php echo i18n("buttonNo");?>
        </button>
        <button dojoType="dijit.form.Button" type="submit" onclick="dijit.byId('dialogQuestion').acceptCallbackYes();dijit.byId('dialogQuestion').hide();">
          <?php echo i18n("buttonYes");?>
        </button>
      </td>
    </tr>
  </table>
</div>

<div id="dialogConfirm" dojoType="dijit.Dialog" title="<?php echo i18n("dialogConfirm");?>">
  <table>
    <tr>
      <td width="50px">
        <img src="img/alert.png" />
      </td>
      <td>
        <div id="dialogConfirmMessage"></div>
      </td>
    </tr>
    <tr><td colspan="2" align="center">&nbsp;</td></tr>
    <tr>
      <td colspan="2" align="center">
        <input type="hidden" id="dialogConfirmAction">
        <button dojoType="dijit.form.Button" onclick="dijit.byId('dialogConfirm').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button dojoType="dijit.form.Button" type="submit" onclick="dijit.byId('dialogConfirm').acceptCallback();dijit.byId('dialogConfirm').hide();">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>

<div id="dialogPrint" dojoType="dijit.Dialog" title="<?php echo i18n("dialogPrint");?>" onHide="dojo.byId('printFrame').src='../view/preparePreview.php';">
  <?php 
    $printHeight=600;
    $printWidth=1000;
    //if (array_key_exists('screenWidth',$_SESSION)) {
    //   $printWidth = $_SESSION['screenWidth'] * 0.8;
    //}
    if (array_key_exists('screenHeight',$_SESSION)) {
      $printHeight=round($_SESSION['screenHeight']*0.65);
    }
  ?> 
  <div id="printPreview" dojoType="dijit.layout.ContentPane" region="center">
    <table>
      <tr>
        <td width="<?php echo $printWidth;?>px" align="right">
          <div id="sentToPrinterDiv">
            <table width="100%"><tr><td width="300px" align="right">
              <button id="sendToPrinter" dojoType="dijit.form.Button" showlabel="false"
                title="<?php echo i18n('sendToPrinter');?>" 
                iconClass="dijitEditorIcon dijitEditorIconPrint" >
                <script type="dojo/connect" event="onClick" args="evt">
                  sendFrameToPrinter();
                </script>
              </button>
            </td>
            <td align="left" width="<?php echo $printWidth - 300;?>px">
              &nbsp;<b><i><?php echo i18n('sendToPrinter')?></i></b>
            </td></tr></table>
          </div>
        </td>
      </tr>
      <tr>
        <td>   
          <iframe width="100%" height="<?php echo $printHeight;?>px"
            scrolling="auto" frameborder="0px" name="printFrame" id="printFrame" src="">
          </iframe>
        </td>
      </tr>
    </table>
  </div>
</div>

<div id="dialogDetail" dojoType="dijit.Dialog" title="<?php echo i18n("dialogDetailCombo");?>" class="background" >
  <?php 
    $detailHeight=600;
    $detailWidth=1000;
    if (array_key_exists('screenWidth',$_SESSION)) {
       $detailWidth = $_SESSION['screenWidth'] * 0.87;
    }
    if (array_key_exists('screenHeight',$_SESSION)) {
      $detailHeight=round($_SESSION['screenHeight']*0.65);
    }
  ?> 
  <div id="detailView" dojoType="dijit.layout.ContentPane" region="center" style="overflow:hidden" class="white">
    <table>
      <tr>
        <td width="300px" align="left">
          <input type="hidden" name="canCreateDetail" id="canCreateDetail" />
          <button id="comboSearchButton" dojoType="dijit.form.Button" showlabel="false"
            title="<?php echo i18n('comboSearchButton');?>" 
            iconClass="iconSearch" >
            <script type="dojo/connect" event="onClick" args="evt">
              displaySearch();
            </script>
          </button>
          <button id="comboSelectButton" dojoType="dijit.form.Button" showlabel="false"
            title="<?php echo i18n('comboSelectButton');?>" 
            iconClass="iconSelect" >
            <script type="dojo/connect" event="onClick" args="evt">
              selectDetailItem();
            </script>
          </button>
          <button id="comboNewButton" dojoType="dijit.form.Button" showlabel="false"
            title="<?php echo i18n('comboNewButton');?>" 
            iconClass="dijitEditorIcon dijitEditorIconNew" >
            <script type="dojo/connect" event="onClick" args="evt">
              newDetailItem();
            </script>
          </button>
          <button id="comboSaveButton" dojoType="dijit.form.Button" showlabel="false"
            title="<?php echo i18n('comboSaveButton');?>" 
            iconClass="dijitEditorIcon dijitEditorIconSave" >
            <script type="dojo/connect" event="onClick" args="evt">
              saveDetailItem();
            </script>
          </button>
         <button id="comboCloseButton" dojoType="dijit.form.Button" showlabel="false"
            title="<?php echo i18n('comboCloseButton');?>" 
            iconClass="dijitEditorIcon dijitEditorIconUndo" >
            <script type="dojo/connect" event="onClick" args="evt">
              hideDetail();
            </script>
          </button>
          <input type="hidden" id='comboName' name='comboName' value='' />
          <input type="hidden" id='comboClass' name='comboClass' value='' />
        </td>
        <td align="left" style="width:<?php echo ($detailWidth - 400);?>px">
          <div style="width:100%;font-size:8pt" dojoType="dijit.layout.ContentPane" region="center" name="comboDetailResult" id="comboDetailResult"></div>
        </td>
        <td></td>
      </tr>
      <tr><td colspan="3">&nbsp;</td></tr>
      <tr>
        <td colspan="3">   
          <iframe width="100%" height="<?php echo $detailHeight;?>px"
            scrolling="auto" frameborder="0px" name="comboDetailFrame" id="comboDetailFrame" src="" >
          </iframe>
        </td>
      </tr>
    </table>
  </div>
</div>
 
<div id="dialogNote" dojoType="dijit.Dialog" title="<?php echo i18n("dialogNote");?>">
  <table>
    <tr>
      <td>
       <form id='noteForm' name='noteForm' onSubmit="return false;">
         <input id="noteId" name="noteId" type="hidden" value="" />
         <input id="noteRefType" name="noteRefType" type="hidden" value="" />
         <input id="noteRefId" name="noteRefId" type="hidden" value="" />
         <textarea dojoType="dijit.form.Textarea" 
          id="noteNote" name="noteNote"
          style="width: 500px;"
          maxlength="4000"
          class="input"
          onClick="dijit.byId('noteNote').setAttribute('class','');">  
          </textarea>
        </form>
      </td>
    </tr>
    <tr>
      <td align="center">
        <input type="hidden" id="dialogNoteAction">
        <button dojoType="dijit.form.Button" onclick="dijit.byId('dialogNote').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button id="dialogNoteSubmit" dojoType="dijit.form.Button" type="submit" onclick="saveNote();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>



<div id="dialogBillLine" dojoType="dijit.Dialog" title="<?php echo i18n("dialogBillLine");?>">
  <table>
    <tr>
      <td>
       <form id='billLineForm' name='billLineForm' onSubmit="return false;">
      	 <input id="billLineId" name="billLineId" type="hidden" value="" />
         <input id="billLineRefType" name="billLineRefType" type="hidden" value="" />
         <input id="billLineRefId" name="billLineRefId" type="hidden" value="" />
       	 <table>
           <tr>
             <td class="dialogLabel" >
              <label for="billLineLine" ><?php echo i18n("colLineNumber");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
		           <textarea dojoType="dijit.form.NumberTextBox" 
			          id="billLineLine" name="billLineLine"
			          style="width: 50px;"
			          class="input"
			          onClick="dijit.byId('billLineLine').setAttribute('class','');">  
		           </textarea>
		         </td>
		       </tr>
		       <tr>
             <td class="dialogLabel" >
               <label for="billLineQuantity" ><?php echo i18n("colQuantity");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <input dojoType="dijit.form.NumberTextBox" 
                id="billLineQuantity" name="billLineQuantity"
                style="width: 50px;"
                class="input">  
               </input> 
             </td>
           </tr>
          </table>
          <div id='billLineFrameTerm'>
          <table>
		       <tr>
             <td class="dialogLabel" >
               <label for="billLineIdTerm" ><?php echo i18n("colIdTerm");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <select dojoType="dijit.form.FilteringSelect" 
                id="billLineIdTerm" name="billLineIdTerm"
                missingMessage="<?php echo i18n('mandatory');?>"
                class="input" value="" >
                <?php 
                   htmlDrawOptionForReference('idTerm', null,null,false);
                 ?>
               </select>  
             </td>
           </tr>
           </table>
           </div> 
           <div id='billLineFrameResource'>
           <table>
           <tr>
             <td class="dialogLabel" >
               <label for="billLineIdResource" ><?php echo i18n("colIdResource");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <select dojoType="dijit.form.FilteringSelect" 
                id="billLineIdResource" name="billLineIdResource"
                missingMessage="<?php echo i18n('mandatory');?>"
                class="input" value="" >
                <?php 
                   htmlDrawOptionForReference('idResource', null,null,false);
                 ?>
               </select>  
             </td>
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="billLineIdActivityPrice" ><?php echo i18n("colIdActivityPrice");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <select dojoType="dijit.form.FilteringSelect" 
                id="billLineIdActivityPrice" name="billLineIdActivityPrice"
                missingMessage="<?php echo i18n('mandatory');?>"
                class="input" value="" >
                <?php 
                   htmlDrawOptionForReference('idActivityPrice', null,null,false);
                 ?>
               </select>  
             </td>
           </tr>
		       <tr>
             <td class="dialogLabel" >
               <label for="billLineStartDate" ><?php echo i18n("colStartDate");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <div id="billLineStartDate" name="billLineStartDate"
                dojoType="dijit.form.DateTextBox" required="true" hasDownArrow="false"   
                type="text" maxlength="10"  style="width:100px; text-align: center;" class="input"
                missingMessage="<?php echo i18n('messageMandatory',array('colDate'));?>" 
                invalidMessage="<?php echo i18n('messageMandatory',array('colDate'));?>" >
               </div>
             </td>
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="billLineEndDate" ><?php echo i18n("colEndDate");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <div id="billLineEndDate" name="billLineEndDate"
                dojoType="dijit.form.DateTextBox" required="true" hasDownArrow="false"   
                type="text" maxlength="10"  style="width:100px; text-align: center;" class="input"
                missingMessage="<?php echo i18n('messageMandatory',array('colDate'));?>" 
                invalidMessage="<?php echo i18n('messageMandatory',array('colDate'));?>" >
               </div>
             </td>
           </tr>
           </table>
           </div> 
           <div id='billLineFrameDescription'>
           <table>
           <tr>
             <td class="dialogLabel" >
              <label for="billLineDescription" ><?php echo i18n("colDescription");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
              <textarea dojoType="dijit.form.Textarea" 
	          id="billLineDescription" name="billLineDescription"
	          style="width: 500px;"
	          maxlength="200"
	          class="input"
	          onClick="dijit.byId('billLineDescription').setAttribute('class','');">  
	          </textarea>
	         </td>
	        </tr>
            <tr>
             <td class="dialogLabel" >
              <label for="billLineDetail" ><?php echo i18n("colDetail");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <textarea dojoType="dijit.form.Textarea" 
	          id="billLineDetail" name="billLineDetail"
	          style="width: 500px;"
	          maxlength="200"
	          class="input">
	          </textarea>  
	         </td>
	        </tr>
          </table>
          </div>
          <table>
            <tr>
             <td class="dialogLabel" >
              <label for="billLinePrice" ><?php echo i18n("colPrice");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
              <textarea dojoType="dijit.form.NumberTextBox" 
	          id="billLinePrice" name="billLinePrice"
	          style="width: 100px;"
	          class="input"
	          onClick="dijit.byId('billLinePrice').setAttribute('class','');">  
	          </textarea> 
	         </td>
	        </tr>
	      </table>     
        </form>
      </td>
    </tr>
    <tr>
      <td align="center">
        <input type="hidden" id="dialogBillLineAction">
        <button dojoType="dijit.form.Button" onclick="dijit.byId('dialogBillLine').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button dojoType="dijit.form.Button" id="dialogBillLineSubmit" type="submit" onclick="saveBillLine();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>


<div id="dialogLink" dojoType="dijit.Dialog" title="<?php echo i18n("dialogLink");?>">
  <table>
    <tr>
      <td>
       <form id='linkForm' name='linkForm' onSubmit="return false;">
         <input id="linkFixedClass" name="linkFixedClass" type="hidden" value="" />
         <input id="linkId" name="linkId" type="hidden" value="" />
         <input id="linkRef1Type" name="linkRef1Type" type="hidden" value="" />
         <input id="linkRef1Id" name="linkRef1Id" type="hidden" value="" />
         <table>
           <tr>
             <td class="dialogLabel"  >
               <label for="linkRef2Type" ><?php echo i18n("linkType") ?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <select dojoType="dijit.form.FilteringSelect" 
                id="linkRef2Type" name="linkRef2Type" 
                onchange="refreshLinkList();"
                class="input" value="" >
                 <?php htmlDrawOptionForReference('idLinkable', null, null, true);?>
               </select>
             </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
           <tr>
             <td class="dialogLabel" >
               <label for="linkRef2Id" ><?php echo i18n("linkElement") ?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <table><tr><td>
               <div id="dialogLinkList" dojoType="dijit.layout.ContentPane" region="center">
                 <input id="linkRef2Id" name="linkRef2Id" type="hidden" value="" />
               </div>
               </td><td style="vertical-align: top">
               <button id="linkDetailButton" dojoType="dijit.form.Button" showlabel="false"
                 title="<?php echo i18n('showDetail')?>"
                 iconClass="iconView">
                 <script type="dojo/connect" event="onClick" args="evt">
                    showDetailLink();
                 </script>
               </button>
               </td></tr></table>
             </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
         </table>
        </form>
      </td>
    </tr>
    <tr>
      <td align="center">
        <input type="hidden" id="dialogLinkAction">
        <button dojoType="dijit.form.Button" onclick="dijit.byId('dialogLink').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button dojoType="dijit.form.Button" type="submit" id="dialogLinkSubmit" onclick="saveLink();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>

<div id="dialogOrigin" dojoType="dijit.Dialog" title="<?php echo i18n("dialogOrigin");?>">
  <table>
    <tr>
      <td>
       <form id='originForm' name='originForm' onSubmit="return false;">
         <input id="originId" name="originId" type="hidden" value="" />
         <input id="originRefId" name="originRefId" type="hidden" value="" />
         <input id="originRefType" name="originRefType" type="hidden" value="" />
         <table>
           <tr>
             <td class="dialogLabel"  >
               <label for="originOriginType" ><?php echo i18n("originType") ?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <select dojoType="dijit.form.FilteringSelect" 
                id="originOriginType" name="originOriginType" 
                onchange="refreshOriginList();"
                class="input" value="" >
                 <?php htmlDrawOptionForReference('idOriginable', null, null, true);?>
               </select>
             </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
           <tr>
             <td class="dialogLabel" >
               <label for="OriginOriginId" ><?php echo i18n("originElement") ?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <table><tr><td>
               <div id="dialogOriginList" dojoType="dijit.layout.ContentPane" region="center">
                 <input id="originOriginId" name="originOriginId" type="hidden" value="" />
               </div>
               </td><td style="vertical-align: top">
               <button id="originDetailButton" dojoType="dijit.form.Button" showlabel="false"
                 title="<?php echo i18n('showDetail')?>"
                 iconClass="iconView">
                 <script type="dojo/connect" event="onClick" args="evt">
                    showDetailOrigin();
                 </script>
               </button>
               </td></tr></table>
             </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
         </table>
        </form>
      </td>
    </tr>
    <tr>
      <td align="center">
        <input type="hidden" id="dialogOriginAction">
        <button dojoType="dijit.form.Button" onclick="dijit.byId('dialogOrigin').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button dojoType="dijit.form.Button" type="submit" id="dialogOriginSubmit" onclick="saveOrigin();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>

<div id="dialogCopy" dojoType="dijit.Dialog" title="<?php echo i18n("dialogCopy");?>">
  <table>
    <tr>
      <td>
       <form dojoType="dijit.form.Form" id='copyForm' name='copyForm' onSubmit="return false;">
         <input id="copyClass" name="copyClass" type="hidden" value="" />
         <input id="copyId" name="copyId" type="hidden" value="" />
         <table>
           <tr>
             <td class="dialogLabel"  >
               <label for="copyToClass" ><?php echo i18n("copyToClass") ?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <select dojoType="dijit.form.FilteringSelect" 
                id="copyToClass" name="copyToClass" required
                class="input" value="" >
                 <?php htmlDrawOptionForReference('idCopyable', null, null, true);?>
                 <script type="dojo/connect" event="onChange" args="evt" >
                   var objclass=copyableArray[this.value];
                   dijit.byId('copyToType').reset();
                   refreshList("idType","scope", objclass, null,'copyToType',true);
                   if (dojo.byId('copyClass').value==objclass) {
                     var runModif="dijit.byId('copyToType').set('value',dijit.byId('id"+objclass+"Type').get('value'))";
                     setTimeout(runModif,1);
                   }
                 </script> 
               </select>
             </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
           <tr>
             <td class="dialogLabel"  >
               <label for="copyToType" ><?php echo i18n("copyToType") ?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <select dojoType="dijit.form.FilteringSelect" 
                id="copyToType" name="copyToType" required
                class="input" value="" >
               </select>
             </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
           <tr>
             <td class="dialogLabel" >
               <label for="copyToName" ><?php echo i18n("copyToName") ?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <div id="copyToName" name="copyToName" dojoType="dijit.form.ValidationTextBox"
                required="required"
                style="width: 400px;"
                trim="true" maxlength="100" class="input"
                value="">
               </div>     
             </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
           <tr>
             <td class="dialogLabel" colspan="2" style="width:100%; text-align: left;">
               <label for="copyToOrigin" style="width:90%;text-align: right;"><?php echo i18n("copyToOrigin") ?>&nbsp;:&nbsp;</label>
               <div id="copyToOrigin" name="copyToOrigin" dojoType="dijit.form.CheckBox" type="checkbox" 
                checked >
               </div>
             </td>
           </tr>
           <tr><td>&nbsp;</td><td >&nbsp;</td></tr>
         </table>
        </form>
      </td>
    </tr>
    <tr>
      <td align="center">
        <input type="hidden" id="copyAction">
        <button dojoType="dijit.form.Button" onclick="dijit.byId('dialogCopy').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button dojoType="dijit.form.Button" type="submit" id="dialogCopySubmit" onclick="copyObjectToSubmit();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>

<div id="dialogCopyProject" dojoType="dijit.Dialog" title="<?php echo i18n("dialogCopyProject");?>">
  <table>
    <tr>
      <td>
       <form dojoType="dijit.form.Form" id='copyProjectForm' name='copyProjectForm' onSubmit="return false;">
         <input id="copyProjectId" name="copyProjectId" type="hidden" value="" />
         <table>
           <tr>
             <td class="dialogLabel"  >
               <label for="copyProjectToType" ><?php echo i18n("colProjectType") ?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <select dojoType="dijit.form.FilteringSelect" 
                id="copyProjectToType" name="copyProjectToType" required
                class="input" value="" >
                <?php htmlDrawOptionForReference('idProjectType', null, null, true);?>
               </select>
             </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
           <tr>
             <td class="dialogLabel" >
               <label for="copyProjectToName" ><?php echo i18n("copyToName") ?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <div id="copyProjectToName" name="copyProjectToName" dojoType="dijit.form.ValidationTextBox"
                required="required"
                style="width: 400px;"
                trim="true" maxlength="100" class="input"
                value="">
               </div>     
             </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
           <tr>
             <td class="dialogLabel" colspan="2" style="width:100%; text-align: left;">
               <label for="copyProjectStructure" style="width:90%;text-align: right;"><?php echo i18n("copyProjectStructure") ?>&nbsp;:&nbsp;</label>
               <div id="copyProjectStructure" name="copyProjectStructure" dojoType="dijit.form.CheckBox" type="checkbox" 
                checked >
               </div>
             </td>
           </tr>
           <tr><td>&nbsp;</td><td >&nbsp;</td></tr>
         </table>
        </form>
      </td>
    </tr>
    <tr>
      <td align="center">
        <input type="hidden" id="copyProjectAction">
        <button dojoType="dijit.form.Button" onclick="dijit.byId('dialogCopyProject').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button dojoType="dijit.form.Button" type="submit" id="dialogProjectCopySubmit" onclick="copyProjectToSubmit();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>

<div id="dialogAttachement" dojoType="dijit.Dialog" title="<?php echo i18n("dialogAttachement");?>">
  <form id='attachementForm' name='attachementForm' 
  ENCTYPE="multipart/form-data" method=POST
  action="../tool/saveAttachement.php"
  target="resultPost"
  onSubmit="return saveAttachement();" >
    <input id="attachementId" name="attachementId" type="hidden" value="" />
    <input id="attachementRefType" name="attachementRefType" type="hidden" value="" />
    <input id="attachementRefId" name="attachementRefId" type="hidden" value="" />
    <table>
      <tr height="30px"> 
        <td class="dialogLabel" >
         <label for="attachementFile" ><?php echo i18n("colFile");?>&nbsp;:&nbsp;</label>
        </td>
        <td>
         <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $paramAttachementMaxSize;?>" />     
         <input MAX_FILE_SIZE="<?php echo $paramAttachementMaxSize;?>"
          dojoType="dojox.form.FileInput" type="file" 
          name="attachementFile" id="attachementFile" 
          cancelText="<?php echo i18n("buttonReset");?>"
          label="<?php echo i18n("buttonBrowse");?>"
          title="<?php echo i18n("helpSelectFile");?>" />
        </td>
      </tr>
      
      <tr> 
        <td class="dialogLabel" >
         <label for="attachementDescription" ><?php echo i18n("colDescription");?>&nbsp;:&nbsp;</label>
        </td>
        <td> 
         <textarea dojoType="dijit.form.Textarea" 
          id="attachementDescription" name="attachementDescription"
          style="width: 350px;"
          maxlength="4000"
          class="input">  
         </textarea>
         <textarea style="display:none" id="resultAck" name="resultAck"></textarea>      
        </td>
      </tr>
      <tr>
        <td colspan="2" align="center">
          <input type="hidden" id="dialogAttachementAction">
          <button dojoType="dijit.form.Button" onclick="dijit.byId('dialogAttachement').hide();">
            <?php echo i18n("buttonCancel");?>
          </button>
          <button id="dialogAttachementSubmit" dojoType="dijit.form.Button" type="submit"
           onclick="saveAttachement();">
            <?php echo i18n("buttonOK");?>
          </button>
        </td>
      </tr>
      <tr>
        <td colspan="2" align="center">
         <div style="display:none">
           <iframe name="resultPost" id="resultPost"></iframe>
         </div>
        </td>
      </tr>
    </table>
  </form>
</div>

<div id="dialogDocumentVersion" dojoType="dijit.Dialog" title="<?php echo i18n("dialogDocumentVersion");?>">
  <form id='documentVersionForm' name='documentVersionForm' jsId='documentVersionForm' 
  ENCTYPE="multipart/form-data" method=POST
  action="../tool/saveDocumentVersion.php"
  target="documentVersionPost"
  onSubmit="return saveDocumentVersion();" >
    <input id="documentVersionId" name="documentVersionId" type="hidden" value="" />
    <input id="documentVersionVersion" name="documentVersionVersion" type="hidden" value="" />
    <input id="documentVersionRevision" name="documentVersionRevision" type="hidden" value="" />
    <input id="documentVersionDraft" name="documentVersionDraft" type="hidden" value="" />
    <input id="documentVersionNewVersion" name="documentVersionNewVersion" type="hidden" value="" />
    <input id="documentVersionNewRevision" name="documentVersionNewRevision" type="hidden" value="" />
    <input id="documentVersionNewDraft" name="documentVersionNewDraft" type="hidden" value="" />
    <input id="documentId" name="documentId" type="hidden" value="" />
    <input id="documentVersionMode" name="documentVersionMode" type="hidden" value="" />
    <input id="typeEvo" name="typeEvo" type="hidden" value="" />
<div id="inputFileDocumentVersion" name="inputFileDocumentVersion">
    <table>
      <tr height="30px"> 
        <td class="dialogLabel" >
         <label for="documentVersionFile" ><?php echo i18n("colFile");?>&nbsp;:&nbsp;</label>
        </td>
        <td>
         <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $paramAttachementMaxSize;?>" />     
         <input MAX_FILE_SIZE="<?php echo $paramAttachementMaxSize;?>"
          dojoType="dojox.form.FileInput" type="file" 
          name="documentVersionFile" id="documentVersionFile" 
          cancelText="<?php echo i18n("buttonReset");?>"
          label="<?php echo i18n("buttonBrowse");?>"
          title="<?php echo i18n("helpSelectFile");?>" />
        </td>
      </tr>
      <tr><td colspan="2"><div style="display:none"><table>
      <tr> 
        <td class="dialogLabel" >
         <label for="documentVersionLink" ><?php echo i18n("colOrLink");?>&nbsp;:&nbsp;</label>
        </td>
        <td> 
         <div dojoType="dijit.form.TextBox" 
          id="documentVersionLink" name="documentVersionLink"
          style="width: 450px;"
          maxlength="400"
          class="input">  
         </div>  
        </td>
      </tr>
      </table></div></td></tr>
    </table>
</div>
    <table>
      <tr> 
        <td class="dialogLabel" >
         <label for="documentVersionVersionDisplay" ><?php echo i18n("colCurrentDocumentVersion");?>&nbsp;:&nbsp;</label>
        </td>
        <td> 
         <div dojoType="dijit.form.TextBox" 
          id="documentVersionVersionDisplay" name="documentVersionVersionDisplay"
          style="width: 450px;" readonly
          maxlength="100"
          class="input">  
         </div>  
        </td>
      </tr>            
      <tr style="height:21px">
        <td class="dialogLabel" >
         <label for="documentVersionUpdate" ><?php echo i18n("documentVersionUpdate");?>&nbsp;:&nbsp;</label>
        </td>
        <td>
          <table><tr>
            <td style="text-align:right; width:5%;">
              <label class="smallRadioLabel" for="documentVersionUpdateMajor"><?php echo i18n('versionMajorUpdate');?>&nbsp;</label>
            </td><td style="text-align:left;"> 
              <input onChange="calculateNewVersion();" type="radio" dojoType="dijit.form.RadioButton" 
			         name="documentVersionUpdate" id="documentVersionUpdateMajor"
			         checked value="major" style="background-color:white;"/>
            </td>
            <td style="text-align:right; width:5%">
              <label class="smallRadioLabel" for="documentVersionUpdateMinor"><?php echo i18n('versionMinorUpdate');?>&nbsp;</label>
            </td><td style="text-align:left;">    
			        <input onChange="calculateNewVersion();" type="radio" dojoType="dijit.form.RadioButton" 
			         name="documentVersionUpdate" id="documentVersionUpdateMinor"
			         value="minor" style="background-color:white;"/>
			      </td>
            <td style="text-align:right; width:5%">
              <label class="smallRadioLabel" for="documentVersionUpdateNo"><?php echo i18n('versionNoUpdate');?>&nbsp;</label>
            </td><td style="text-align:left;">    
              <input onChange="calculateNewVersion();" type="radio" dojoType="dijit.form.RadioButton" 
               name="documentVersionUpdate" id="documentVersionUpdateNo"
               value="no" style="background-color:white;"/>
            </td>
            <td style="text-align:right; width:5%">
              <label class="smallRadioLabel" for="documentVersionUpdateDraft"><?php echo i18n('versionDraftUpdate');?>&nbsp;</label>
            </td>
            <td style="text-align:right; width:5%">
		        <input onChange="calculateNewVersion();" type="radio" dojoType="dijit.form.CheckBox" 
		         name="documentVersionUpdateDraft" id="documentVersionUpdateDraft"
		         value="draft" style="background-color:white;"/>
		        </td>
		      </tr></table>
         </td> 
      </tr>
      <tr> 
        <td class="dialogLabel" >
         <label for="documentVersionNewVersionDisplay" ><?php echo i18n("colNextDocumentVersion");?>&nbsp;:&nbsp;</label>
        </td>
        <td> 
         <div dojoType="dijit.form.TextBox" 
          id="documentVersionNewVersionDisplay" name="documentVersionNewVersionDisplay"
          style="width: 450px;" readonly
          maxlength="100"
          class="input">  
         </div>  
        </td>
      </tr>   
      <tr>
        <td class="dialogLabel" >
          <label for="documentVersionDate" ><?php echo i18n("colDate");?>&nbsp;:&nbsp;</label>
        </td>
        <td>
          <div id="documentVersionDate" name="documentVersionDate"
           dojoType="dijit.form.DateTextBox" 
           invalidMessage="<?php echo i18n('messageInvalidDate');?> " 
           type="text" maxlength="10" 
           style="width:100px; text-align: center;" class="input"
           required="true"
           hasDownArrow="true" 
           onchange="calculateNewVersion(false);"
           missingMessage="<?php echo i18n('messageMandatory',array('colDate'));?>" 
           invalidMessage="<?php echo i18n('messageMandatory',array('colDate'));?>" 
          >
          </div>
        </td>
      </tr>
      <tr>
        <td class="dialogLabel" >
          <label for="documentVersionIdStatus" ><?php echo i18n("colIdStatus");?>&nbsp;:&nbsp;</label>
        </td>
        <td>
          <select dojoType="dijit.form.FilteringSelect" 
                id="documentVersionIdStatus" name="documentVersionIdStatus"
                class="input" value="" 
                onChange=""
                missingMessage="<?php echo i18n('messageMandatory',array(i18n('colIdStatus')));?>" >
                 <?php //htmlDrawOptionForReference('idStatus', null, null, true);?>
          </select>  
        </td>
      </tr>
      <tr>
        <td class="dialogLabel" >
          <label for="documentVersionIsRef" ><?php echo i18n("colIsRef");?>&nbsp;:&nbsp;</label>
        </td>
        <td>
          <table><tr><td>
          <input dojoType="dijit.form.CheckBox" 
           name="documentVersionIsRef" id="documentVersionIsRef"
           style="background-color:white;"
           onclick="setDisplayIsRefDocumentVersion();"
           />
           </td><td>
          <span id="documentVersionIsRefDisplay" style="font-size:80%"><i><?php echo i18n('documentVersionIsRef');?>&nbsp;</i></span>
          </tr></table>
        </td>     
      </tr>          
      <tr> 
        <td class="dialogLabel" >
         <label for="documentVersionDescription" ><?php echo i18n("colDescription");?>&nbsp;:&nbsp;</label>
        </td>
        <td> 
         <textarea dojoType="dijit.form.Textarea" 
          id="documentVersionDescription" name="documentVersionDescription"
          style="width: 450px;"
          maxlength="4000"
          class="input">  
         </textarea><textarea style="display:none" id="documentVersionAck" name="documentVersionAck"></textarea>      
        </td>
      </tr>
      <tr>
        <td colspan="2" align="center">
          <input type="hidden" id="dialogDocumentVersionAction">
          <button dojoType="dijit.form.Button" onclick="dijit.byId('dialogDocumentVersion').hide();">
            <?php echo i18n("buttonCancel");?>
          </button>
          <button id="submitDocumentVersionUpload" dojoType="dijit.form.Button" type="submit" 
           onclick="saveDocumentVersion();">
            <?php echo i18n("buttonOK");?>
          </button>
        </td>
      </tr>
      <tr>
        <td colspan="2" align="center">
         <div style="display:none">
           <iframe name="documentVersionPost" id="documentVersionPost"></iframe>
         </div>
        </td>
      </tr>
    </table>
  </form>
</div>
  
<div id="dialogAssignment" dojoType="dijit.Dialog" title="<?php echo i18n("dialogAssignment");?>">
  <table>
    <tr>
      <td>
       <form dojoType="dijit.form.Form" id='assignmentForm' jsid='assignmentForm' name='assignmentForm' onSubmit="return false;">
         <input id="assignmentId" name="assignmentId" type="hidden" value="" />
         <input id="assignmentRefType" name="assignmentRefType" type="hidden" value="" />
         <input id="assignmentRefId" name="assignmentRefId" type="hidden" value="" />
         <table>
           <tr>
             <td class="dialogLabel" >
               <label for="assignmentIdResource" ><?php echo i18n("colIdResource");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
              <select dojoType="dijit.form.FilteringSelect"
                id="assignmentIdResource" name="assignmentIdResource"
                class="input" value="" 
                onChange="assignmentChangeResource();"
                missingMessage="<?php echo i18n('messageMandatory',array(i18n('colIdResource')));?>" >
                 <?php htmlDrawOptionForReference('idResource', null, null, true);?>
               </select>  
             </td>
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="assignmentIdRole" ><?php echo i18n("colIdRole");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
              <select dojoType="dijit.form.FilteringSelect" 
                id="assignmentIdRole" name="assignmentIdRole"
                class="input" value="" 
                onChange="assignmentChangeRole();" >                
                 <?php htmlDrawOptionForReference('idRole', null, null, true);?>            
               </select>  
             </td>
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="assignmentDailyCost" ><?php echo i18n("colCost");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <?php echo ($currencyPosition=='before')?$currency:''; ?>
               <input id="assignmentDailyCost" name="assignmentDailyCost" value="" 
                 dojoType="dijit.form.NumberTextBox" 
                 constraints="{min:0}" 
                 style="width:97px" 
                 
                 readonly />
               <?php echo ($currencyPosition=='after')?$currency:'';
                     echo " / ";
                     echo i18n('shortDay'); ?>
             </td>
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="assignmentRate" ><?php echo i18n("colRate");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <input id="assignmentRate" name="assignmentRate" value="" 
                 dojoType="dijit.form.NumberTextBox" 
                 constraints="{min:0,max:999}" 
                 style="width:97px" 
                 missingMessage="<?php echo i18n('messageMandatory',array(i18n('colRate')));?>" 
                 required="true" />
             </td>
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="assignmentAssignedWork" ><?php echo i18n("colAssignedWork");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <input id="assignmentAssignedWork" name="assignmentAssignedWork" value="" 
                 dojoType="dijit.form.NumberTextBox" 
                 constraints="{min:0,max:9999.99}" 
                 style="width:97px"
                 onchange="assignmentUpdateLeftWork('assignment');"
                 onblur="assignmentUpdateLeftWork('assignment');" />
               <input id="assignmentAssignedUnit" name="assignmentAssignedUnit" value="" readonly tabindex="-1"
                 xdojoType="dijit.form.TextBox" 
                 class="display" style="width:15px; background-color:white; color:#000000; border:0px;"/>
               <input type="hidden" id="assignmentAssignedWorkInit" name="assignmentAssignedWorkInit" value="" 
                 style="width:97px"/>  
             </td>    
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="assignmentRealWork" ><?php echo i18n("colRealWork");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <input id="assignmentRealWork" name="assignmentRealWork" value=""  
                 dojoType="dijit.form.NumberTextBox" 
                 constraints="{min:0,max:9999.99}" 
                 style="width:97px" readonly />
               <input id="assignmentRealUnit" name="assignmentRealUnit" value="" readonly tabindex="-1"
                 xdojoType="dijit.form.TextBox" 
                 class="display" style="width:15px;background-color:#FFFFFF; color:#000000; border:0px;"/>
             </td>
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="assignmentLeftWork" ><?php echo i18n("colLeftWork");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <input id="assignmentLeftWork" name="assignmentLeftWork" value=""  
                 dojoType="dijit.form.NumberTextBox" 
                 constraints="{min:0,max:9999.99}" 
                 onchange="assignmentUpdatePlannedWork('assignment');"
                 onblur="assignmentUpdatePlannedWork('assignment');"  
                 style="width:97px" />
               <input id="assignmentLeftUnit" name="assignmentLeftUnit" value="" readonly tabindex="-1"
                 xdojoType="dijit.form.TextBox" 
                 class="display" style="width:15px;background-color:#FFFFFF; color:#000000; border:0px;"/>
               <input type="hidden" id="assignmentLeftWorkInit" name="assignmentLeftWorkInit" value="" 
                 style="width:97px"/>  
             </td>
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="assignmentPlannedWork" ><?php echo i18n("colPlannedWork");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <input id="assignmentPlannedWork" name="assignmentPlannedWork" value=""  
                 dojoType="dijit.form.NumberTextBox" 
                 constraints="{min:0,max:9999.99}" 
                 style="width:97px" readonly /> 
               <input id="assignmentPlannedUnit" name="assignmentPlannedUnit" value="" readonly tabindex="-1"
                 xdojoType="dijit.form.TextBox" 
                 class="display" style="width:15px;background-color:#FFFFFF; border:0px;"/>
             </td>
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="assignmentComment" ><?php echo i18n("colComment");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <input id="assignmentComment" name="assignmentComment" value=""  
                 dojoType="dijit.form.Textarea"
                 class="input" 
                 /> 
             </td>
           </tr>
         </table>
        </form>
      </td>
    </tr>
    <tr>
      <td align="center">
        <input type="hidden" id="dialogAssignmentAction">
        <button dojoType="dijit.form.Button" onclick="dijit.byId('dialogAssignment').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button dojoType="dijit.form.Button" id="dialogAssignmentSubmit" type="submit" onclick="saveAssignment();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>

<div id="dialogExpenseDetail" dojoType="dijit.Dialog" title="<?php echo i18n("dialogExpenseDetail");?>">
  <table>
    <tr>
      <td>
       <form dojoType="dijit.form.Form" id='expenseDetailForm' jsid='expenseDetailForm' name='expenseDetailForm' onSubmit="return false;">
         <input id="expenseDetailId" name="expenseDetailId" type="hidden" value="" />
         <input id="idExpense" name="idExpense" type="hidden" value="" />
         <table>
           <tr>
             <td class="dialogLabel" >
               <label for="expenseDetailName" ><?php echo i18n("colName");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <input id="expenseDetailName" name="expenseDetailName" value="" 
                 dojoType="dijit.form.TextBox" 
                 style="width:400px" 
                 required="true" 
                 missingMessage="<?php echo i18n('messageMandatory',array('colName'));?>" 
                 invalidMessage="<?php echo i18n('messageMandatory',array('colName'));?>"              
               />
             </td>
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="expenseDetailDate" ><?php echo i18n("colDate");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <div id="expenseDetailDate" name="expenseDetailDate"
                 dojoType="dijit.form.DateTextBox" 
                 invalidMessage="<?php echo i18n('messageInvalidDate');?> " 
                 type="text" maxlength="10" 
                 style="width:100px; text-align: center;" class="input"
                 required="true"
                 hasDownArrow="true" 
                 missingMessage="<?php echo i18n('messageMandatory',array('colDate'));?>" 
                 invalidMessage="<?php echo i18n('messageMandatory',array('colDate'));?>" 
                 >
             </div>
             </td>
           </tr>
 
           <tr>
             <td class="dialogLabel" >
               <label for="expenseDetailType" ><?php echo i18n("colType");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
              <select dojoType="dijit.form.FilteringSelect" 
                id="expenseDetailType" name="expenseDetailType"
                style="width:200px" 
                class="input" value="" 
                onChange="expenseDetailTypeChange();" >                
                 <?php htmlDrawOptionForReference('idExpenseDetailType', null, null, true);?>            
               </select>  
             </td>
           </tr>
           <tr>
            <td colspan="2">
              <div id="expenseDetailDiv" dojoType="dijit.layout.ContentPane" region="center" >    
              </div>
            </td> 
           </tr>

           <tr>
             <td class="dialogLabel" >
               <label for="expenseDetailAmount" ><?php echo i18n("colAmount");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <?php echo ($currencyPosition=='before')?$currency:''; ?>
               <input id="expenseDetailAmount" name="expenseDetailAmount" value="" 
                 dojoType="dijit.form.NumberTextBox" 
                 constraints="{min:0}" 
                 style="width:97px"
                 readonly="readonly"              
               />
               <?php echo ($currencyPosition=='after')?$currency:'';?>
             </td>
           </tr> 
         </table>
        </form>
      </td>
    </tr>
    <tr>
      <td align="center">
        <input type="hidden" id="dialogExpenseDetailAction">
        <button dojoType="dijit.form.Button" onclick="dijit.byId('dialogExpenseDetail').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button dojoType="dijit.form.Button" type="submit" id="dialogExpenseDetailSubmit" onclick="saveExpenseDetail();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>

<div id="dialogPlan" dojoType="dijit.Dialog" title="<?php echo i18n("dialogPlan");?>">
  <table>
    <tr>
      <td>
       <form id='dialogPlanForm' name='dialogPlanForm' onSubmit="return false;">
         <table>
           <tr>
             <td class="dialogLabel"  >
               <label for="idProjectPlan" ><?php echo i18n("colIdProject") ?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <select dojoType="dijit.form.FilteringSelect" 
                id="idProjectPlan" name="idProjectPlan" 
                missingMessage="<?php echo i18n('messageMandatory',array(i18n('colIdProject')));?>" 
                class="input" value="" >
                 <?php 
                    $proj=null; 
                    if (array_key_exists('project',$_SESSION)) {
                        $proj=$_SESSION['project'];
                    }
                    htmlDrawOptionForReference('idProject', $proj, null, false);
                 ?>
               </select>
             </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
           <tr>
             <td class="dialogLabel"  >
               <label for="startDatePlan" ><?php echo i18n("colStartDate") ?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <div dojoType="dijit.form.DateTextBox" 
                 id="startDatePlan" name="startDatePlan" 
                 invalidMessage="<?php echo i18n('messageInvalidDate')?>" 
                 type="text" maxlength="10"
                 style="width:100px; text-align: center;" class="input"
                 required="true"
                 hasDownArrow="true"
                 missingMessage="<?php echo i18n('messageMandatory',array(i18n('colStartDate')));?>"
                 value="<?php echo date('Y-m-d');?>" >
               </div>
             </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
         </table>
        </form>
      </td>
    </tr>
    <tr>
      <td align="center">
        <input type="hidden" id="dialogPlanAction">
        <button dojoType="dijit.form.Button" onclick="dijit.byId('dialogPlan').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button dojoType="dijit.form.Button" type="submit" id="dialogPlanSubmit" onclick="plan();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>


<div id="dialogDependency" dojoType="dijit.Dialog" title="<?php echo i18n("dialogDependency");?>">
  <table>
    <tr>
      <td>
       <form id='dependencyForm' name='dependencyForm' onSubmit="return false;">
         <input id="dependencyId" name="dependencyId" type="hidden" value="" />
         <input id="dependencyRefType" name="dependencyRefType" type="hidden" value="" />
         <input id="dependencyRefId" name="dependencyRefId" type="hidden" value="" />
         <input id="dependencyType" name="dependencyType" type="hidden" value="" />
         <table>
           <tr>
             <td class="dialogLabel"  >
               <label for="dependencyRefTypeDep" ><?php echo i18n("linkType") ?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <select dojoType="dijit.form.FilteringSelect" 
                id="dependencyRefTypeDep" name="dependencyRefTypeDep" 
                onchange="refreshDependencyList();"
                missingMessage="<?php echo i18n('messageMandatory',array(i18n('linkType')));?>"
                class="input" value="" >
                 <?php htmlDrawOptionForReference('idDependable', null, null, true);?>
               </select>
             </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
           <tr>
             <td class="dialogLabel" >
               <label for="dependencyRefIdDep" ><?php echo i18n("linkElement") ?>&nbsp;:&nbsp;</label>
             </td>
             <td><table><tr><td>
               <div id="dialogDependencyList" dojoType="dijit.layout.ContentPane" region="center">
                 <input id="dependencyRefIdDep" name="dependencyRefIdDep" type="hidden" value="" />
                  OK
               </div>
               </td><td style="vertical-align: top">
               <button id="dependencyDetailButton" dojoType="dijit.form.Button" showlabel="false"
                 title="<?php echo i18n('showDetail')?>"
                 iconClass="iconView">
                 <script type="dojo/connect" event="onClick" args="evt">
                    showDetailDependency();
                 </script>
               </button>
               </td></tr></table>
             </td>
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
         </table>
        </form>
      </td>
    </tr>
    <tr>
      <td align="center">
        <input type="hidden" id="dialogDependencyAction">
        <button dojoType="dijit.form.Button" onclick="dijit.byId('dialogDependency').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button dojoType="dijit.form.Button" type="submit" id="dialogDependencySubmit" onclick="saveDependency();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>

<div id="dialogResourceCost" dojoType="dijit.Dialog" title="<?php echo i18n("dialogResourceCost");?>">
  <table>
    <tr>
      <td>
       <form dojoType="dijit.form.Form" id='resourceCostForm' jsid='resourceCostForm' name='resourceCostForm' onSubmit="return false;">
         <input id="resourceCostId" name="resourceCostId" type="hidden" value="" />
         <input id="resourceCostIdResource" name="resourceCostIdResource" type="hidden" value="" />
         <input id="resourceCostFunctionList" name="resourceCostFunctionList" type="hidden" value="" />
         <table>
           <tr>
             <td class="dialogLabel" >
               <label for="resourceCostIdRole" ><?php echo i18n("colIdRole");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
              <select dojoType="dijit.form.FilteringSelect" 
                id="resourceCostIdRole" name="resourceCostIdRole"
                class="input" value=""
                onChange="resourceCostUpdateRole();"
                missingMessage="<?php echo i18n('messageMandatory',array(i18n('colIdRole')));?>" >
                 <?php htmlDrawOptionForReference('idRole', null, null, true);?>
               </select>  
             </td>
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="resourceCostValue" ><?php echo i18n("colCost");?>&nbsp;:&nbsp;</label>
             </td>
             <td><nobr>
               <?php echo ($currencyPosition=='before')?$currency:''; ?>
               <input id="resourceCostValue" name="resourceCostValue" value="" 
                 dojoType="dijit.form.NumberTextBox" 
                 constraints="{min:0}" 
                 style="width:97px; text-align: right;" 
                 missingMessage="<?php echo i18n('messageMandatory',array(i18n('colCost')));?>" 
                 required="true" />
               <?php echo ($currencyPosition=='after')?$currency:'';
                     echo " / ";
                     echo i18n('shortDay'); ?>
               </nobr>
             </td>
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="resourceCostStartDate" ><?php echo i18n("colStartDate");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <div id="resourceCostStartDate" name="resourceCostStartDate" value="" 
                 dojoType="dijit.form.DateTextBox" 
                 style="width:100px" class="input"
                 hasDownArrow="true"
               >
               </div>
             </td>    
           </tr>
         </table>
        </form>
      </td>
    </tr>
    <tr>
      <td align="center">
        <input type="hidden" id="dialogResourceCostAction">
        <button dojoType="dijit.form.Button" onclick="dijit.byId('dialogResourceCost').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button dojoType="dijit.form.Button" type="submit" id="dialogResourceCostSubmit" onclick="saveResourceCost();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>


<div id="dialogVersionProject" dojoType="dijit.Dialog" title="<?php echo i18n("dialogVersionProject");?>">
  <table>
    <tr>
      <td>
       <form dojoType="dijit.form.Form" id='versionProjectForm' name='versionProjectForm' onSubmit="return false;">
         <input id="versionProjectId" name="versionProjectId" type="hidden" value="" />
         <table>
           <tr>
             <td class="dialogLabel"  >
               <label for="versionProjectProject" ><?php echo i18n("colIdProject") ?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <select dojoType="dijit.form.FilteringSelect" 
                id="versionProjectProject" name="versionProjectProject" 
                class="input" value="" required="required">
                 <?php htmlDrawOptionForReference('idProject', null, null, true);?>
               </select>
             </td>
           </tr>
           <tr>
             <td class="dialogLabel"  >
               <label for="versionProjectVersion" ><?php echo i18n("colIdVersion") ?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <select dojoType="dijit.form.FilteringSelect" 
                id="versionProjectVersion" name="versionProjectVersion" 
                class="input" value="" required="required">
                 <?php htmlDrawOptionForReference('idVersion', null, null, true);?>
               </select>
             </td>
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="versionProjectStartDate" ><?php echo i18n("colStartDate");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <div id="versionProjectStartDate" name="versionProjectStartDate" value="" 
                 dojoType="dijit.form.DateTextBox" 
                 style="width:100px" class="input"
                 hasDownArrow="true"
               >
               </div>
             </td>    
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="versionProjectEndDate" ><?php echo i18n("colEndDate");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <div id="versionProjectEndDate" name="versionProjectEndDate" value="" 
                 dojoType="dijit.form.DateTextBox" 
                 style="width:100px" class="input"
                 hasDownArrow="true"
               >
               </div>
             </td>    
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="versionProjectIdle" ><?php echo i18n("colIdle");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <div id="versionProjectIdle" name="versionProjectIdle"
                 dojoType="dijit.form.CheckBox" type="checkbox" >
               </div>
             </td>    
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
         </table>
        </form>
      </td>
    </tr>
    <tr>
      <td align="center">
        <input type="hidden" id="versionProjectAction">
        <button dojoType="dijit.form.Button" onclick="dijit.byId('dialogVersionProject').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button dojoType="dijit.form.Button" type="submit" id="dialogVersionProjectSubmit" onclick="saveVersionProject();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>

<div id="dialogAffectation" dojoType="dijit.Dialog" title="<?php echo i18n("dialogAffectation");?>">
  <table>
    <tr>
      <td>
       <form dojoType="dijit.form.Form" id='affectationForm' name='affectationForm' onSubmit="return false;">
         <input id="affectationId" name="affectationId" type="hidden" value="" />
         <input id="affectationIdTeam" name="affectationIdTeam" type="hidden" value="" />
         <table>
           <tr>
             <td class="dialogLabel"  >
               <label for="affectationProject" ><?php echo i18n("colIdProject") ?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <select dojoType="dijit.form.FilteringSelect" 
                id="affectationProject" name="affectationProject" 
                class="input" value="" required="required">
                 <?php htmlDrawOptionForReference('idProject', null, null, true);?>
               </select>
             </td>
           </tr>
           <tr>
             <td class="dialogLabel"  >
               <label for="affectationResource" ><?php echo i18n("colIdResource") ?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <select dojoType="dijit.form.FilteringSelect" 
                id="affectationResource" name="affectationResource" 
                class="input" value="" required="required">
                 <?php htmlDrawOptionForReference('idResource', null, null, true);?>
               </select>
             </td>
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="affectationRate" ><?php echo i18n("colRate");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <div id="affectationRate" name="affectationRate" value="" 
                 dojoType="dijit.form.NumberTextBox" 
                 style="width:100px" class="input"
                 hasDownArrow="true"
               >
               </div>
             </td>    
           </tr>
           <tr>
             <td class="dialogLabel" >
               <label for="affectationIdle" ><?php echo i18n("colIdle");?>&nbsp;:&nbsp;</label>
             </td>
             <td>
               <div id="affectationIdle" name="affectationIdle"
                 dojoType="dijit.form.CheckBox" type="checkbox" >
               </div>
             </td>    
           </tr>
           <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
         </table>
        </form>
      </td>
    </tr>
    <tr>
      <td align="center">
        <input type="hidden" id="affectationAction">
        <button dojoType="dijit.form.Button" onclick="dijit.byId('dialogAffectation').hide();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button dojoType="dijit.form.Button" type="submit" id="dialogAffectationSubmit" onclick="saveAffectation();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>

<div id="dialogFilter" dojoType="dijit.Dialog" title="<?php echo i18n("dialogFilter");?>" style="top: 100px;">
  <table>
    <tr>
     <td class="section"><?php echo i18n("sectionStoredFilters");?></td>
    </tr>
    <tr>
      <td>
        <div id='listStoredFilters' dojoType="dijit.layout.ContentPane" region="center"></div>
      </td>
    </tr>
    <tr><td>&nbsp;</td></tr>
  </table>
  <table style="border: 1px solid grey;">
    <tr>
     <td class="section"><?php echo i18n("sectionActiveFilter");?></td>
    </tr>
    <tr>
      <td style="margin: 2px;"> 
        <div id='listFilterClauses' dojoType="dijit.layout.ContentPane" region="center" style="overflow: hidden"></div>
         
        <form id='dialogFilterForm' name='dialogFilterForm' onSubmit="return false;">
         <input type="hidden" id="filterObjectClass" name="filterObjectClass" />
         <input type="hidden" id="filterClauseId" name="filterClauseId" />
         <input type="hidden" id="filterDataType" name="filterDataType" />
         <input type="hidden" id="filterName" name="filterName" />
         <table width="100%" style="border: 1px solid grey;">
           <tr><td colspan="4" class="filterHeader"><?php echo i18n("addFilterClauseTitle");?></td></tr>
           <tr style="vertical-align: top;">
             <td style="width: 210px;" >
               <div dojoType="dojo.data.ItemFileReadStore" jsId="attributeStore" url="../tool/jsonList.php?listType=empty" searchAttr="name" >
               </div>
               <select dojoType="dijit.form.FilteringSelect" 
                id="idFilterAttribute" name="idFilterAttribute" 
                missingMessage="<?php echo i18n('attributeNotSelected');?>"
                class="input" value="" style="width: 200px;" store="attributeStore">
                  <script type="dojo/method" event="onChange" >
                    filterSelectAtribute(this.value);
                  </script>              
               </select>
             </td>
             <td style="width: 110px;">
               <div dojoType="dojo.data.ItemFileReadStore" jsId="operatorStore" url="../tool/jsonList.php?listType=empty" searchAttr="name" >
               </div>
               <select dojoType="dijit.form.FilteringSelect" 
                id="idFilterOperator" name="idFilterOperator" 
                missingMessage="<?php echo i18n('valueNotSelected');?>"
                class="input" value="" style="width: 100px;" store="operatorStore">
                  <script type="dojo/method" event="onChange" >
                    filterSelectOperator(this.value);
                  </script>        
               </select>
             </td>
             <td style="width:210px;">
               <input id="filterValue" name="filterValue" value=""  
                 dojoType="dijit.form.TextBox" 
                 style="width:200px" />
               <select id="filterValueList" name="filterValueList[]" value=""  
                 dojoType="dijit.form.MultiSelect" multiple
                 style="width:200px" size="10" class="input"></select>
                <input type="checkbox" id="filterValueCheckbox" name="filterValueCheckbox" value=""  
                 dojoType="dijit.form.CheckBox" 
                 /> 
                <input id="filterValueDate" name="filterValueDate" value=""  
                 dojoType="dijit.form.DateTextBox" 
                 style="width:200px" />
                 <select id="filterSortValueList" name="filterSortValueList" value="asc"  
                 dojoType="dijit.form.FilteringSelect"
                 missingMessage="<?php echo i18n('valueNotSelected');?>" 
                 style="width:200px" size="10" class="input">
                  <option value="asc" SELECTED><?php echo i18n('sortAsc');?></option>
                  <option value="desc"><?php echo i18n('sortDesc');?></option>
                 </select> 
             </td>
             <td style="width:25px; text-align: center;" align="center">
               <img src="css/images/smallButtonAdd.png" onClick="addfilterClause();" title="<?php echo i18n('addFilterClause');?>" class="smallButton"/> 
             </td>
           </tr>
         </table>
        </form>
      </td>
    </tr>
    <tr>
      <td align="center">
        <button dojoType="dijit.form.Button" onclick="defaultFilter();">
          <?php echo i18n("buttonDefault");?>
        </button>
        <button dojoType="dijit.form.Button" onclick="clearFilter();">
          <?php echo i18n("buttonClear");?>
        </button>
        <button dojoType="dijit.form.Button" onclick="cancelFilter();">
          <?php echo i18n("buttonCancel");?>
        </button>
        <button dojoType="dijit.form.Button" type="submit" id="dialogFilterSubmit" onclick="selectFilter();return false;">
          <?php echo i18n("buttonOK");?>
        </button>
      </td>
    </tr>
  </table>
</div>
</body>

</html>
