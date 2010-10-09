<?php
/* ============================================================================
 * Presents the list of objects of a given class.
 *
 */
require_once "../tool/projector.php";
scriptLog('   ->/view/imputationList.php');

$user=$_SESSION['user'];
$rangeType='week';
$currentWeek=weekNumber(date('Y-m-d')) ;
$currentYear=strftime("%Y") ;
$rangeValue=$currentYear . $currentWeek;

?>

  
<div dojoType="dijit.layout.BorderContainer">
  <div dojoType="dijit.layout.ContentPane" region="top" id="imputationButtonDiv" class="dojoxGridRowSelected" >
  <table width="100%" height="27px" class="dojoxGridRowSelected" >
    <tr height="27px">
      <td width="50px" align="center">
        <img src="css/images/iconImputation32.png" width="32" height="32" />
      </td>
      <td width="200px" ><span class="title"><?php echo i18n('menuImputation');?></span></td>
      <td>   
          <table style="width: 100%; ">
            <tr>
              <td nowrap="nowrap">
                <?php echo i18n("colIdResource");?> 
                <select dojoType="dijit.form.FilteringSelect" class="input" 
                  style="width: 150px;"
                  name="userName" id="userName"
                  value="<?php echo ($user->isResource)?$user->id:' ';?>" 
                  >
                  <script type="dojo/method" event="onChange" >
                    refreshImputationList();
                  </script>
                  <?php 
                    if ( $user->isResource ) { 
                      htmlDrawOptionForReference('idResource', $user->id, null, true);
                    } else {
                      htmlDrawOptionForReference('idResource', null, null, false);
                    }?>  
                </select>
                &nbsp;&nbsp;<?php echo i18n("year");?>
                <div style="width:70px; text-align: center; color: #000000;" 
                  dojoType="dijit.form.NumberSpinner" 
                  constraints="{min:2000,max:2100,places:0,pattern:'###0'}"
                  intermediateChanges="true"
                  maxlength="4"
                  value="<?php echo $currentYear;?>" smallDelta="1"
                  id="yearSpinner" name="yearSpinner" >
                  <script type="dojo/method" event="onChange" >
                   return refreshImputationPeriod();
                  </script>
                </div>
                &nbsp;&nbsp;<?php echo i18n("week");?>
                <div style="width:55px; text-align: center; color: #000000;" 
                  dojoType="dijit.form.NumberSpinner" 
                  constraints="{min:1,max:55,places:0,pattern:'00'}"
                  intermediateChanges="true"
                  maxlength="2"
                  value="<?php echo $currentWeek;?>" smallDelta="1"
                  id="weekSpinner" name="weekSpinner" >
                  <script type="dojo/method" event="onChange" >
                   return refreshImputationPeriod();
                  </script>
                </div>
              </td>
              <td style="width: 200px;text-align: right; align: right;">
                <?php echo i18n("labelShowIdle");?>
                <div title="<?php echo i18n('showIdleElements')?>" dojoType="dijit.form.CheckBox" type="checkbox" id="listShowIdle" name="listShowIdle">
                  <script type="dojo/method" event="onChange" >
                    return refreshImputationList();
                  </script>
                </div>&nbsp;
              </td>
            </tr>

          </table>    
      </td>
    </tr>
  </table>
  <table width="100%" height="27px" class="dojoxGridRowSelected" >
    <tr>
      <td width="50px" >
        &nbsp;
      </td>
      <td> 
        <button id="saveParameterButton" dojoType="dijit.form.Button" showlabel="false"
          title="<?php echo i18n('buttonSaveImputation');?>"
          iconClass="dijitEditorIcon dijitEditorIconSave" >
            <script type="dojo/connect" event="onClick" args="evt">
              submitForm("../tool/saveImputation.php","resultDiv", "listForm", true);
            </script>
        </button>
        <button title="<?php echo i18n('print')?>"  
         dojoType="dijit.form.Button" 
         id="printButton" name="printButton"
         iconClass="dijitEditorIcon dijitEditorIconPrint" showLabel="false">
          <script type="dojo/connect" event="onClick" args="evt">
            showPrint('../report/imputation.php', 'imputation');
          </script>
        </button>
        <button id="undoButton" dojoType="dijit.form.Button" showlabel="false"
         title="<?php echo i18n('buttonUndoImputation');?>"
         "disabled"
         iconClass="dijitEditorIcon dijitEditorIconUndo" >
          <script type="dojo/connect" event="onClick" args="evt">
            formChangeInProgress=false;
            refreshImputationList();
          </script>
        </button>    
        <div dojoType="dijit.Tooltip" connectId="saveButton"><?php echo i18n("buttonSaveImputation")?></div>
      </td>
      <td >
        <div style="width: 80%; margin: 0px 8px 4px 8px; padding: 5px;" id="resultDiv" dojoType="dijit.layout.ContentPane" region="center" >
        </div>       
      </td>
      <td width="10%" style="text-align: right; align: right;" NOWRAP>
        <?php echo i18n("labelShowPlannedWork");?>
        <div title="<?php echo i18n('showPlannedWork')?>" dojoType="dijit.form.CheckBox" type="checkbox" id="listShowPlannedWork" name="listShowPlannedWork" checked>
          <script type="dojo/method" event="onChange" >
            return refreshImputationList();
          </script>
        </div>&nbsp;
      </td>
    </tr>
  </table>
  </div>
  <div style="position:relative;" dojoType="dijit.layout.ContentPane" region="center" id="workDiv" name="workDiv">
     <form dojoType="dijit.form.Form" id="listForm" action="" method="post" >
       <input type="hidden" name="userId" id="userId" value="<?php echo $user->id;?>"/>
       <input type="hidden" name="rangeType" id="rangeType" value="<?php echo $rangeType;?>"/>
       <input type="hidden" name="rangeValue" id="rangeValue" value="<?php echo $rangeValue;?>"/>
       <input type="checkbox" name="idle" id="idle" style="display: none;">     
       <input type="checkbox" name="showPlannedWork" id="showPlannedWork" style="display: none;">
       <input type="hidden" id="page" name="page" value="../report/imputation.php"/>
      <?php ImputationLine::drawLines($user->id, $rangeType, $rangeValue, false, true);?>
     </form>
  </div>
</div>
