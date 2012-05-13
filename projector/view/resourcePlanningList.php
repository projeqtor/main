<?php
/* ============================================================================
 * Presents the list of objects of a given class.
 *
 */
require_once "../tool/projector.php";
scriptLog('   ->/view/resourcePlanningList.php');

$canPlan=false;
$right=SqlElement::getSingleSqlElementFromCriteria('habilitationOther', array('idProfile'=>$user->idProfile, 'scope'=>'planning'));
if ($right) {
  $list=new ListYesNo($right->rightAccess);
  if ($list->code=='YES') {
    $canPlan=true;
  }
}
$startDate=date('Y-m-d');
$endDate=null;
$user=$_SESSION['user'];
$saveDates=false;
$paramStart=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningStartDate'));
if ($paramStart->id) {
  $startDate=$paramStart->parameterValue;
  $saveDates=true;
}
$paramEnd=SqlElement::getSingleSqlElementFromCriteria('Parameter',array('idUser'=>$user->id,'idProject'=>null,'parameterCode'=>'planningEndDate'));
if ($paramEnd->id) {
  $endDate=$paramEnd->parameterValue;
  $saveDates=true;
}

//$objectClass='Task';
//$obj=new $objectClass;
?>
   
<div id="mainPlanningDivContainer" dojoType="dijit.layout.BorderContainer">
	<div dojoType="dijit.layout.ContentPane" region="top" id="listHeaderDiv" height="27px">
		<table width="100%" height="27px" class="listTitle" >
		  <tr height="27px">
		    <td width="50px" align="center">
		      <span style="position:absolute; left:10px; top:7px">
            <img src="css/images/iconPlanning32.png" width="32" height="32" />
          </span>
		    </td>
		    <td  width="120px"><span class="title"><?php echo i18n('menuResourcePlanning');?></span></td>
		    <td>   
		      <form dojoType="dijit.form.Form" id="listForm" action="" method="" >
		        <table style="width: 100%;">
		          <tr>
		            <td>
		              <input type="hidden" id="objectClass" name="objectClass" value="" /> 
		              <input type="hidden" id="objectId" name="objectId" value="" />
		              &nbsp;&nbsp;&nbsp;
