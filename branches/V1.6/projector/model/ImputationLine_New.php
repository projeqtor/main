<?php 
/** ============================================================================
 * Project is the main object of the project managmement.
 * Almost all other objects are linked to a given project.
 */ 
class ImputationLine {

  // List of fields that will be exposed in general user interface
  //public $id;    // redefine $id to specify its visible place 
  public $refType;
  public $refId;
  public $idProject;
  public $idAssignment;
  public $name;
  public $comment;
  public $wbs;
  public $wbsSortable;
  public $topId;
  public $validatedWork;
  public $assignedWork;
  public $plannedWork;
  public $realWork;
  public $leftWork;
  public $imputable;
  public $elementary;
  public $arrayWork;
  public $arrayPlannedWork;
  public $startDate;
  public $endDate;

  /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL) {
    $arrayWork=array();
  }

   /** ==========================================================================
   * Return some lines for imputation purpose, including assignment and work
   * @return void
   */ 
  function __destruct() {
  }

  static function getLines($resourceId, $rangeType, $rangeValue, $showIdle, $showPlanned=true) {
    $result=array();
    if ($rangeType=='week') {
      $nbDays=7;
    }
    $crit=array('idResource' => $resourceId);
    if (! $showIdle) {
      $crit['idle']='0';
    }
    $startDate=self::getFirstDay($rangeType, $rangeValue);
    $ass=new Assignment();
    $assList=$ass->getSqlElementsFromCriteria($crit,false);
    $crit=array('idResource' => $resourceId);
    $crit[$rangeType]=$rangeValue;
    $work=new Work();
    $workList=$work->getSqlElementsFromCriteria($crit,false);
    $plannedWork=new PlannedWork();
    if ($showPlanned) {
      $plannedWorkList=$plannedWork->getSqlElementsFromCriteria($crit,false);
    }
    // Check if asxsignment exists for each work (may be closed, so make it appear)
    if (! $showIdle) {
      foreach ($workList as $work) { 
        $found=false;
        foreach ($assList as $ass) {
          if ($work->refType==$ass->refType and $work->refId==$ass->refId) {
            $found=true;
            break;
          }
        }
        if (! $found) {
          $ass=new Assignment($work->idAssignment);
          $assList[]=$ass;
        }
      }
    }
    foreach ($assList as $ass) {      
      $elt=new ImputationLine();
      $elt->refType=$ass->refType;
      $elt->refId=$ass->refId;
      $elt->comment=$ass->comment;
      $elt->idProject=$ass->idProject;
      $elt->idAssignment=$ass->id;
      $elt->assignedWork=$ass->assignedWork;
      $elt->plannedWork=$ass->plannedWork;
      $elt->realWork=$ass->realWork;
      $elt->leftWork=$ass->leftWork;
      $elt->arrayWork=array();
      $elt->arrayPlannedWork=array();
      $crit=array('refType'=>$elt->refType, 'refId'=>$elt->refId);
      $plan=null;
      $plan=SqlElement::getSingleSqlElementFromCriteria('PlanningElement', $crit);
      if ($plan) {
        $elt->name=$plan->refName;
        $elt->wbs=$plan->wbs;
        $elt->wbsSortable=$plan->wbsSortable;
        $elt->topId=$plan->topId;
        $elt->elementary=$plan->elementary;
        $elt->startDate=($plan->realStartDate)?$plan->realStartDate:$plan->plannedStartDate;
        $elt->endDate=($plan->realEndDate)?$plan->realEndDate:$plan->plannedEndDate;
      }
      $elt->imputable=true;
      $key=$elt->wbsSortable . ' ' . $ass->refType . '#' . $ass->refId;
      if (array_key_exists($key,$result)) {
        $key.= '/#' . $ass->id;
      }
      // fetch all work stored in database for this assignment
      foreach ($workList as $work) {
        if ($work->idAssignment==$elt->idAssignment) {
          $workDate=$work->workDate;
          $offset=dayDiffDates($startDate, $workDate)+1;
          $elt->arrayWork[$offset]=$work;
        }
      }
      // Fill arrayWork for days without an input
      for ($i=1; $i<=$nbDays; $i++) {
        if ( ! array_key_exists($i, $elt->arrayWork)) {
          $elt->arrayWork[$i]=new Work();
        }
      }
      if ($showPlanned) {
        foreach ($plannedWorkList as $plannedWork) {
          if ($plannedWork->idAssignment==$elt->idAssignment) {
           $workDate=$plannedWork->workDate;
           $offset=dayDiffDates($startDate, $workDate)+1;
           $elt->arrayPlannedWork[$offset]=$plannedWork;
          }
        }  
        // Fill arrayWork for days without an input
        for ($i=1; $i<=$nbDays; $i++) {
          if ( ! array_key_exists($i, $elt->arrayPlannedWork)) {
            $elt->arrayPlannedWork[$i]=new PlannedWork();
          }
        }    
      }
      $result[$key]=$elt;
    }
    foreach ($result as $key=>$elt) {
      $result=self::getParent($elt, $result);
    }
    ksort($result);
    return $result;
  }
  // Get the parent line for hierarchc display purpose
  private static function getParent($elt, $result){
    $plan=null;
    if ($elt->topId) {
      $plan=new PlanningElement($elt->topId);
    }
    if ($plan) {
      $key=$plan->wbsSortable . ' ' . $plan->refType . '#' . $plan->refId;
      if (! array_key_exists($key,$result)) {
        $top=new ImputationLine();
        $top->imputable=false;
        $top->name=$plan->refName;
        $top->wbs=$plan->wbs;
        $top->wbsSortable=$plan->wbsSortable;
        $top->topId=$plan->topId;
        $top->refType=$plan->refType;
        $top->refId=$plan->refId;        
        //$top->assignedWork=$plan->assignedWork;
        //$top->plannedWork=$plan->plannedWork;
        //$top->realWork=$plan->realWork;
        //$top->leftWork=$plan->leftWork;
        $result[$key]=$top;
        $result=self::getParent($top, $result);
      }
    }
    return $result;
  }
  
  private static function getFirstDay($rangeType, $rangeValue) {
    if ($rangeType=='week') {
      $year=substr($rangeValue,0,4);
      $week=substr($rangeValue,4,2);
      $day=firstDayofWeek($week,$year);
      return date('Y-m-d',$day);
    }
  }
  
  static function drawLines($resourceId, $rangeType, $rangeValue, $showIdle, $showPlanned=true, $print=false) {
    $nameWidth=220;
    $dateWidth=80;
    $workWidth=60;
    $inputWidth=30;
    $resource=new Resource($resourceId);
    $weekendColor="cfcfcf";
    $currentdayColor="ffffaa";
    $today=date('Y-m-d');
    if ($rangeType=='week') {
      $nbDays=7;
    }
    $startDate=self::getFirstDay($rangeType, $rangeValue);
    //$endDate=$startDate;
    //DateTime::add($endDate, new DateInterval("P" . $nbDays . "D"));
    $plus=$nbDays-1;
    $endDate=date('Y-m-d',strtotime("+$plus days", strtotime($startDate)));
    $rangeValueDisplay=substr($rangeValue,0,4) . '-' . substr($rangeValue,4);
    $colSum=array();
    for ($i=1; $i<=$nbDays; $i++) {
      $colSum[$i]=0;
    }
    echo '<table style="border: 1px solid #AAAAAA; margin: 0px; padding: 0px;">';
    echo '<TR style="height: 20px;">';
    echo '  <TD class="reportTableHeader" style="border-right:0px"></TD>';
    echo '  <TD class="reportTableHeader" style="border-left:0px; text-align:left;" colspan="5">' . $resource->name . ' - ' . i18n($rangeType) . ' ' . $rangeValueDisplay . '</TD>';
    echo '  <TD class="reportTableHeader" colspan="' . $nbDays . '" ' 
      . 'style="text-align: center;width:' . ($inputWidth*$nbDays) . 'px;">' 
      . htmlFormatDate($startDate) 
      . ' - ' 
      . htmlFormatDate($endDate)
      . '</TD>';
    echo '  <TD class="reportTableHeader" colspan="2" style="text-align:center;color: #707070;width:' . ($workWidth*2) . 'px;">' .  htmlFormatDate($today) . '</TD>';
    echo '</TR>';
    echo '<TR style="height: 20px;">';
    echo '  <TD class="reportTableHeader" style="border-right:0px;width:15px;"></TD>';
    echo '  <TD class="reportTableHeader" style="border-left:0px;width: ' . $nameWidth . 'px;text-align: left; ' 
      . 'border-left:0px; " nowrap>' .  i18n('colTask') . '</TD>';
    echo '  <TD class="reportTableHeader" style="width: ' . $dateWidth . 'px;">' 
      . i18n('colStart') . '</TD>';
    echo '  <TD class="reportTableHeader" style="width: ' . $dateWidth . 'px;">' 
      . i18n('colEnd') . '</TD>';
    echo '  <TD class="reportTableHeader" style="width: ' . $workWidth . 'px;">' 
      . i18n('colAssigned') . '</TD>';
    echo '  <TD class="reportTableHeader" style="width: ' . $workWidth . 'px;">' 
      . i18n('colReal') . '</TD>';
    $curDate=$startDate;
    for ($i=1; $i<=$nbDays; $i++) {
      echo '  <TD class="reportTableColumnHeader" style="font-size:95%;width: ' . $inputWidth . 'px;';
      if ($today==$curDate) {
        echo ' background-color:#' . $currentdayColor . '; color: #aaaaaa;"';
      } else if (isOffDay($curDate)) {
        echo ' background-color:#' . $weekendColor . '; color: #aaaaaa;"';
      }
      echo '">';
      if ($rangeType=='week') {
        echo  i18n('colWeekday' . $i) . "&nbsp;"  . date('d',strtotime($curDate)) ;
      }
      if (! $print) {    
        echo ' <input type="hidden" id="day_' . $i . '" name="day_' . $i . '" value="' . $curDate . '" />';
      }
      echo '</TD>';
      $curDate=date('Y-m-d',strtotime("+1 days", strtotime($curDate)));
    }   
    echo '  <TD class="reportTableHeader" style="width: ' . $workWidth . 'px;">' 
      . i18n('colLeft') . '</TD>';
    echo '  <TD class="reportTableHeader" style="width: ' . $workWidth . 'px;">' 
      . i18n('colPlanned') . '</TD>';
    echo '</TR>';  
    $tab=ImputationLine::getLines($resourceId, $rangeType, $rangeValue, $showIdle, $showPlanned);
    $nbLine=0;
    foreach ($tab as $key=>$line) {
      $nbLine++;
      $compStyle="";
      $bgColor="";
      if ($line->elementary) {
        $rowType="row";
      } else {
        $rowType="group";
        $compStyle="font-weight: bold; background: #E8E8E8;";
      }
      echo '<tr id="line_' . $nbLine . '" style="height:18px">';
      echo '<td class="reportTableData" style="' . $compStyle . '">';
      if (! $print) {
        echo '<input type="hidden" id="wbs_' . $nbLine . '" name="wbs_' . $nbLine . '"' 
          . ' value="' . $line->wbsSortable . '"/>';
        echo '<input type="hidden" id="status_' . $nbLine . '" name="status_' . $nbLine . '"'
          . ' value="opened"/>';
        echo '<input type="hidden" id="refType_' . $nbLine . '" name="refType_' . $nbLine . '"'
          . ' value="' . $line->refType . '"/>';
        echo '<input type="hidden" id="refId_' . $nbLine . '" name="refId_' . $nbLine . '"'
          . ' value="' . $line->refId . '"/>';
        echo '<input type="hidden" id="idAssignment_' . $nbLine . '" name="idAssignment_' . $nbLine . '"'
          . ' value="' . $line->idAssignment . '"/>';
        echo '<input type="hidden" id="imputable_' . $nbLine . '" name="imputable_' . $nbLine . '"'
          . ' value="' . $line->imputable . '"/>';
        echo '<input type="hidden" id="idProject_' . $nbLine . '" name="idProject_' . $nbLine . '"'
          . ' value="' . $line->idProject . '"/>';
      }
      echo '<img src="css/images/icon' . $line->refType . '16.png" />';
      echo '</td>';
      if (! $print) {
        echo '<td class="reportTableData" style="' . $compStyle . '" title="' . htmlEncodeJson($line->comment) . '">';
      } else {
        echo '<td class="reportTableData" style="' . $compStyle . '">';
      }
      // tab the name depending on level
      echo '<table><tr><td>';
      
      $level=(strlen($line->wbsSortable)+1)/4;
      $levelWidth = ($level-1) * 16;
      echo '<div style="float: left;width:' . $levelWidth . 'px;">&nbsp;</div>';     
        
      /*$max=(strlen($line->wbsSortable)+1)/4;
      for($j=1; $j < $max; $j++) {
        echo '&nbsp;&nbsp;&nbsp;&nbsp;';
      }*/
      
      echo '</td>';
      if ($rowType=="group" and ! $print) {
        echo '<td width="16"><span id="group_' . $nbLine . '" class="ganttExpandOpened"';
        echo 'onclick="workOpenCloseLine(' . $nbLine . ')">'; 
        echo '&nbsp;&nbsp;&nbsp;&nbsp;</span><span>&nbsp</span></td>' ;
      } else if (! $print) {
        echo '<td width="16"><div style="float: left;width:16px;">&nbsp;</div></td>';
      }
      //echo $line->wbs . ' '. $line->name . '</td>'; // for testing purpose, add wbs code
      echo '<td>' . $line->name . '</td>';
      if ($line->comment and !$print) {
        echo '<td>&nbsp;&nbsp;<img src="img/note.png" /></td>';
      }
      echo '</tr></table>';
      echo '</td>'; 
      echo '<td class="ganttDetail" align="center">' . htmlFormatDate($line->startDate) . '</td>';
      echo '<td class="ganttDetail" align="center">' . htmlFormatDate($line->endDate) . '</td>';
      echo '<td class="ganttDetail" align="center">';
      if ($line->imputable) {
        if (!$print) {
          echo '<div type="text" dojoType="dijit.form.NumberTextBox" ';
          echo ' constraints="{pattern:\'###0.0#\'}"'; 
          echo '  style="width: 60px; text-align: center;" ';
          echo ' trim="true" class="displayTransparent" readOnly="true" disabled="true"';
          echo ' id="assignedWork_' . $nbLine . '"';
          echo ' name="assignedWork_' . $nbLine . '"';
          echo ' value="' . $line->assignedWork . '" ';
          echo ' >';
          echo '</div>';
        } else {
          echo $line->assignedWork;
        }
      }
      echo '</td>';
      echo '<td class="ganttDetail" align="center">';
      if ($line->imputable) {
        if (!$print) {
          echo '<div type="text" dojoType="dijit.form.NumberTextBox" ';
          echo ' constraints="{pattern:\'###0.0#\'}"'; 
          echo '  style="width: 60px; text-align: center;" ';
          echo ' trim="true" class="displayTransparent" readOnly="true" disabled="true"';
          echo ' id="realWork_' . $nbLine . '"';
          echo ' name="realWork_' . $nbLine . '"';
          echo ' value="' . $line->realWork . '" ';
          echo ' >';
          echo '</div>';
        } else {
          echo  $line->realWork;
        }
      }
      echo '</td>';
      $curDate=$startDate;
      for ($i=1; $i<=$nbDays; $i++) {
        echo '<td class="ganttDetail" align="center"';
        if ($today==$curDate) {
          echo ' style="background-color:#' . $currentdayColor . ';"';
        } else if (isOffDay($curDate)) {
          echo ' style="background-color:#' . $weekendColor . '; color: #aaaaaa;"';
        }
        echo '>';
        if ($line->imputable) {
          $valWork=$line->arrayWork[$i]->work;
          $idWork=$line->arrayWork[$i]->id;
          if (! $print) {
            echo '<div style="position: relative">';
            if ($showPlanned) {
              echo '<div style="display: inline;';
              echo ' position: absolute; right: 2px; top: 0px; text-align: right;';
              echo ' color:#8080DD; font-size:80%;">';
              echo $line->arrayPlannedWork[$i]->work;
              echo '</div>';          
            }
            echo '<div type="text" dojoType="dijit.form.NumberTextBox" ';
            echo ' constraints="{min:0}"'; 
            echo '  style="width: 45px; text-align: center;" ';
            echo ' trim="true" maxlength="4" class="input" ';
            echo ' id="workValue_' . $nbLine . '_' . $i . '"';
            echo ' name="workValue_' . $nbLine . '_' . $i . '"';
            echo ' value="' . $valWork . '" ';
            echo ' >';
            echo '<script type="dojo/method" event="onChange" args="evt">';
            echo '  dispatchWorkValueChange("' . $nbLine . '","' . $i . '");';
            echo '</script>';
            echo '</div>';
            echo '</div>';
            echo '<input type="hidden" id="workId_' . $nbLine . '_' . $i . '"'
              . 'name="workId_' . $nbLine . '_' . $i . '"'
              . ' value="' . $idWork . '"/>';
            echo '<input type="hidden" id="workOldValue_' . $nbLine . '_' . $i . '"'
              . 'name="workOldValue_' . $nbLine . '_' . $i . '"'
              . ' value="' . $valWork . '"/>';
          } else {
            echo $valWork;
          }
          $colSum[$i]+=$valWork;             
        }
        echo '</td>';
        $curDate=date('Y-m-d',strtotime("+1 days", strtotime($curDate)));
      }
      echo '<td class="ganttDetail" align="center">';
      if ($line->imputable) {
        if (!$print) {
          echo '<div type="text" dojoType="dijit.form.NumberTextBox" ';
          echo ' constraints="{min:0}"'; 
          echo '  style="width: 60px; text-align: center;" ';
          echo ' trim="true" class="input" ';
          echo ' id="leftWork_' . $nbLine . '"';
          echo ' name="leftWork_' . $nbLine . '"';
          echo ' value="' . $line->leftWork . '" ';
          echo ' >';
          echo '<script type="dojo/method" event="onChange" args="evt">';
          echo '  dispatchLeftWorkValueChange("' . $nbLine . '");';
          echo '</script>';
          echo '</div>';
        } else {
          echo $line->leftWork;
        }
      } 
      echo '</td>';
      echo '<td class="ganttDetail" align="center">';
      if ($line->imputable) {
        if (!$print) {
          echo '<div type="text" dojoType="dijit.form.NumberTextBox" ';
          echo ' constraints="{pattern:\'###0.0#\'}"'; 
          echo '  style="width: 60px; text-align: center;" ';
          echo ' trim="true" class="displayTransparent" readonly="true" disabled="true"';
          echo ' id="plannedWork_' . $nbLine . '"';
          echo ' name="plannedWork_' . $nbLine . '"';
          echo ' value="' . $line->plannedWork . '" ';
          echo ' >';
          echo '</div>';
        } else {
          echo $line->plannedWork;
        }
      } 
      echo '</td>';
      echo '</tr>';
    }
    echo '<TR class="ganttDetail" >';
    echo '  <TD class="ganttLeftTopLine" style="width:15px;"></TD>';
    echo '  <TD class="ganttLeftTopLine" style="width: ' . $nameWidth . 'px;text-align: left; ' 
      . 'border-left:0px; " nowrap><NOBR></NOBR></TD>';
    echo '  <TD class="ganttLeftTopLine" style="width: ' . $dateWidth . 'px;"><NOBR>' 
      . '</NOBR></TD>';
    echo '  <TD class="ganttLeftTopLine" style="width: ' . $dateWidth . 'px;"><NOBR>' 
      . '</NOBR></TD>';
    echo '  <TD class="ganttLeftTopLine" style="width: ' . $workWidth . 'px;"><NOBR>' 
      . '</NOBR></TD>';
    echo '  <TD class="ganttLeftTopLine" style="width: ' . $workWidth . 'px;"><NOBR>' 
      . '</NOBR></TD>';
    $curDate=$startDate;
    for ($i=1; $i<=$nbDays; $i++) {
      echo '  <TD class="ganttLeftTitle" style="width: ' . $inputWidth . 'px;';
      if ($today==$curDate) {
        //echo ' background-color:#' . $currentdayColor . ';';
      }
      echo '"><NOBR>'; 
      if (!$print) {
        echo '<div type="text" dojoType="dijit.form.NumberTextBox" ';
        echo ' constraints="{}"'; 
        echo '  style="width: 45px; text-align: center;" ';
        echo ' trim="true" class="displayTransparent" ';
        echo ' id="colSumWork_' . $i . '"';
        echo ' name="colSumWork_' . $i . '"';
        echo ' value="' . $colSum[$i] . '" ';
        echo ' >';
        echo '</div>';
      } else {
        echo $colSum[$i];
      }
      echo '</NOBR></TD>';
      $curDate=date('Y-m-d',strtotime("+1 days", strtotime($curDate)));
    }    
    echo '  <TD class="ganttLeftTopLine" style="width: ' . $workWidth . 'px;"><NOBR>' 
      .  '</NOBR></TD>';
    echo '  <TD class="ganttLeftTopLine" style="width: ' . $workWidth . 'px;"><NOBR>' 
      .  '</NOBR></TD>';
    echo '</TR>';      
    echo '</table>';  
    echo '<input type="hidden" id="nbLines" name="nbLines" value="' . $nbLine . '"/>';
  }
