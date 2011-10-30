<?PHP
/** ===========================================================================
 * Get the list of objects, in Json format, to display the grid list
 */
  require_once "../tool/projector.php";  
  scriptLog('   ->/tool/jsonPlanning.php');
  $objectClass='PlanningElement';
  $obj=new $objectClass();
  $table=$obj->getDatabaseTableName();
  $print=false;
  if ( array_key_exists('print',$_REQUEST) ) {
    $print=true;
    include_once('../tool/formatter.php');
  }  
  
  // Header
  if ( array_key_exists('report',$_REQUEST) ) {
    $headerParameters="";
    if (array_key_exists('startDate',$_REQUEST) and trim($_REQUEST['startDate'])!="") {
      $headerParameters.= i18n("colStartDate") . ' : ' . dateFormatter($_REQUEST['startDate']) . '<br/>';
    }
    if (array_key_exists('endDate',$_REQUEST) and trim($_REQUEST['endDate'])!="") {
      $headerParameters.= i18n("colEndDate") . ' : ' . dateFormatter($_REQUEST['endDate']) . '<br/>';
    }
    if (array_key_exists('format',$_REQUEST)) {
      $headerParameters.= i18n("colFormat") . ' : ' . i18n($_REQUEST['format']) . '<br/>';
    }
    if (array_key_exists('idProject',$_REQUEST) and trim($_REQUEST['idProject'])!="") {
      $headerParameters.= i18n("colIdProject") . ' : ' . SqlList::getNameFromId('Project', $_REQUEST['idProject']) . '<br/>';
    }
    include "../report/header.php";
  }
  if (! isset($outMode)) { $outMode=""; } 
  
  $accessRightRead=securityGetAccessRight('menuProject', 'read');
  
  $querySelect = '';
  $queryFrom='';
  $queryWhere='';
  $queryOrderBy='';
  $idTab=0;
  if (! array_key_exists('idle',$_REQUEST) ) {
    $queryWhere= $table . ".idle=0 ";
  }
  $queryWhere.= ($queryWhere=='')?'':' and ';
  $queryWhere.=getAccesResctictionClause('Activity',$objectClass);
  if ( array_key_exists('report',$_REQUEST) ) {
    if (array_key_exists('idProject',$_REQUEST) and $_REQUEST['idProject']!=' ') {
      $queryWhere.= ($queryWhere=='')?'':' and ';
      $queryWhere.=  $table . ".idProject in " . getVisibleProjectsList(true, $_REQUEST['idProject']) ;
    }
  } else {
  	$queryWhere.= ($queryWhere=='')?'':' and ';
    $queryWhere.=  $table . ".idProject in " . getVisibleProjectsList() ;
  }
  
