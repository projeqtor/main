<?php 
/** ============================================================================
 * Project is the main object of the project managmement.
 * Almost all other objects are linked to a given project.
 */ 
class PlannedWork extends Work {

  public $_noHistory;
    
  // List of fields that will be exposed in general user interface
  
  // Define the layout that will be used for lists
  private static $_layout='
    <th field="id" formatter="numericFormatter" width="10%" ># ${id}</th>
    <th field="nameResource" width="35%" >${resourceName}</th>
    <th field="nameProject" width="35%" >${projectName}</th>
    <th field="rate" width="15%" formatter="percentFormatter">${rate}</th>  
    <th field="idle" width="5%" formatter="booleanFormatter" >${idle}</th>
    ';

  
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL) {
    parent::__construct($id);
  }

   /** ==========================================================================
   * Destructor
   * @return void
   */ 
  function __destruct() {
    parent::__destruct();
  }

// ============================================================================**********
// GET STATIC DATA FUNCTIONS
// ============================================================================**********
  
  /** ==========================================================================
   * Return the specific layout
   * @return the layout
   */
  protected function getStaticLayout() {
    return self::$_layout;
  }


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
      $colScript .= '    if (dijit.byId("PlanningElement_realEndDate").get("value")==null) {';
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
  
  public static function plan($projectId, $startDate) {
//echo "<br/>******************************";
//echo "<br/>PLANNING - Started at " . date('H:i:s');
//echo "<br/>******************************";
    set_time_limit(300);

    $withProjectRepartition=true;
    $result="";
    $startTime=time();
    $startMicroTime=microtime(true);
    $globalMaxDate=date('Y')+3 . "-12-31"; // Don't try to plan after Dec-31 of current year + 3
    $globalMinDate=date('Y')-1 . "-01-01"; // Don't try to plan before Jan-01 of current year -1
    
    $arrayPlannedWork=array();
    $arrayAssignment=array();
    $arrayPlanningElement=array();
    
    // build in list to get a where clause : "idProject in ( ... )" 
    $proj=new Project($projectId);
    $inClause="idProject in " . transformListIntoInClause($proj->getRecursiveSubProjectsFlatList(true, true));  
    // Purge existing planned work
    $plan=new PlannedWork();
    $plan->purge($inClause);
    // Get the list of all PlanningElements to plan (includes Activity and/or Projects)
    $pe=new PlanningElement();
    $clause=$inClause . " and idle=0";
    $order=" priority asc, wbsSortable asc";
    $list=$pe->getSqlElementsFromCriteria(null,false,$clause,$order,true);
    $listPlan=self::sortPlanningElements($list);
    $resources=array();
    $a=new Assignment();
    $topList=array();
    // Treat each PlanningElement
    foreach ($listPlan as $idPlan=>$plan) {
      //$changedPlan=false;
      // Determine planning profile
      $profile="ASAP";
      $startPlan=$startDate;
      $endPlan=null;
      $step=1;
      if (! $plan->idPlanningMode) {
        $profile="ASAP";
      } else {
        $pm=new PlanningMode($plan->idPlanningMode);
        $profile=$pm->code;  
      }
      if ($profile=="REGUL" or $profile=="FULL" 
       or $profile=="HALF" ) { // Regular planning
        $startPlan=$plan->validatedStartDate;
        $endPlan=$plan->validatedEndDate;
        $step=1;
      } else if ($profile=="FDUR") { // Fixed duration
      	if ($plan->validatedStartDate) { 
      	  $startPlan=$plan->validatedStartDate;
      	}
        $step=1;
      } else if ($profile=="ASAP") { // As soon as possible
        $startPlan=$plan->validatedStartDate;
        $endPlan=null;
        $step=1;
      } else if ($profile=="ALAP") { // As late as possible (before end date)
          $startPlan=$plan->validatedEndDate;
          $endPlan=$startDate;
          $step=-1;
      } else if ($profile=="FLOAT") { // Floating milestone
        $startPlan=$startDate;
        $endPlan=null;
        $step=1;
      } else if ($profile=="FIXED") { // Fixed milestone
        $startPlan=$plan->validatedEndDate;
        $endPlan=$plan->validatedEndDate;
        $plan->plannedStartDate=$plan->validatedEndDate;
        $plan->plannedEndDate=$plan->validatedEndDate;
        $listPlan=self::storeListPlan($listPlan,$plan);
        //$plan->save();
        $step=1;
      } else {
        $profile=="ASAP"; // Default is ASAP
        $startPlan=$startDate;
        $endPlan=null;
        $step=1;
      }
      // If dependencies exist,
      $dep=new Dependency(); 
      $precList=$dep->getSqlElementsFromCriteria(array("successorId"=>$plan->id),false);
      foreach ($precList as $precDep) {
        $prec=null;
        foreach ($listPlan as $tstPrec) {
          if ($tstPrec->id==$precDep->predecessorId) {
            $prec=$tstPrec;
            break;
          }
        }
        if ($prec==null) {
          $prec=new PlanningElement($precDep->predecessorId);
        }
        if ($prec->plannedEndDate > $startPlan) {        
          if ($prec->refType=='Milestone') {
          	if ($plan->refType=='Milestone') {
          	  $startPlan=addWorkDaysToDate($prec->plannedEndDate,1);
          	} else {
              $startPlan=addWorkDaysToDate($prec->plannedEndDate,1);
            }         	
          } else {
          	if ($plan->refType=='Milestone') {
          	  $startPlan=addWorkDaysToDate($prec->plannedEndDate,1);
          	} else {
              $startPlan=addWorkDaysToDate($prec->plannedEndDate,2);
            }           
          }
        }
      }
      if ($plan->refType=='Milestone') {
        if ($profile!="FIXED") {
        	if (count($precList)>0) {
            $plan->plannedStartDate=addWorkDaysToDate($startPlan,2);
        	} else {
        		$plan->plannedStartDate=addWorkDaysToDate($startPlan,1);
        	}
          $plan->plannedEndDate=$plan->plannedStartDate;
          $plan->plannedDuration=0;
          //$plan->save();
          $listPlan=self::storeListPlan($listPlan,$plan);
        }
        if ($profile=="FIXED") {
        	$plan->plannedEndDate=$plan->validatedEndDate;
        	$plan->plannedDuration=0;
          //$plan->save();
          $listPlan=self::storeListPlan($listPlan,$plan);
        }
      } else {        
        if (! $plan->realStartDate) {
          $plan->plannedStartDate=($plan->leftWork>0)?null:$startPlan;
        }
        if (! $plan->realEndDate) {
          $plan->plannedEndDate=($plan->leftWork>0)?null:$startPlan;
        }
        if ($profile=="FDUR") {
          if (! $plan->realStartDate) {
            $plan->plannedStartDate=$startPlan;
            $endPlan=addWorkDaysToDate($startPlan,$plan->validatedDuration);
          } else {
            $endPlan=addWorkDaysToDate($plan->realStartDate,$plan->validatedDuration);
          }
          if (! $plan->realEndDate) {
            $plan->plannedEndDate=$endPlan;
          }
          $listPlan=self::storeListPlan($listPlan,$plan);
          $plan->save();
        }
        // get list of top project to chek limit on each project
        if ($withProjectRepartition) {
          $proj = new Project($plan->idProject);
          $listTopProjects=$proj->getTopProjectList(true);
        }
        //$minDate=$globalMaxDate;
        //$maxDate=addDaysToDate($startDate,-1);
        $crit=array("refType"=>$plan->refType, "refId"=>$plan->refId);
        $listAss=$a->getSqlElementsFromCriteria($crit,false);        
        foreach ($listAss as $ass) {
          $changedAss=true;
          $ass->plannedStartDate=null;
          $ass->plannedEndDate=null;
          $r=new Resource($ass->idResource);
          $capacity=($r->capacity)?$r->capacity:1;
          if (array_key_exists($ass->idResource,$resources)) {
            $ress=$resources[$ass->idResource];
          } else {
            $ress=$r->getWork($startDate, $withProjectRepartition);        
          }
          if ($startPlan>$startDate) {
            $currentDate=$startPlan;
          } else {
            $currentDate=$startDate;
            if ($step==-1) {
              $step=1;
            }
          }
          $assRate=1;
          if ($ass->rate) {
            $assRate=$ass->rate / 100;
          }
          // Get data to limit to affectation on each project           
          if ($withProjectRepartition) {
            foreach ($listTopProjects as $idProject) {
              $projKey='Project#' . $idProject;
              if (! array_key_exists($projKey,$ress)) {
                $ress[$projKey]=array();
              }
              if (! array_key_exists('rate',$ress[$projKey])) {
                $ress[$projKey]['rate']=$r->getAffectationRate($idProject);
              }
            }
          }
          //$projRate=$ress['Project#' . $ass->idProject]['rate'];
          $capacityRate=round($assRate*$capacity,2);
          $left=$ass->leftWork;
          $regul=false;
          if ($profile=="REGUL" or $profile=="FULL" or $profile=="HALF" or $profile="FDUR") {
            $delai=workDayDiffDates($currentDate,$endPlan);
            if ($delai and $delai>0) { 
              $regul=$plan->leftWork/$delai;
              $regulDone=0;
              $interval=0;
            }
          }
          while (1) {            
            if ($left<0.01) {
              break;
            }
            // Set limits to avoid eternal loop
            if ($currentDate==$globalMaxDate) { break; }         
            if ($currentDate==$globalMinDate) { break; }
            if ($ress['Project#' . $plan->idProject]['rate']==0) { break ; }
            if (isOpenDay($currentDate)) {
              $planned=0;
              $week=weekFormat($currentDate);
              if (array_key_exists($currentDate, $ress)) {
                $planned=$ress[$currentDate];
              }
              if ($regul) {
                  $interval+=$step;
              }
              if ($planned < $capacity)  {
                $value=$capacity-$planned; 
                 if ($value>$capacityRate) {
                 	 $value=$capacityRate;
                 }
                if ($withProjectRepartition) {
                  foreach ($listTopProjects as $idProject) {
                    $projectKey='Project#' . $idProject;
                    $plannedProj=0;
                    $rateProj=1;
                    if (array_key_exists($week,$ress[$projectKey])) {
                      $plannedProj=$ress[$projectKey][$week];
                    }
                    $rateProj=$ress[$projectKey]['rate'] / 100;
                    $leftProj=round(5*$capacity*$rateProj,2)-$plannedProj; // capacity for a week
                    if ($value>$leftProj) {
                      $value=$leftProj;
                    }
                  }
                }
                $value=($value>$left)?$left:$value; 
                if ($regul) {
                  $regulTarget=round( ($interval*$regul) ,1);
                  $toPlan=$regulTarget-$regulDone;
                  if ($value>$toPlan) {
                    $value=$toPlan;
                  }
                  if ($profile=="FULL" and $toPlan<1 and $interval<$delai) {
                    $value=0;
                  }
                  if ($profile=="HALF" and $interval<$delai) {
                    if ($toPlan<0.5) {
                      $value=0;
                    } else {
                      $value=0.5;
                    }
                  }
                  $regulDone+=$value;
                }
                if ($value>=0.01) {             
                  $plannedWork=new PlannedWork();
                  $plannedWork->idResource=$ass->idResource;
                  $plannedWork->idProject=$ass->idProject;
                  $plannedWork->refType=$ass->refType;
                  $plannedWork->refId=$ass->refId;
                  $plannedWork->idAssignment=$ass->id;
                  $plannedWork->work=$value;
                  $plannedWork->setDates($currentDate);
                  $arrayPlannedWork[]=$plannedWork;
                  $ass->plannedWork+=$value;
                  if (! $ass->plannedStartDate or $ass->plannedStartDate>$currentDate) {
                    $ass->plannedStartDate=$currentDate;
                  }
                  if (! $ass->plannedEndDate or $ass->plannedEndDate<$currentDate) {
                    $ass->plannedEndDate=$currentDate;
                  }
                  if (! $plan->plannedStartDate or $plan->plannedStartDate>$currentDate) {
                    $plan->plannedStartDate=$currentDate;
                  }
                  if (! $plan->plannedEndDate or $plan->plannedEndDate<$currentDate) {
                    $plan->plannedEndDate=$currentDate;
                  }
                  $changedAss=true;
                  $left-=$value;
                  $ress[$currentDate]=$value+$planned;
                  // Set value on each project (from current to top)
                  if ($withProjectRepartition and $value >= 0.01) {
                    foreach ($listTopProjects as $idProject) {
                      $projectKey='Project#' . $idProject;
                      $plannedProj=0;
                      if (array_key_exists($week,$ress[$projectKey])) {
                        $plannedProj=$ress[$projectKey][$week];
                      }
                      $ress[$projectKey][$week]=$value+$plannedProj;               
                    }
                  }
                }
              }            
            }
            $currentDate=addDaysToDate($currentDate,$step);
            if ($currentDate<$startDate and $step=-1) {
              $currentDate=$startPlan;
              $step=1;
            }
          }
          if ($changedAss) {
            $ass->_noHistory=true;    
            $arrayAssignment[]=$ass;
          }
          $resources[$ass->idResource]=$ress;
        } 
      }
      $listPlan=self::storeListPlan($listPlan,$plan);
    }
    $cpt=0;
    $query='';
    foreach ($arrayPlannedWork as $pw) {
      if ($cpt==0) {
        $query='INSERT into ' . $pw->getDatabaseTableName() 
          . ' (`idResource`,`idProject`,`refType`,`refId`,`idAssignment`,`work`,`workDate`,`day`,`week`,`month`,`year`)'
          . ' VALUES ';
      } else {
        $query.=', ';
      }
      $query.='(' 
        . "'" . $pw->idResource . "',"
        . "'" . $pw->idProject . "',"
        . "'" . $pw->refType . "',"
        . "'" . $pw->refId . "',"
        . "'" . $pw->idAssignment . "',"
        . "'" . $pw->work . "',"
        . "'" . $pw->workDate . "',"
        . "'" . $pw->day . "',"
        . "'" . $pw->week . "',"
        . "'" . $pw->month . "',"
        . "'" . $pw->year . "')";
      $cpt++; 
      if ($cpt>=100) {
        $query.=';';
        SqlDirectElement::execute($query);
        $cpt=0;
        $query='';
      }
    }
    if ($query!='') {
      $query.=';';
      SqlDirectElement::execute($query);
    }
    
    // save Assignment
    foreach ($arrayAssignment as $ass) {
      $ass->save();
    }
    // save PlanningElements with no assignments 
    foreach ($listPlan as $pe) {
      if (! $pe->elementary or $pe->refType=="Milestone") { 
    	  $pe->save();
      }
    }
    // Recalculate not elementary items
    $clause.=" and elementary=0";
    $order="wbsSortable desc";
    $list=$pe->getSqlElementsFromCriteria(null,false,$clause,$order,true);
    foreach ($list as $plan) {
    	PlanningElement::updateSynthesis($plan->refType, $plan->refId);
    }
    
    $endTime=time();
    $endMicroTime=microtime(true);

    $duration = round(($endMicroTime - $startMicroTime)*1000)/1000;
    $result=i18n('planDone', array($duration));
    $result .= '<input type="hidden" id="lastPlanStatus" value="OK" />';

    return $result;
  }
  
  private static function storeListPlan($listPlan,$plan) {
  	$listPlan[$plan->id]=$plan;
  	if ($plan->topId and array_key_exists($plan->topId, $listPlan)) {
  		$top=$listPlan[$plan->topId];
  		if (!$top->plannedStartDate or $top->plannedStartDate>$plan->plannedStartDate) {
  			$top->plannedStartDate=$plan->plannedStartDate;
  		}
  	  if (! $top->plannedEndDate or $top->plannedEndDate<$plan->plannedEndDate) {
        $top->plannedEndDate=$plan->plannedEndDate;
      }
      $listPlan[$top->id]=$top;
  	}
  	return $listPlan;
  }
  
  private static function sortPlanningElements($planList) {
    $result=array();
    foreach ($planList as $key=>$plan) {
      if ( ! array_key_exists ($key,$result)) {
        $predList=$plan->getPredecessorItemsArray();
        if (count($predList)==0) {
          $result[$key]=$plan;
        } else {
          $tempList=array();
          foreach ($planList as $tmpKey=>$tmpPlan) {
            if (array_key_exists($tmpKey,$predList)) {
              $tempList[$tmpKey]=$tmpPlan;
            }
          }
          $result=array_merge($result,self::sortPlanningElements($tempList));
          $result[$key]=$plan;
        }
      }
    }
    return $result;
  }
}
?>