// ============================================================================**********
// GET STATIC DATA FUNCTIONS
// ============================================================================**********
  


// ============================================================================**********
// GET VALIDATION SCRIPT
// ============================================================================**********
  
  /** ==========================================================================
   * Return the validation sript for some fields
   * @return the validation javascript (for dojo frameword)
   */
  public function getValidationScript($colName) {
    $colScript = parent::getValidationScript($colName);

    if ($colName=="idle") {   
      $colScript .= '<script type="dojo/connect" event="onChange" >';
      $colScript .= '  if (this.checked) { ';
      $colScript .= '    if (dijit.byId("PlanningElement_realEndDate").value==null) {';
      $colScript .= '      dijit.byId("PlanningElement_realEndDate").set("value", new Date); ';
      $colScript .= '    }';
      $colScript .= '  } else {';
      $colScript .= '    dijit.byId("PlanningElement_realEndDate").set("value", null); ';
      //$colScript .= '    dijit.byId("PlanningElement_realDuration").set("value", null); ';
      $colScript .= '  } '; 
      $colScript .= '  formChanged();';
      $colScript .= '</script>';
    }
    return $colScript;
  }
  
// ============================================================================**********
// MISCELLANOUS FUNCTIONS
// ============================================================================**********
  
  public function save() {
    $finalResult="";
    foreach($this->arrayWork as $work) {
      $result="";
      if ($work->work) {
        //echo "save";
        $result=$work->save();
      } else {
        if ($work->id) {
          //echo "delete";
          $result=$work->delete();
        }
      }
      if (stripos($result,'id="lastOperationStatus" value="ERROR"')>0 ) {
        $status='ERROR';
        $finalResult=$result;
        break;
      } else if (stripos($result,'id="lastOperationStatus" value="OK"')>0 ) {
        $status='OK';
        $finalResult=$result;
      } else { 
        if ($finalResult=="") {
          $finalResult=$result;
        }
      } 
    }
    return $finalResult;
  }
}
?>