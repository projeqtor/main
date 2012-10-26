<?php
include_once "../tool/projector.php";
include_once "testTools.php";

set_time_limit(3600);

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
        and $class!='ShortType'
        //and $class>='A'and $class<'B' 
        ){
          $obj=new $class;
          if (is_subclass_of($obj, "SqlElement")) {
        	 testObject($obj);
          }
        }   
      }
    }
  }
}
testFooter();

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
		Sql::query("DELETE FROM assignment where refType='Activity' and refId=".$obj->id);
	}
	$res=$obj->delete();
  testResult($res, testCheck($res,'delete'));
	
	//Sql::commitTransaction();
}

function fillObj($obj) {
//debugLog(get_class($obj).' #'.$obj->id . "==================================");
  $dbCrit=$obj->getDatabaseCriteria();
	foreach($obj as $fld=>$val){
		$var=($obj->id)?'zzzzzzzzzzzzzzzzzzzzzzzzz':'abcdfeghijklmnopqrstuvwxy';
		$num=($obj->id)?2:1;
		$bool=($obj->id)?0:1;
		$id=($obj->id)?2:1;
		for ($i=1;$i<=4;$i++) {$var.=$var;}
		$dbType=$obj->getDataType($fld);
		$dbLength=$obj->getDataLength($fld);		
		if ($fld=='idActivity') {
			// Nothing => would lead to invalid controls
		} else if (isset($dbCrit[$fld])) {
			// Nothing : field is a database criteria : will be set automatically	
		} else if (substr($fld,0,1)=='_') {
			// Nothing
		} else if ($fld=='id' or $fld=='refType' or $fld=='refId' or $fld=='topRefType' or $fld=='topRefType' or $fld=='topId') {
			// Nothing
		} else if (substr($fld,0,1)==strtoupper(substr($fld,0,1))) {
			if (is_object($obj->$fld)) {
				//$subObj=new $fld($obj->$fld->id);
				//$obj->$fld=fillObj($subObj);
				$obj->$fld=fillObj($obj->$fld);
//debugLog($obj->$fld);
			}
		} else if ($fld=='wbs' or $fld=='wbsSortable') {
			$obj->$fld=null;
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