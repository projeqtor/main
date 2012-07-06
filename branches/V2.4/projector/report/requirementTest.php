<?php
include_once '../tool/projector.php';
include_once '../tool/formatter.php';
//echo 'versionReport.php';

$paramProject='';
if (array_key_exists('idProject',$_REQUEST)) {
  $paramProject=trim($_REQUEST['idProject']);
};
  
$paramProduct='';
if (array_key_exists('idProduct',$_REQUEST)) {
  $paramProduct=trim($_REQUEST['idProduct']);
};
$paramVersion='';
if (array_key_exists('idVersion',$_REQUEST)) {
  $paramVersion=trim($_REQUEST['idVersion']);
};
$paramDetail=false;
if (array_key_exists('showDetail',$_REQUEST)) {
  $paramDetail=trim($_REQUEST['showDetail']);
}

$user=$_SESSION['user'];
  
  // Header
$headerParameters="";
if ($paramProject!="") {
  $headerParameters.= i18n("colIdProject") . ' : ' . SqlList::getNameFromId('Project', $paramProject) . '<br/>';
}
if ($paramProduct!="") {
  $headerParameters.= i18n("colIdProduct") . ' : ' . SqlList::getNameFromId('Product', $paramProduct) . '<br/>';
}
if ($paramVersion!="") {
  $headerParameters.= i18n("colVersion") . ' : ' . SqlList::getNameFromId('Version', $paramVersion) . '<br/>';
}
include "header.php";

$where=getAccesResctictionClause('Requirement',false);

$order="";

if ($paramProject) {
  $lstProject=array($paramProject=>SqlList::getNameFromId('Project',$paramProject));
  $where.=" and idProject=".$paramProject;
} else {
  $lstProject=SqlList::getList('Project');
  $lstProject[0]='<i>'.i18n('undefinedValue').'</i>';
}

if ($paramProduct) {
  $lstProduct=array($paramProduct=>SqlList::getNameFromId('Product',$paramProduct));
  $where.=" and idProduct=".$paramProduct;
} else {
  $lstProduct=SqlList::getList('Product');
  $lstProduct[0]='<i>'.i18n('undefinedValue').'</i>';
}

if ($paramVersion) {
  $lstVersion=array($paramVersion=>SqlList::getNameFromId('Version',$paramVersion));
  $where.=" and idTargetVersion=".$paramVersion;
} else {
  $lstVersion=SqlList::getList('Version');
  $lstVersion[0]='<i>'.i18n('undefinedValue').'</i>';
}

$lstType=SqlList::getList('RequirementType');

$req=new Requirement();
$lst=$req->getSqlElementsFromCriteria(null, false, $where,'idProject, idProduct, idVersion, id');

if (checkNoData($lst)) exit;

echo '<table width="95%" align="center">';
echo '<tr>';
echo '<td class="reportTableHeader" style="width:8%" rowspan="2" >' . i18n('colIdProject') . '</td>';
echo '<td class="reportTableHeader" style="width:8%" rowspan="2" >' . i18n('colIdProduct') . '</td>';
echo '<td class="reportTableHeader" style="width:12%" rowspan="2" >' . i18n('colIdVersion') . '</td>';
echo '<td class="reportTableHeader" style="width:8%" rowspan="2" >' . i18n('colType') . '</td>';
//echo '<td class="reportTableHeader" style="width:4%" rowspan="2" >' . i18n('colId') . '</td>';
//echo '<td class="reportTableHeader" style="width:12%" rowpan="2" >' . i18n('colReference') . '</td>';
echo '<td class="reportTableHeader" style="width:40%" colspan="2" rowspan="2" >' . i18n('Requirement') . '</td>';
echo '<td class="reportTableHeader" style="width:25%" colspan="5" >' .  i18n('TestCase') . " / " . i18n('sectionProgress') . '</td>';
echo '</tr>';
echo '<tr>';
echo '<td class="largeReportHeader" style="width:5%;text-align:center;">' . i18n('colLinked') . '</td>';
echo '<td class="largeReportHeader" style="width:5%;text-align:center;">' . i18n('colPlanned') . '</td>';
echo '<td class="largeReportHeader" style="width:5%;text-align:center;">' . i18n('colPassed') . '</td>';
echo '<td class="largeReportHeader" style="width:5%;text-align:center;">' . i18n('colBlocked') . '</td>';
echo '<td class="largeReportHeader" style="width:5%;text-align:center;">' . i18n('colFailed') . '</td>';
echo '</tr>';
$sumPlanned=0;
$sumLinked=0;
$sumPassed=0;
$sumBlocked=0;
$sumFailed=0;
$cpt=0;
$sumReal='';
  