/*  if ($accessRightRead=='NO') {
    $queryWhere.= ($queryWhere=='')?'':' and ';
    $queryWhere.=  "(1 = 2)";      
  } else if ($accessRightRead=='OWN') {
    $queryWhere.= ($queryWhere=='')?'':' and ';
    //$queryWhere.=  $table . ".idUser = '" . $_SESSION['user']->id . "'";     
    $queryWhere.=  "(1 = 2)"; // If visibility = own => no visibility            
  } else if ($accessRightRead=='PRO') {
    $queryWhere.= ($queryWhere=='')?'':' and ';
    $queryWhere.=  $table . ".idProject in " . transformListIntoInClause($_SESSION['user']->getVisibleProjects()) ;      
  } else if ($accessRightRead=='ALL') {
    // No restriction to add
  }*/
  $querySelect .= $table . ".* ";
  $queryFrom .= $table;
  // Link to resource
  //$querySelect .= ", user.fullName as nameResource ";
  //$queryFrom .= " left join user on ( task.idResource=user.id )";    
  
  $queryOrderBy .= $table . ".wbsSortable ";

  // constitute query and execute
  $queryWhere=($queryWhere=='')?' 1=1':$queryWhere;
  $query='select ' . $querySelect 
       . ' from ' . $queryFrom
       . ' where ' . $queryWhere 
       . ' order by ' . $queryOrderBy;
  $result=Sql::query($query);
  $nbRows=0;
  if ($print) {
    if ( array_key_exists('report',$_REQUEST) ) {
      $test=array();
      if (Sql::$lastQueryNbRows > 0) $test[]="OK";
      if (checkNoData($test))  exit;
    }
    if ($outMode=='mpp') {
    	exportGantt($result);
    } else {
    	displayGantt($result);
    }
  } else {
    // return result in json format
    $d=new Dependency();
    echo '{"identifier":"id",' ;
    echo ' "items":[';
    if (Sql::$lastQueryNbRows > 0) {
      while ($line = Sql::fetchLine($result)) {
        echo (++$nbRows>1)?',':'';
        echo  '{';
        $nbFields=0;
        $idPe="";
        foreach ($line as $id => $val) {
          if ($val==null) {$val=" ";}
          if ($val=="") {$val=" ";}
          echo (++$nbFields>1)?',':'';
          echo '"' . htmlEncode($id) . '":"' . htmlEncodeJson($val) . '"';
          if ($id=='id') {$idPe=$val;}
        }
        $crit=array('successorId'=>$idPe);
        $listPred="";
        $depList=$d->getSqlElementsFromCriteria($crit,false);
        foreach ($depList as $dep) {
          $listPred.=($listPred!="")?',':'';
          $listPred.=$dep->predecessorId;
        }
        echo ', "depend":"' . $listPred . '"';
        echo '}';       
      }
    }
    echo ' ] }'; 
  }

  function displayGantt($result) {
    $showWbs=false;
    if (array_key_exists('showWBS',$_REQUEST) ) {
      $showWbs=true;
    }
    // calculations
    $startDate=date('Y-m-d');
    if (array_key_exists('startDate',$_REQUEST)) {
      $startDate=$_REQUEST['startDate'];
    }
    $endDate='';
    if (array_key_exists('endDate',$_REQUEST)) {
      $endDate=$_REQUEST['endDate'];
    }
    $format='day';
    if (array_key_exists('format',$_REQUEST)) {
      $format=$_REQUEST['format'];
    }
    if($format == 'day') {
      $colWidth = 18;
      $colUnit = 1;
      $topUnit=7;
    } else if($format == 'week') {
      $colWidth = 50;
      $colUnit = 7;
      $topUnit=7;
    } else if($format == 'month') {
      $colWidth = 60;
      $colUnit = 30;
      $topUnit=30;
    }
    $maxDate = '';
    $minDate = '';
    if (Sql::$lastQueryNbRows > 0) {
      $resultArray=array();
      while ($line = Sql::fetchLine($result)) {
        $pStart="";
        $pStart=(trim($line['initialStartDate'])!="")?$line['initialStartDate']:$pStart;
        $pStart=(trim($line['validatedStartDate'])!="")?$line['validatedStartDate']:$pStart;
        $pStart=(trim($line['plannedStartDate'])!="")?$line['plannedStartDate']:$pStart;
        $pStart=(trim($line['realStartDate'])!="")?$line['realStartDate']:$pStart;
        if (trim($line['plannedStartDate'])!=""
        and trim($line['realStartDate'])!=""
        and $line['plannedStartDate']<$line['realStartDate'] ) {
          $pStart=$line['plannedStartDate'];
        }
        $pEnd="";
        $pEnd=(trim($line['initialEndDate'])!="")?$line['initialEndDate']:$pEnd;
        $pEnd=(trim($line['validatedEndDate'])!="")?$line['validatedEndDate']:$pEnd;
        $pEnd=(trim($line['plannedEndDate'])!="")?$line['plannedEndDate']:$pEnd;
        $pEnd=(trim($line['realEndDate'])!="")?$line['realEndDate']:$pEnd;
        //if ($pEnd=="") {$pEnd=date('Y-m-d');}
        if ($line['refType']=='Milestone') {
          $pStart=$pEnd;
        }
        $line['pStart']=$pStart;
        $line['pEnd']=$pEnd;
        $resultArray[]=$line;
        if ($maxDate=='' or $maxDate<$pEnd) {$maxDate=$pEnd;}
        if ($minDate=='' or $minDate>$pStart) {$minDate=$pStart;}
      }
      if ($minDate<$startDate) {
        $minDate=$startDate;
      }
      if ($endDate and $maxDate>$endDate) {
        $maxDate=$endDate;
      }
      if ($format=='day' or $format=='week') {   
        $minDate=addDaysToDate($minDate,-1);
        $minDate=date('Y-m-d',firstDayofWeek(weekNumber($minDate),substr($minDate,0,4)));
        $maxDate=addDaysToDate($maxDate,+1);
        $maxDate=date('Y-m-d',firstDayofWeek(weekNumber($maxDate),substr($maxDate,0,4)));
        $maxDate=addDaysToDate($maxDate,+6);
      } else if ($format=='month') {
        //$minDate=addDaysToDate($minDate,-1);
        $minDate=substr($minDate,0,8).'01';
        $maxDate=addDaysToDate($maxDate,+1);
        $maxDate=addMonthsToDate($maxDate,+1);
        $maxDate=substr($maxDate,0,8).'01';
        $maxDate=addDaysToDate($maxDate,-1);
      }
      $numDays = (dayDiffDates($minDate, $maxDate) +1);
      $numUnits = round($numDays / $colUnit);
      $topUnits = round($numDays / $topUnit);
      $days=array();
      $openDays=array();
      $day=$minDate;
      for ($i=0;$i<$numDays; $i++) {
        $days[$i]=$day;
        $openDays[$i]=isOpenDay($day);
        $day=addDaysToDate($day,1);
      }
      //echo "mindate:$minDate maxdate:$maxDate numDays:$numDays numUnits:$numUnits topUnits:$topUnits" ;     
      // Header
      //echo '<table dojoType="dojo.dnd.Source" id="wishlistNode" class="container ganttTable" style="border: 1px solid #AAAAAA; margin: 0px; padding: 0px;">';
      echo '<table style="font-size:80%; border: 1px solid #AAAAAA; margin: 0px; padding: 0px;">';
      echo '<tr style="height: 20px;"><td colspan="6">&nbsp;</td>';
      $day=$minDate;
      for ($i=0;$i<$topUnits;$i++) {
        $span=$topUnit;
        $title="";
        if ($format=='month') {
          $title=substr($day,0,4);
          $span=numberOfDaysOfMonth($day);
        } else if($format=='week') {
          $title=substr($day,2,2) . " #" . weekNumber($day);
        } else if ($format=='day') {
          $tDate = explode("-", $day);
          $date= mktime(0, 0, 0, $tDate[1], $tDate[2]+1, $tDate[0]);
          $title=substr($day,0,4) . " #" . weekNumber($day);
          $title.=' (' . substr(i18n(date('F', $date)),0,4) . ')';
        }
        echo '<td class="reportTableHeader" colspan="' . $span . '">';
        echo $title;
        echo '</td>';
        if ($format=='month') {
          $day=addMonthsToDate($day,1);
        } else {
          $day=addDaysToDate($day,$topUnit);
        }
      }
      echo '</tr>';
      echo '<TR style="height: 20px;">';
      echo '  <TD class="reportTableHeader" style="width:15px; border-right:0px;"></TD>';
      echo '  <TD class="reportTableHeader" style="width:150px; border-left:0px; text-align: left;">' . i18n('colTask') . '</TD>';
      echo '  <TD class="reportTableHeader" style="width:30px">' . i18n('colDuration') . '</TD>' ;
      echo '  <TD class="reportTableHeader" style="width:30px">'  . i18n('colPct') . '</TD>' ;
      echo '  <TD class="reportTableHeader" style="width:50px">'  . i18n('colStart') . '</TD>' ;
      echo '  <TD class="reportTableHeader" style="width:50px">'  . i18n('colEnd') . '</TD>' ;
      $weekendColor="#cfcfcf";
      $day=$minDate;
      for ($i=0;$i<$numUnits;$i++) {
        $color="";
        $span=$colUnit;
        if ($format=='month') {
          $tDate = explode("-", $day);
          $date= mktime(0, 0, 0, $tDate[1], $tDate[2]+1, $tDate[0]);
          $title=i18n(date('F', $date));
          $span=numberOfDaysOfMonth($day);
        } else if($format=='week') {
          $title=substr(htmlFormatDate($day),0,5);
        } else if ($format=='day') {
          $color=($openDays[$i]==1)?'':'background-color:' . $weekendColor . ';';
          $title=substr($days[$i],-2);
        }
        echo '<td class="reportTableColumnHeader" colspan="' . $span . '" style="width:' . $colWidth . 'px;magin:0px;padding:0px;' . $color . '">';
        echo $title . '</td>';
        if ($format=='month') {
          $day=addMonthsToDate($day,1);
        } else {
          $day=addDaysToDate($day,$topUnit);
        }
      }      
      echo '</TR>';       
      
      // lines
      $width=round($colWidth/$colUnit) . "px;";
      foreach ($resultArray as $line) {
        $pEnd=$line['pEnd'];
        $pStart=$line['pStart'];
        $realWork=$line['realWork'];
        $plannedWork=$line['plannedWork'];
        $progress=' 0';
        if ($plannedWork>0) {
          $progress=round(100*$realWork/$plannedWork);
        } else {
          if ($line['done']) {
            $progress=100;
          }
        }
        // pGroup : is the tack a group one ?
        $pGroup=($line['elementary']=='0')?1:0;
        $compStyle="";
        $bgColor="";
        if( $pGroup) {
          $rowType = "group";
          $compStyle="font-weight: bold; background: #E8E8E8;";
          $bgColor="background: #E8E8E8;";
        } else if( $line['refType']=='Milestone'){
          $rowType  = "mile";
        } else {
          $rowType  = "row";
        }
        $wbs=$line['wbsSortable'];
        $level=(strlen($wbs)+1)/4;
        $tab=""; 
        for ($i=1;$i<$level;$i++) {
          $tab.='<span class="ganttSep">&nbsp;&nbsp;&nbsp;&nbsp;</span>';
        }
        $pName=($showWbs)?$line['wbs']." ":"";
        $pName.=$line['refName'];
        $duration=($rowType=='mile' or $pStart=="" or $pEnd=="")?'-':workDayDiffDates($pStart, $pEnd) . "&nbsp;" . i18n("shortDay");
        //echo '<TR class="dojoDndItem ganttTask' . $rowType . '" style="margin: 0px; padding: 0px;">';
        echo '<TR style="height:18px">';
        echo '  <TD class="reportTableData" style="border-right:0px;' . $compStyle . '"><img style="width:16px" src="../view/css/images/icon' . $line['refType'] . '16.png" /></TD>';
        echo '  <TD class="reportTableData" style="border-left:0px; text-align: left;' . $compStyle . '"><NOBR>' . $tab . htmlEncode($line['refName']) . '</NOBR></TD>';
        echo '  <TD class="reportTableData" style="' . $compStyle . '" >' . $duration  . '</TD>' ;
        echo '  <TD class="reportTableData" style="' . $compStyle . '" >' . percentFormatter($progress) . '</TD>' ;
        echo '  <TD class="reportTableData" style="' . $compStyle . '">'  . (($pStart)?dateFormatter($pStart):'-') . '</TD>' ;
        echo '  <TD class="reportTableData" style="' . $compStyle . '">'  . (($pEnd)?dateFormatter($pEnd):'-') . '</TD>' ;
        if ($pGroup) {
          $pColor='#505050;';
          //$pBackground='background:#505050 url(../view/img/grey.png) repeat-x;';
          $pBackground='background-color:#505050;';
        } else {         
          if (trim($line['validatedEndDate'])!="" && $line['validatedEndDate'] < $pEnd) {
            $pColor='#BB5050';
            //$pBackground='background:#BB5050 url(../view/img/red.png) repeat-x;';
            $pBackground='background-color:#BB5050;';
          } else  {
            $pColor="#50BB50";
            //$pBackground='background:#50BB50 url(../view/img/green.png) repeat-x;';
            $pBackground='background-color:#50BB50;';
          }
        }
        for ($i=0;$i<$numDays;$i++) {
          $color=$bgColor;
          $noBorder="border-left: 0px;";
          if ($format=='month') {
            $fontSize='90%';
            if ( $i<($numDays-1) and substr($days[($i+1)],-2)!='01' ) {
              $noBorder="border-left: 0px;border-right: 0px;";
            }
          } else if($format=='week') {
            $fontSize='90%';
            if ( ( ($i+1) % $colUnit)!=0) {
              $noBorder="border-left: 0px;border-right: 0px;";
            }
          } else if ($format=='day') {
            $fontSize='150%';
            $color=($openDays[$i]==1)?$bgColor:'background-color:' . $weekendColor . ';';
          }
          $height=($pGroup)?'4':'8';         
          if ($days[$i]>=$pStart and $days[$i]<=$pEnd) {
            if ($rowType=="mile") {
              echo '<td class="reportTableData" style="font-size: ' . $fontSize . ';' . $color . $noBorder . ';color:' . $pColor . ';">';
              if($progress < 100) {
                echo '&loz;' ;
              } else { 
                echo '&diams;' ;
              }
            } else {
              $subHeight=round((18-$height)/2);
              echo '<td class="reportTableData" style="width:' . $width .';padding:0px;' . $color . '; vertical-align: middle;' . $noBorder . '">';
              echo '<table width="100%" >';
              //echo '<tr style="height:' . $subHeight . 'px;"><td style="' . $noBorder . '"></td></tr>';              
              echo '<tr height="' . $height . 'px"><td style="width:100%; ' . $pBackground . 'height:' .  $height . 'px;"></td></tr>';              
              //echo '<tr style="height:' . $subHeight . 'px;"><td style="' . $noBorder . '"></td></tr>';
              echo '</table>';
            }
          } else { 
            echo '<td class="reportTableData" width="' . $width .'" style="width: ' . $width . $color . $noBorder . '">';
            //if($format=='week') {
              //echo '&nbsp;&nbsp;';
            //}
          }
          echo '</td>';
        }
        echo '</TR>';        
      }
    }
    echo "</table>"; 
  }
  
  function exportGantt($result) {
  	global $paramDbDisplayName, $currency, $currencyPosition;
  	$nl="\n";
  	$hoursPerDay=Parameter::getGlobalParameter('dayTime');
    $startDate=date('Y-m-d');
    $startTime=Parameter::getGlobalParameter('startAM') . ':00';
    $endTime=Parameter::getGlobalParameter('endPM') . ':00';
    if (array_key_exists('startDate',$_REQUEST)) {
      $startDate=$_REQUEST['startDate'];
    }
    $endDate='';
    if (array_key_exists('endDate',$_REQUEST)) {
      $endDate=$_REQUEST['endDate'];
    }
    $maxDate = '';
    $minDate = '';
    $resultArray=array();
    if (Sql::$lastQueryNbRows > 0) {
      while ($line = Sql::fetchLine($result)) {
        $pStart="";
        $pStart=(trim($line['initialStartDate'])!="")?$line['initialStartDate']:$pStart;
        $pStart=(trim($line['validatedStartDate'])!="")?$line['validatedStartDate']:$pStart;
        $pStart=(trim($line['plannedStartDate'])!="")?$line['plannedStartDate']:$pStart;
        $pStart=(trim($line['realStartDate'])!="")?$line['realStartDate']:$pStart;
        if (trim($line['plannedStartDate'])!=""
        and trim($line['realStartDate'])!=""
        and $line['plannedStartDate']<$line['realStartDate'] ) {
          $pStart=$line['plannedStartDate'];
        }
        $pEnd="";
        $pEnd=(trim($line['initialEndDate'])!="")?$line['initialEndDate']:$pEnd;
        $pEnd=(trim($line['validatedEndDate'])!="")?$line['validatedEndDate']:$pEnd;
        $pEnd=(trim($line['plannedEndDate'])!="")?$line['plannedEndDate']:$pEnd;
        $pEnd=(trim($line['realEndDate'])!="")?$line['realEndDate']:$pEnd;
        if ($line['refType']=='Milestone') {
          $pStart=$pEnd;
        }
        $line['pStart']=$pStart;
        $line['pEnd']=$pEnd;
        $line['pDuration']=workDayDiffDates($pStart,$pEnd);
        $resultArray[]=$line;
        if ($maxDate=='' or $maxDate<$pEnd) {$maxDate=$pEnd;}
        if ($minDate=='' or $minDate>$pStart) {$minDate=$pStart;}
      }
      if ($minDate<$startDate) {
        $minDate=$startDate;
      }
      if ($endDate and $maxDate>$endDate) {
        $maxDate=$endDate;
      }
    }
  	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . $nl;
    echo '<Project xmlns="http://schemas.microsoft.com/project">' . $nl;
    echo '<Title>' . $paramDbDisplayName . '</Title>' . $nl;
    echo '<ScheduleFromStart>1</ScheduleFromStart>' . $nl;
    echo '<StartDate>' . $minDate . 'T00:00:00</StartDate>' . $nl;
    echo '<FinishDate>' . $maxDate . 'T00:00:00</FinishDate>' . $nl;
    echo '<FYStartDate>1</FYStartDate>' . $nl;
    echo '<CriticalSlackLimit>0</CriticalSlackLimit>' . $nl;
    echo '<CurrencyDigits>2</CurrencyDigits>' . $nl;
    echo '<CurrencySymbol>' . $currency . '</CurrencySymbol>' . $nl;
    echo '<CurrencySymbolPosition>' . (($currencyPosition=='before')?'0':'1') . '</CurrencySymbolPosition>' . $nl;
    echo '<CalendarUID>1</CalendarUID>' . $nl;
    echo '<DefaultStartTime>' . $startTime . '</DefaultStartTime>' . $nl;
    echo '<DefaultFinishTime>' . $endTime . '</DefaultFinishTime>' . $nl;
    echo '<MinutesPerDay>' . ($hoursPerDay*60) . '</MinutesPerDay>' . $nl;
    echo '<MinutesPerWeek>' . ($hoursPerDay*60*5) . '</MinutesPerWeek>' . $nl;
    echo '<DaysPerMonth>20</DaysPerMonth>' . $nl;
    /*<DefaultTaskType>0</DefaultTaskType>
    <DefaultFixedCostAccrual>2</DefaultFixedCostAccrual>
    <DefaultStandardRate>10</DefaultStandardRate>
    <DefaultOvertimeRate>15</DefaultOvertimeRate>*/
    echo '<DurationFormat>7</DurationFormat>' . $nl;
    echo '<WorkFormat>2</WorkFormat>' . $nl;
    /*<EditableActualCosts>0</EditableActualCosts>
    <HonorConstraints>0</HonorConstraints>
    <EarnedValueMethod>0</EarnedValueMethod>
    <InsertedProjectsLikeSummary>0</InsertedProjectsLikeSummary>
    <MultipleCriticalPaths>0</MultipleCriticalPaths>
    <NewTasksEffortDriven>0</NewTasksEffortDriven>
    <NewTasksEstimated>1</NewTasksEstimated>
    <SplitsInProgressTasks>0</SplitsInProgressTasks>
    <SpreadActualCost>0</SpreadActualCost>
    <SpreadPercentComplete>0</SpreadPercentComplete>
    <TaskUpdatesResource>1</TaskUpdatesResource>
    <FiscalYearStart>0</FiscalYearStart>
    <WeekStartDay>0</WeekStartDay>
    <MoveCompletedEndsBack>0</MoveCompletedEndsBack>
    <MoveRemainingStartsBack>0</MoveRemainingStartsBack>
    <MoveRemainingStartsForward>0</MoveRemainingStartsForward>
    <MoveCompletedEndsForward>0</MoveCompletedEndsForward>
    <BaselineForEarnedValue>0</BaselineForEarnedValue>
    <AutoAddNewResourcesAndTasks>1</AutoAddNewResourcesAndTasks>*/
    echo '<CurrentDate>' . date('Y-m-dTh:i:s') . '</CurrentDate>' . $nl;
    echo '<MicrosoftProjectServerURL>1</MicrosoftProjectServerURL>' . $nl;
    echo '<Autolink>1</Autolink>' . $nl;
    echo '<NewTaskStartDate>0</NewTaskStartDate>' . $nl;
    echo '<DefaultTaskEVMethod>0</DefaultTaskEVMethod>' . $nl;
    echo '<ProjectExternallyEdited>0</ProjectExternallyEdited>' . $nl;
    echo '<ActualsInSync>0</ActualsInSync>' . $nl;
    echo '<RemoveFileProperties>0</RemoveFileProperties>' . $nl;
    echo '<AdminProject>0</AdminProject>' . $nl;
    /*<ExtendedAttributes>
        <ExtendedAttribute>
            <FieldID>188743731</FieldID>
            <FieldName>Text1</FieldName>
        </ExtendedAttribute>
    </ExtendedAttributes>*/
    echo '<Calendars>' . $nl;
    echo '<Calendar>' . $nl;
    echo ' <UID>1</UID>' . $nl;
    echo ' <IsBaseCalendar>1</IsBaseCalendar>' . $nl;
    echo ' <WeekDays>' . $nl;
    echo ' <WeekDay>' . $nl;
    echo '  <DayType>1</DayType>' . $nl;
    echo '  <DayWorking>0</DayWorking>' . $nl;
    echo ' </WeekDay>' . $nl;
    echo ' <WeekDay>' . $nl;
    echo '  <DayType>2</DayType>' . $nl;
    echo '  <DayWorking>1</DayWorking>' . $nl;
    echo '  <WorkingTimes/>' . $nl;
    echo ' </WeekDay>' . $nl;
    echo ' <WeekDay>' . $nl;
    echo '  <DayType>3</DayType>' . $nl;
    echo '  <DayWorking>1</DayWorking>' . $nl;
    echo '  <WorkingTimes/>' . $nl;
    echo ' </WeekDay>' . $nl;
    echo ' <WeekDay>' . $nl;
    echo '  <DayType>4</DayType>' . $nl;
    echo '  <DayWorking>1</DayWorking>' . $nl;
    echo '  <WorkingTimes/>' . $nl;
    echo ' </WeekDay>' . $nl;
    echo ' <WeekDay>' . $nl;
    echo '  <DayType>5</DayType>' . $nl;
    echo '  <DayWorking>1</DayWorking>' . $nl;
    echo '  <WorkingTimes/>' . $nl;
    echo ' </WeekDay>' . $nl;
    echo ' <WeekDay>' . $nl;
    echo '  <DayType>6</DayType>' . $nl;
    echo '  <DayWorking>1</DayWorking>' . $nl;
    echo '  <WorkingTimes/>' . $nl;
    echo ' </WeekDay>' . $nl;
    echo ' <WeekDay>' . $nl;
    echo '  <DayType>7</DayType>' . $nl;
    echo '  <DayWorking>0</DayWorking>' . $nl;
    echo ' </WeekDay>' . $nl;
    echo ' </WeekDays>' . $nl;
    echo '</Calendar>' . $nl;
    echo '</Calendars>' . $nl;
    echo '<Tasks>' . $nl;
    $cpt=0;
    foreach ($resultArray as $line) {
    	$cpt++;
      echo '<Task>' . $nl;
      echo ' <UID>' . $line['id'] . '</UID>' . $nl;
      echo ' <ID>' . $cpt . '</ID>' . $nl;  // TODO : should be order of the tack in the list
      echo ' <Name>' . htmlEncode($line['refName']) . '</Name>' . $nl;
      echo ' <Type>1</Type>' . $nl; // TODO : 0=Fixed Units, 1=Fixed Duration, 2=Fixed Work.
      echo ' <IsNull>0</IsNull>' . $nl;
      echo ' <WBS>' . $line['wbs'] . '</WBS>' . $nl;
      echo ' <OutlineNumber>' . $line['wbs'] . '</OutlineNumber>' . $nl;
      echo ' <OutlineLevel>' . (substr_count($line['wbs'],'.')+1) . '</OutlineLevel>' . $nl;
      echo ' <Priority>' . $line['priority'] . '</Priority>' . $nl;
      echo ' <Start>' . $line['pStart'] . 'T' . $startTime . '</Start>' . $nl;
      echo ' <Finish>' . $line['pEnd'] . 'T' . $endTime . '</Finish>' . $nl;
      echo ' <Duration>' . formatDuration($line['pDuration'],$hoursPerDay) . '</Duration>' . $nl; // TODO : to update PT112H0M0S
      echo ' <DurationFormat>7</DurationFormat>' . $nl;
      echo ' <ResumeValid>0</ResumeValid>' . $nl;
      echo ' <EffortDriven>1</EffortDriven>' . $nl;
      echo ' <Recurring>0</Recurring>' . $nl;
      /*      <OverAllocated>0</OverAllocated>' . $nl;
            <Estimated>0</Estimated>' . $nl;
            <Milestone>0</Milestone>' . $nl;
            <Summary>1</Summary>' . $nl;
            <Critical>1</Critical>' . $nl;
            <IsSubproject>0</IsSubproject>' . $nl;
            <IsSubprojectReadOnly>0</IsSubprojectReadOnly>' . $nl;
            <ExternalTask>0</ExternalTask>' . $nl;
            <FixedCostAccrual>3</FixedCostAccrual>' . $nl;
            <PercentComplete>0</PercentComplete>' . $nl;
            <RemainingDuration>PT111H39M50S</RemainingDuration>' . $nl;
            <ConstraintType>0</ConstraintType>' . $nl;
            <CalendarUID>-1</CalendarUID>' . $nl;
            <LevelAssignments>0</LevelAssignments>' . $nl;
            <LevelingCanSplit>0</LevelingCanSplit>' . $nl;
            <IgnoreResourceCalendar>0</IgnoreResourceCalendar>' . $nl;
            <HideBar>0</HideBar>' . $nl;
            <Rollup>0</Rollup>' . $nl;
            <EarnedValueMethod>0</EarnedValueMethod>' . $nl;
            /*<ExtendedAttribute>
                <FieldID>188743731</FieldID>
                <Value>lmk</Value>
            </ExtendedAttribute>*/
      echo ' <Active>1</Active>' . $nl;
      echo ' <Manual>0</Manual>' . $nl;
      echo '</Task>' . $nl;
    }
    echo '</Tasks>' . $nl;
    echo '</Project>' . $nl;
  }
  
  function formatDuration($duration, $hoursPerDay) {
    $hourDuration=$duration=$hoursPerDay;
  	$res = 'PT' . $hourDuration . 'H0M0S'; 
  	return $res;
  }
?>
