<?php 
//echo "globalCostPlanning.php";
include_once '../tool/projector.php';
$idProject="";
if (array_key_exists('idProject',$_REQUEST) and trim($_REQUEST['idProject'])!="") {
  $idProject=trim($_REQUEST['idProject']);
}
$scale='month';
if (array_key_exists('scale',$_REQUEST)) {
  $scale=$_REQUEST['scale'];
}

$headerParameters="";
if ($idProject!="") {
  $headerParameters.= i18n("colIdProject") . ' : ' . SqlList::getNameFromId('Project',$idProject) . '<br/>';
}
include "header.php";

$accessRightRead=securityGetAccessRight('menuProject', 'read');
  
$user=$_SESSION['user'];
$queryWhere=getAccesResctictionClause('Activity','w');

if ($idProject!='') {
  $queryWhere.=  " and w.idProject in " . getVisibleProjectsList(true, $idProject) ;
} else {
  //$queryWhere.=  "and w.idProject in " . getVisibleProjectsList() ;
}
  
$querySelect1= 'select sum(w.cost) as sumCost, w.' . $scale . ' as scale , w.idProject'; 
$queryGroupBy1 = 'w.'.$scale . ', w.idProject';
$queryWhere1 = $queryWhere;

$querySelect2= 'select sum(w.work * a.dailyCost) as sumCost, w.' . $scale . ' as scale , w.idProject'; 
$queryGroupBy2 = $scale . ', w.idProject';
$queryWhere2 = $queryWhere . ' and w.idAssignment=a.id ';
// constitute query and execute

$tab=array();
$start="";
$end="";
for ($i=1;$i<=2;$i++) {
  $obj=($i==1)?new Work():new PlannedWork();
  $ass=new Assignment();
  $var=($i==1)?'real':'plan';
  $querySelect=($i==1)?$querySelect1:$querySelect2;
  $queryGroupBy=($i==1)?$queryGroupBy1:$queryGroupBy2;
  $queryWhere=($i==1)?$queryWhere1:$queryWhere2;
  //$queryFrom=($i==1)?$queryFrom1:$queryFrom2;
  $queryWhere=($queryWhere=='')?' 1=1':$queryWhere;
  $query=$querySelect 
     . ' from ' . $obj->getDatabaseTableName().' w '.(($i==2)?', '.$ass->getDatabaseTableName() . ' a':'') 
     . ' where ' . $queryWhere
     . ' group by ' . $queryGroupBy; 
  $result=Sql::query($query);
  while ($line = Sql::fetchLine($result)) {
    $date=$line['scale'];
    $proj=$line['idProject'];
    $cost=round($line['sumCost'],2);
    if (! array_key_exists($proj, $tab) ) {
      $tab[$proj]=array("name"=>SqlList::getNameFromId('Project', $proj), "real"=>array(),"plan"=>array());
    }
    $tab[$proj][$var][$date]=$cost;
    if ($start=="" or $start>$date) {
      $start=$date;
    }
    if ($end=="" or $end<$date) {
      $end=$date;
    }
  }
}
if (checkNoData($tab)) exit;

$arrDates=array();
$arrYear=array();
$date=$start;
while ($date<=$end) {
  $arrDates[]=$date;
  $year=substr($date,0,4);
  if (! array_key_exists($year,$arrYear)) {
    $arrYear[$year]=0;
  }
  $arrYear[$year]+=1;
  if ($scale=='week') {
    $day=date('Y-m-d',firstDayofWeek(substr($date,4,2),substr($date,0,4)));
    $next=addWeeksToDate($day,1);
    $date=str_replace('-','', weekFormat($next));
  } else {
    $day=substr($date,0,4) . '-' . substr($date,4,2) . '-01';
    $next=addMonthsToDate($day,1);
    $date=substr($next,0,4) . substr($next,5,2);
  }
}
// Header
$plannedBGColor='#FFFFDD';
$plannedFrontColor='#777777';
$plannedStyle=' style="width:20px;text-align:center;background-color:' . $plannedBGColor . '; color: ' . $plannedFrontColor . ';" ';