if ($paramDetail) {
  echo '<tr><td colspan="10" style="font-size:3px;">&nbsp;</td></tr>';
}
foreach ($lst as $req) {
  echo '<tr>';
  echo '<td class="reportTableData">' . (($req->idProject)?$lstProject[$req->idProject]:'') . '</td>';
  echo '<td class="reportTableData">' . (($req->idProduct)?$lstProduct[$req->idProduct]:'') . '</td>';
  echo '<td class="reportTableData">' . (($req->idTargetVersion)?$lstVersion[$req->idTargetVersion]:'') . '</td>';
  echo '<td class="reportTableData">' . (($req->idRequirementType)?$lstType[$req->idRequirementType]:'') . '</td>';
  echo '<td class="reportTableData">#' . $req->id . '</td>';
  echo '<td class="reportTableData" style="text-align:left;">' . $req->name . '</td>';
  echo '<td class="reportTableData">' . $req->countLinked . '</td>';
  echo '<td class="reportTableData" >' . $req->countPlanned . '</td>';
  echo '<td class="reportTableData" style="' . (($req->countPassed and $req->countPassed==$req->countPlanned)?'color:green;':'') . '">' . $req->countPassed . '</td>';
  echo '<td class="reportTableData" style="' . (($req->countBlocked)?'color:orange;':'') . '">' . $req->countBlocked . '</td>';
  echo '<td class="reportTableData" style="' . (($req->countFailed)?'color:red;':'') . '">' . $req->countFailed . '</td>';
  echo '</tr>';
  $sumLinked+=$req->countLinked;
  $sumPlanned+=$req->countPlanned;
  $sumPassed+=$req->countPassed;
  $sumBlocked+=$req->countBlocked;
  $sumFailed+=$req->countFailed;
  $cpt+=1;
  if ($paramDetail) {
  	$link=new Link();
  	$crit=array('ref1Type'=>'Requirement', 'ref1Id'=>$req->id, 'ref2Type'=>'TestCase');
  	$lst=$link->getSqlElementsFromCriteria($crit, null, null, 'ref2id');
  	if (count($lst)>0) {
	  	echo '<tr><td></td><td colspan="10">';
	  	echo '<table width="100%">';
	  	echo '<tr>';
	  	echo '<td class="largeReportHeader" colspan="2">' . i18n('TestCase') . '</td>';
	  	echo '<td class="largeReportHeader" colspan="2">' . i18n('TestSession') . '</td>';
	  	echo '<td class="largeReportHeader" colspan="2" width="10%">' . i18n('colResult') . '</td>';
	  	echo '</tr>';
	  	
  	  foreach ($lst as $link) {
        $tcr=new TestCaseRun();
        $crit=array('idTestCase'=>$link->ref2Id);
        $lstTcr=$tcr->getSqlElementsFromCriteria($crit,true, false, 'idTestSession');
        foreach ($lstTcr as $tcr) {
        	echo '<tr>';
        	echo '<td class="largeReportData" width="5%" style="text-align: center;">#' . $tcr->idTestCase . '</td>';
        	echo '<td class="largeReportData width="40%"">' . SqlList::getNameFromId('TestCase',$tcr->idTestCase) . '</td>';
        	echo '<td class="largeReportData" width="5%" style="text-align: center;">' . (($tcr->idTestSession)?'#':'') . $tcr->idTestSession . '</td>';
        	echo '<td class="largeReportData" width="45%" >' . (($tcr->idTestSession)?SqlList::getNameFromId('TestSession', $tcr->idTestSession):'') . '</td>';
          $st=new RunStatus($tcr->idRunStatus);
        	echo '<td class="largeReportData" style="text-align: center;" width="4%" >' . (($tcr->id)?colorNameFormatter(i18n($st->name) . '#split#' . $st->color):'') . '</td>';
        	echo '<td class="largeReportData" style="text-align: center;font-size:75%;" width="6%" >' . htmlFormatDate($tcr->statusDateTime, true) . '</td>';
        	echo '</tr>';
        }
      }
      echo '<tr><td colspan="6" style="font-size:3px;">&nbsp;</td></tr>';
      echo '</table>';
      echo '</td></tr>';
  	}
  }
}
echo '<tr>';
echo '<td colspan="6"></td>';
echo '<td class="largeReportHeader" >' . $sumLinked . '</td>';
echo '<td class="largeReportHeader" >' . $sumPlanned . '</td>';
echo '<td class="largeReportHeader" >' . $sumPassed . '</td>';
echo '<td class="largeReportHeader" >' . $sumBlocked . '</td>';
echo '<td class="largeReportHeader" >' . $sumFailed . '</td>';
echo '</tr>';
echo '</table>';
echo '<br/>';
