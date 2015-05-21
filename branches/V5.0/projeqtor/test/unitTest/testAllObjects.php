<?php
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2015 Pascal BERNARD - support@projeqtor.org
 * Contributors : -
 * 
 * This file is part of ProjeQtOr.
 * 
 * ProjeQtOr is free software: you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free 
 * Software Foundation, either version 3 of the License, or (at your option) 
 * any later version.
 * 
 * ProjeQtOr is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for 
 * more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * ProjeQtOr. If not, see <http://www.gnu.org/licenses/>.
 *
 * You can get complete code of ProjeQtOr, other resource, help and information
 * about contributors at http://www.projeqtor.org 
 *     
 *** DO NOT REMOVE THIS NOTICE ************************************************/

include_once "../tool/projeqtor.php";
include_once "testTools.php";
header ('Content-Type: text/html; charset=UTF-8');
projeqtor_set_time_limit(3600);

// PREPARE TESTS
// => remove mail sending, to avoid spamming
//Sql::query('UPDATE statusmail set idle=1');

$classDir="../model/";
testHeader('ALL OBJECTS');
if (is_dir($classDir)) {
  if ($dirHandler = opendir($classDir)) {
    while (($file = readdir($dirHandler)) !== false) {
      if ($file!="." and $file!=".." and filetype($classDir . $file)=="file") {
        $split=explode('.',$file);
        $class=$split[0];
        if ($class!='GeneralWork' and $class!='index' and $class!='Mutex' and $class!='NumberFormatter52'
        and $class!='ShortType' and $class!='ImapMailbox' and $class!='ContextType'
        and substr($class,-4)!='Main' and $class!='_securityCheck'
        //and $class>='Ti' // and $class<'B' 
        ){
          $obj=new $class();
          if (is_subclass_of($obj, "SqlElement")) {
        	 testObject($obj);
          }
        }   
      }
    }
  }
}
testFooter();
testSummary();

function testObject($obj) {
	testTitle(get_class($obj));
 //return;
	//Sql::beginTransaction();
	
	testSubTitle('Create');
	$obj=fillObj($obj);
	$res=$obj->save();
	testResult($res, testCheck($res,'insert'));
	
	testSubTitle('Update');
	$obj=fillObj($obj);
	$res=$obj->save();
  testResult($res, testCheck($res,'update'));
	
	testSubTitle('Delete');
	if (get_class($obj)=='Activity') {
		$ass=new Assignment();
		Sql::query("DELETE FROM ".$ass->getDatabaseTableName()." where refType='Activity' and refId=".$obj->id);
	}
	$res=$obj->delete();
  testResult($res, testCheck($res,'delete'));
	
	//Sql::commitTransaction();
}

function fillObj($obj) {
  $dbCrit=$obj->getDatabaseCriteria();
	foreach($obj as $fld=>$val){
		$var=($obj->id)?'zzzzzzzzzzzzzzzzzzzzzzzzz':'abcdfeghijklmnopqrstuvwxy';
		$num=(substr($fld,0,4)=='real' and substr(get_class($obj),-7)!='Expense')?0:(($obj->id)?2:1);
		$bool=($obj->id)?0:1;
		$id=($obj->id)?2:1;
		for ($i=1;$i<=4;$i++) {$var.=$var;}
		$dbType=$obj->getDataType($fld);
		$dbLength=$obj->getDataLength($fld);		
		if ($fld=='idActivity' or $fld=='idRequirement' or $fld=='idTestCase') {
			// Nothing => would lead to invalid controls
		} else if (isset($dbCrit[$fld])) {
			// Nothing : field is a database criteria : will be set automatically	
		} else if (substr($fld,0,1)=='_') {
			// Nothing
		} else if ($fld=='refType' or $fld=='originType') {
			$pos=strpos(get_class($obj),'PlanningElement');
			if ($pos>0) {
				$obj->$fld=substr(get_class($obj),0,$pos);
			} else {
			  $obj->$fld='Project';
			}
		} else if ($fld=='refId') {
			$obj->$fld='9999999999';
		} else if ($fld=='id' or $fld=='topRefType' or $fld=='topRefType' or $fld=='topId') {
			// Nothing
		} else if (substr($fld,0,1)==strtoupper(substr($fld,0,1))) {
			if (is_object($obj->$fld)) {
				//$subObj=new $fld($obj->$fld->id);
				//$obj->$fld=fillObj($subObj);
				$obj->$fld=fillObj($obj->$fld);
			}
		} else if ($obj->id and $fld=='idBill') {
			$obj->$fld=null;
		} else if ($fld=='wbs' or $fld=='wbsSortable') {
			$obj->$fld=null;
		} else if ($fld=='predecessorRefType') {
			$obj->$fld='Activity';
	  } else if ($fld=='successorRefType') {
	    $obj->$fld='Milestone';
		} else if ($dbType=='varchar') {			 
	    $obj->$fld=substr($var,0,$dbLength);
		} else if ($dbType=='int' and $dbLength==1) {
			$obj->$fld=0;
		} else if ($dbType=='int' and $dbLength==12 and substr($fld,0,2)=='id' and $fld!='id') {
      $obj->$fld=$id;
		} else if (($dbType=='int' or $dbType=='decimal') and $fld!='id') {
      $obj->$fld=($dbLength=='1')?$bool:$num;
    } else if (($dbType=='date' )) {
      $obj->$fld=date('Y-m-d');
    } else if (($dbType=='datetime' )) {
      $obj->$fld=date('Y-m-d H:i:s');
		}
	}
	return $obj;
}