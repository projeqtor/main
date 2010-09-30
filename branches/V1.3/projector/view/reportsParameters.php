<?php
/* ============================================================================
 * Presents the list of objects of a given class.
 *
 */
require_once "../tool/projector.php";
scriptLog('   ->/view/reportsList.php');
?>
<form id='reportForm' name='reportForm' onSubmit="return false;">
<table>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
<?php 
$currentWeek=weekNumber(date('Y-m-d'));
if (strlen($currentWeek)==1) {
  $currentWeek='0' . $currentWeek;
}
$currentYear=strftime("%Y") ;
$currentMonth=strftime("%m") ;
$idReport=$_REQUEST['idReport'];
if (!$idReport) {
  exit;
}
$report=new Report($idReport);
echo "<input type='hidden' id='reportFile' name='reportFile' value='" . $report->file . "' />";
echo "<input type='hidden' id='reportId' name='reportId' value='" . $report->id . "' />";
$param=new ReportParameter();
$crit=array('idReport'=>$idReport);
$listParam=$param->getSqlElementsFromCriteria($crit);
foreach ($listParam as $param) {
  if ($param->paramType=='week') {
    $defaultWeek='';
    $defaultYear='';
    if ($param->defaultValue=='currentWeek') {
      $defaultWeek=$currentWeek;
      $defaultYear=$currentYear;
    }
    ?>
    <input type="hidden" id='periodValue' name='periodValue' value='<?php echo $currentYear . $currentWeek;?>' />
    <input type="hidden" id='periodType' name='periodType' value='week'/>
    <tr>
    <td><label><?php echo i18n("year");?>&nbsp;:&nbsp;</label></td>
    <td><div style="width:70px; text-align: center; color: #000000;" 
      dojoType="dijit.form.NumberSpinner" 
      constraints="{min:2000,max:2100,places:0,pattern:'###0'}"
      intermediateChanges="true"
      maxlength="4"
      value="<?php echo $defaultYear;?>" smallDelta="1"
      id="yearSpinner" name="yearSpinner" >
      <script type="dojo/method" event="onChange">
        var year=dijit.byId('yearSpinner').attr('value');
        var week=dijit.byId('weekSpinner').attr('value') + '';
        week=(week.length==1)?'0'+week:week;
        dojo.byId('periodValue').value='' + year + week;
      </script>
    </div></td>
    </tr>
    <tr>
    <td><label><?php echo i18n("week");?>&nbsp;:&nbsp;</label></td>
    <td><div style="width:55px; text-align: center; color: #000000;" 
       dojoType="dijit.form.NumberSpinner" 
       constraints="{min:1,max:55,places:0,pattern:'00'}"
       intermediateChanges="true"
       maxlength="2"
       value="<?php echo $defaultWeek;?>" smallDelta="1"
       id="weekSpinner" name="weekSpinner" >
       <script type="dojo/method" event="onChange" >
         var year=dijit.byId('yearSpinner').attr('value');
         var week=dijit.byId('weekSpinner').attr('value') + '';
         week=(week.length==1)?'0'+week:week;
         dojo.byId('periodValue').value='' + year + week;
       </script>
     </div></td>
     </tr>
<?php 
  } else if ($param->paramType=='month') {
    $defaultMonth='';
    $defaultYear='';
    if ($param->defaultValue=='currentMonth') {
      $defaultMonth=$currentMonth;
      $defaultYear=$currentYear;
    }
?>
    <input type="hidden" id='periodValue' name='periodValue' value='<?php echo $currentYear . $currentMonth;?>' />
    <input type="hidden" id='periodType' name='periodType' value='month'/>
    <tr>
    <td><label><?php echo i18n("year");?>&nbsp;:&nbsp;</label></td>
    <td><div style="width:70px; text-align: center; color: #000000;" 
      dojoType="dijit.form.NumberSpinner" 
      constraints="{min:2000,max:2100,places:0,pattern:'###0'}"
      intermediateChanges="true"
      maxlength="4"
      value="<?php echo $defaultYear;?>" smallDelta="1"
      id="yearSpinner" name="yearSpinner" >
      <script type="dojo/method" event="onChange">
        var year=dijit.byId('yearSpinner').attr('value');
        var month=dijit.byId('monthSpinner').attr('value') + '';
        month=(month.length==1)?'0'+month:month;
        dojo.byId('periodValue').value='' + year + month;
      </script>
    </div></td>
    </tr>
    <tr>
    <td><label><?php echo i18n("month");?>&nbsp;:&nbsp;</label></td>
    <td><div style="width:55px; text-align: center; color: #000000;" 
       dojoType="dijit.form.NumberSpinner" 
       constraints="{min:1,max:12,places:0,pattern:'00'}"
       intermediateChanges="true"
       maxlength="2"
       value="<?php echo $defaultMonth;?>" smallDelta="1"
       id="monthSpinner" name="monthSpinner" >
       <script type="dojo/method" event="onChange" >
        var year=dijit.byId('yearSpinner').attr('value');
        var month=dijit.byId('monthSpinner').attr('value') + '';
        month=(month.length==1)?'0'+month:month;
        dojo.byId('periodValue').value='' + year + month;
       </script>
     </div></td>
     </tr> 
<?php    
  } else if ($param->paramType=='year') {
    $defaultYear='';
    if ($param->defaultValue=='currentYear') {
      $defaultYear=$currentYear;
    }
?>
    <input type="hidden" id='periodValue' name='periodValue' value='<?php echo $currentYear;?>' />
    <input type="hidden" id='periodType' name='periodType' value='year'/>
    <tr>
    <td><label><?php echo i18n("year");?>&nbsp;:&nbsp;</label></td>
    <td><div style="width:70px; text-align: center; color: #000000;" 
      dojoType="dijit.form.NumberSpinner" 
      constraints="{min:2000,max:2100,places:0,pattern:'###0'}"
      intermediateChanges="true"
      maxlength="4"
      value="<?php echo $defaultYear;?>" smallDelta="1"
      id="yearSpinner" name="yearSpinner" >
      <script type="dojo/method" event="onChange">
        var year=dijit.byId('yearSpinner').attr('value');
        dojo.byId('periodValue').value='' + year;
      </script>
    </div></td>
    </tr>
<?php    
  } else if ($param->paramType=='date') {
    $defaultDate='';
    if ($param->defaultValue=='today') {
      $defaultDate=date('Y-m-d');
    } else if ($param->defaultValue) {
      $defaultDate=$param->defaultValue; 
    }
?>
    <tr>
    <td><label><?php echo i18n('col' . ucfirst($param->name));?>&nbsp;:&nbsp;</label></td>
    <td><div style="width:90px; text-align: center; color: #000000;" 
      dojoType="dijit.form.DateTextBox" 
      invalidMessage="<?php echo i18n('messageInvalidDate');?>" 
      value="<?php echo $defaultDate;?>"
      id="<?php echo $param->name;?>" name="<?php echo $param->name;?>" >
    </div></td>
    </tr>
<?php    
  } else if ($param->paramType=='periodScale') {
    $defaultValue=$param->defaultValue;
?>
    <tr>
    <td><label><?php echo i18n('col' . ucfirst($param->name));?>&nbsp;:&nbsp;</label></td>
    <td>
    <select dojoType="dijit.form.FilteringSelect" class="input" 
       style="width: 200px;"
       id="<?php echo $param->name;?>" name="<?php echo $param->name;?>"
     >
       <option value="day" <?php echo ($defaultValue=='day')?'SELECTED':'';?> ><?php echo i18n('day'); ?> </option>
       <option value="week" <?php echo ($defaultValue=='week')?'SELECTED':'';?> ><?php echo i18n('week'); ?> </option>
       <option value="month" <?php echo ($defaultValue=='month')?'SELECTED':'';?> ><?php echo i18n('month'); ?> </option>
     </select>
    </td>
    </tr>
<?php    
  } else if ($param->paramType=='projectList') {
    $defaultValue='';
    if ($param->defaultValue=='currentProject') {
      if (array_key_exists('project',$_SESSION)) {
        if ($_SESSION['project']!='*') {
          $defaultValue=$_SESSION['project'];
        }
      }
    } else if ($param->defaultValue) {
      $defaultValue=$param->defaultValue; 
    }
?>
    <tr>
    <td><label><?php echo i18n('col' . ucfirst($param->name));?>&nbsp;:&nbsp;</label></td>
    <td>
    <select dojoType="dijit.form.FilteringSelect" class="input" 
       style="width: 200px;"
       id="<?php echo $param->name;?>" name="<?php echo $param->name;?>"
     >
       <?php htmlDrawOptionForReference('idProject', $defaultValue, null, false); ?>
     </select>    
    </td>
    </tr>
<?php 
  }
}
?>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td></td>
    <td>
      <button dojoType="dijit.form.Button" type="submit" id="reportSubmit" onclick="runReport();return false;">
        <?php echo i18n("buttonOK");?>
      </button>
    </td>
  </tr>
</table>
</form>