<?php if ($canPlan) { ?>
		              <button id="planButton" dojoType="dijit.form.Button" showlabel="false"
		                title="<?php echo i18n('buttonPlan');?>"
		                iconClass="iconPlan" >
		                <script type="dojo/connect" event="onClick" args="evt">
                     showPlanParam();
                     return false;
                    </script>
		              </button>
<?php }?>             
		            </td>
		            <td style="white-space:nowrap;">
		              <table>
                    <tr>
                      <td align="right">&nbsp;&nbsp;&nbsp;<?php echo i18n("displayStartDate");?>&nbsp;&nbsp;</td><td>
                        <div dojoType="dijit.form.DateTextBox"
                           id="startDatePlanView" name="startDatePlanView"
                           invalidMessage="<?php echo i18n('messageInvalidDate')?>"
                           type="text" maxlength="10"
                           style="width:100px; text-align: center;" class="input"
                           hasDownArrow="true"
                           value="<?php echo $startDate;?>" >
                           <script type="dojo/method" event="onChange" >
                            refreshJsonPlanning();
                           </script>
                         </div>
                      </td>
                    </tr>
                    <tr>
                      <td align="right">&nbsp;&nbsp;&nbsp;<?php echo i18n("displayEndDate");?>&nbsp;&nbsp;</td>
                      <td>
                        <div dojoType="dijit.form.DateTextBox"
                           id="endDatePlanView" name="endDatePlanView"
                           invalidMessage="<?php echo i18n('messageInvalidDate')?>"
                           type="text" maxlength="10"
                           style="width:100px; text-align: center;" class="input"
                           hasDownArrow="true"
                           value="<?php echo $endDate;?>" >
                           <script type="dojo/method" event="onChange" >
                            refreshJsonPlanning();
                           </script>
                        </div>
                      </td>
                    </tr>
                  </table>
		            </td>
                <td>
                  <table>
                    <tr>
                      <td width="32px">
                        <button title="<?php echo i18n('printPlanning')?>"
                         dojoType="dijit.form.Button"
                         id="listPrint" name="listPrint"
                         iconClass="dijitEditorIcon dijitEditorIconPrint" showLabel="false">
                          <script type="dojo/connect" event="onClick" args="evt">
                          showPrint("../tool/jsonResourcePlanning.php", 'planning');
                          </script>
                        </button>
                      </td>
                      <td width="32px">
                        <button title="<?php echo i18n('reportPrintPdf')?>"
                         dojoType="dijit.form.Button"
                         id="listPrintPdf" name="listPrintPdf"
                         iconClass="iconPdf" showLabel="false">
                          <script type="dojo/connect" event="onClick" args="evt">
                          showPrint("../tool/jsonResourcePlanning.php", 'planning', null, 'pdf');
                          </script>
                        </button>
                      </td>
                      <td width="32px">
                        <input type="hidden" id="outMode" name="outMode" value="" />
                      </td>
                    </tr>
                    <tr>
                      <td colspan="3" style="white-space:nowrap;">
                        <span title="<?php echo i18n('saveDates')?>" dojoType="dijit.form.CheckBox"
                           type="checkbox" id="listSaveDates" name="listSaveDates" class=""
                           <?php if ( $saveDates) {echo 'checked="checked"'; } ?>  >

                          <script type="dojo/method" event="onChange" >
                            refreshJsonPlanning();
                          </script>
                        </span>
                        <span for="listSaveDates"><?php echo i18n("saveDates");?></span>
                      </td>
                    </tr>
                  </table>
                </td>
		            <td>
                  <div id="planResultDiv" style=" width: 260px;height: 10px;" 
                    dojoType="dijit.layout.ContentPane" region="center" >
                  </div>
                </td>
		            <td style="text-align: right; align: right;">
		              <table width="100%"><tr><td>
                  <?php echo i18n("labelShowWbs");?>
                  </td><td >
		              <div title="<?php echo i18n('showWbs')?>" dojoType="dijit.form.CheckBox" 
                    type="checkbox" id="showWBS" name="showWBS">
		                <script type="dojo/method" event="onChange" >
                      refreshJsonPlanning();
                    </script>
		              </div>&nbsp;
		              </td></tr><tr><td>
		              <?php echo i18n("labelShowIdle");?>
                  </td><td>
		              <div title="<?php echo i18n('showIdleElements')?>" dojoType="dijit.form.CheckBox" 
                    type="checkbox" id="listShowIdle" name="listShowIdle">
		                <script type="dojo/method" event="onChange" >
                      refreshJsonPlanning();
                    </script>
		              </div>&nbsp;
                  </td></tr>
                  </table>
		            </td>
		          </tr>
		        </table>    
		      </form>
		    </td>
		  </tr>
		</table>
		<div id="listBarShow" onMouseover="showList('mouse')" onClick="showList('click');">
		  <div id="listBarIcon" align="center"></div>
		</div>
	
		<div dojoType="dijit.layout.ContentPane" id="planningJsonData" jsId="planningJsonData" 
     style="display: none">
		  <?php
            include '../tool/jsonResourcePlanning.php';
          ?>
		</div>
	</div>
	<div dojoType="dijit.layout.ContentPane" region="center" id="gridContainerDiv">
   <div id="submainPlanningDivContainer" dojoType="dijit.layout.BorderContainer"
    style="border-top:1px solid #ffffff;">
	   <div dojoType="dijit.layout.ContentPane" region="left" splitter="true" 
      style="width:425px; height:100%; overflow-x:scroll; overflow-y:hidden;" class="ganttDiv" 
      id="leftGanttChartDIV" name="leftGanttChartDIV"
      onScroll="dojo.byId('ganttScale').style.left=(this.scrollLeft)+'px';">
     </div>
     <div dojoType="dijit.layout.ContentPane" region="center" 
      style="height:100%; overflow:hidden;" class="ganttDiv" 
      id="GanttChartDIV" name="GanttChartDIV" >
       <div id="mainRightPlanningDivContainer" dojoType="dijit.layout.BorderContainer">
         <div dojoType="dijit.layout.ContentPane" region="top" 
          style="width:100%; height:43px; overflow:hidden;" class="ganttDiv"
          id="topGanttChartDIV" name="topGanttChartDIV">
         </div>
         <div dojoType="dijit.layout.ContentPane" region="center" 
          style="width:100%; overflow-x:scroll; overflow-y:scroll; position: relative; top:-10px;" class="ganttDiv"
          id="rightGanttChartDIV" name="rightGanttChartDIV"
          onScroll="dojo.byId('rightside').style.left='-'+(this.scrollLeft+1)+'px';
                    dojo.byId('leftside').style.top='-'+(this.scrollTop)+'px';"
         >
         </div>
       </div>
     </div>
   </div>
	</div>
</div>