echo "<table width='95%' align='center'><tr>";
echo '<td><table width="100%" align="left"><tr>';
echo "<td class='reportTableDataFull' style='width:20px; text-align:center;'>";
echo "1";
echo "</td><td width='100px' class='legend'>" . i18n('colRealWork') . "</td>";
echo "<td width='5px'>&nbsp;&nbsp;&nbsp;</td>";
echo '<td class="reportTableDataFull" ' . $plannedStyle . '>';
echo "<i>1</i>";
echo "</td><td width='100px' class='legend'>" . i18n('colPlannedWork') . "</td>";
echo "<td>&nbsp;</td>";
echo "</tr></table>";
echo "<br/>";
echo '<table width="100%" align="center">';
echo '<tr rowspan="2">';
echo '<td class="reportTableHeader" rowspan="2">' . i18n('Project') . '</td>';
foreach ($arrYear as $year=>$nb) {
  echo '<td class="reportTableHeader" colspan="' . $nb . '">' . $year . '</td>';
}
echo '<td class="reportTableHeader" rowspan="2">' . i18n('sum') . '</td>';
echo '</tr>';
echo '<tr>';
$arrSum=array();
foreach ($arrDates as $date) {
  echo '<td class="reportTableColumnHeader" >';
  echo substr($date,4,2); 
  echo '</td>';
  $arrSum[$date]=0;
} 
echo '</tr>';
asort($tab);
$sumProj=array();
foreach($tab as $proj=>$lists) {
  $sumProj[$proj]=array();
  for ($i=1; $i<=2; $i++) {
    if ($i==1) {
      echo '<tr><td class="reportTableLineHeader" rowspan="2">' . $lists['name'] . '</td>';
      $style='';
      $mode='real';
      $ital=false;
    } else {
      echo '<tr>';
      $style=$plannedStyle;
      $mode='plan';
      $ital=true;
    }
    $sum=0;
    foreach($arrDates as $date) {
      if ($i==1) {
        $sumProj[$proj][$date]=0;
      }
      $val="";
      if (array_key_exists($mode, $lists) and array_key_exists($date,$lists[$mode])) {
        $val=$lists[$mode][$date];
      }
      echo '<td class="reportTableData" ' . $style . '>';
      echo ($ital)?'<i>':'';
      echo htmlDisplayCurrency($val);
      echo ($ital)?'</i>':'';
      $sum+=$val;
      $arrSum[$date]+=$val;
      echo '</td>';
      $sumProj[$proj][$date]+=$val;
    }
    echo '<td class="reportTableColumnHeader" style="text-align:right;">';
    echo ($ital)?'<i>':'';
    echo htmlDisplayCurrency($sum);
    echo ($ital)?'</i>':'';
    echo '</td>';
    echo '</tr>';
    
  }
}
echo "<tr><td>&nbsp;</td></tr>";
echo '<tr><td class="reportTableHeader" >' . i18n('sum') . '</td>';
$sum=0;
$cumul=array();
foreach ($arrSum as $date=>$val) {
  echo '<td class="reportTableHeader" >' . htmlDisplayCurrency($val) . '</td>';
  $sum+=$val;
  $cumul[$date]=$sum;
}
echo '<td class="reportTableHeader" >' . htmlDisplayCurrency($sum) . '</td>';
echo '</tr>';
echo '</table>';
echo '</td></tr></table>';
// Graph
if (! testGraphEnabled()) { return;}
  include("../external/pChart/pData.class");  
  include("../external/pChart/pChart.class");  
$dataSet=new pData;
foreach($sumProj as $id=>$vals) {
  $dataSet->AddPoint($vals,$id);
  $dataSet->SetSerieName($tab[$id]['name'],$id);
  $dataSet->AddSerie($id);
}
$arrLabel=array();
foreach($arrDates as $date){
  $arrLabel[]=substr($date,0,4) . '-' . substr($date,4,2);
}
$dataSet->AddPoint($arrLabel,"dates");  
$dataSet->SetAbsciseLabelSerie("dates");   
$width=700;
$graph = new pChart($width,260);  
$graph->setFontProperties("../external/pChart/Fonts/tahoma.ttf",10);
$graph->drawRoundedRectangle(5,5,$width-5,258,5,230,230,230);  
$graph->setGraphArea(40,30,$width-200,200);  
$graph->drawGraphArea(252,252,252);  
$graph->setFontProperties("../external/pChart/Fonts/tahoma.ttf",8);  
$graph->drawScale($dataSet->GetData(),$dataSet->GetDataDescription(),SCALE_ADDALL,0,0,0,TRUE,90,1, true);  
$graph->drawGrid(5,TRUE,230,230,230,255);  
$graph->drawStackedBarGraph($dataSet->GetData(),$dataSet->GetDataDescription(),TRUE);  
$graph->setFontProperties("../external/pChart/Fonts/tahoma.ttf",8);  
$graph->drawLegend($width-150,35,$dataSet->GetDataDescription(),240,240,240);  

$graph->clearScale();
$serie=0;  
foreach($sumProj as $id=>$vals) {
  $serie+=1;
  $dataSet->RemoveSerie($id);
}
$dataSet->AddPoint($cumul,"sum");
$dataSet->SetSerieName(i18n("cumulated"),"sum");  
$dataSet->AddSerie("sum");
$dataSet->SetYAxisName(i18n("cumulated"));
$graph->setFontProperties("../external/pChart/Fonts/tahoma.ttf",8);
$graph->setColorPalette($serie,0,0,0);  
$graph->drawRightScale($dataSet->GetData(),$dataSet->GetDataDescription(),SCALE_START0,0,0,0,true,90,1, true);
$graph->drawLineGraph($dataSet->GetData(),$dataSet->GetDataDescription());  
$graph->drawPlotGraph($dataSet->GetData(),$dataSet->GetDataDescription(),3,2,255,255,255);  

$imgName=getGraphImgName("globalCostPlanning");
$graph->Render($imgName);
echo '<table width="95%" align="center"><tr><td align="center">';
echo '<img src="' . $imgName . '" />'; 
echo '</td></tr></table>';
echo '<br/>';
?>
