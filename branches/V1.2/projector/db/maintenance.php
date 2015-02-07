<?php
$maintenance=true;
// Version History : starts at 0.3.0 with clean database (before scripts are empty)
$versionHistory = array(
  "V0.3.0",
  "V0.4.0",
  "V0.5.0",
  "V0.6.0",
  "V0.7.0",
  "V0.8.0",
  "V0.9.0",
  "V1.0.0",
  "V1.1.0",
  "V1.2.0");
$versionParameters =array(
  'V1.2.0'=>array('paramMailSmtpServer'=>'localhost',
                 'paramMailSmtpPort'=>'25',
                 'paramMailSendmailPath'=>null,
                 'paramMailTitle'=>'[Project\'Or RIA] ${item} #${id} moved to status ${status}',
                 'paramMailMessage'=>'The status of ${item} #${id} [${name}] has changed to ${status}',
                 'paramMailShowDetail'=>'true' ) );
$SqlEndOfCommand=";";
$SqlComment="--";
   
require_once (dirname(__FILE__) . '/../tool/projector.php');

$nbErrors=0;
$currVersion=Sql::getDbVersion();
traceLog("");
traceLog("=====================================");
traceLog("");
traceLog("DataBase actual Version = " . $currVersion );
traceLog("ProjectOR actual Version = " . $version );
traceLog("");
if ($currVersion=="") {
  $currVersion='0.0.0';
  // if no current version, parameters are set through config.php
  $versionParameters=array(); // Clear $versionParameter to avoid dupplication of parameters
}
$arrVers=explode('.',substr($currVersion,1));
$currVer=$arrVers[0];
$currMaj=$arrVers[1];
$currRel=$arrVers[2];

foreach ($versionHistory as $vers) {
  $arrVers=explode('.',substr($vers,1));
  $histVer=$arrVers[0];
  $histMaj=$arrVers[1];
  $histRel=$arrVers[2];
  if ( $histVer > $currVer 
  or ( $histVer == $currVer and $histMaj > $currMaj)
  or ( $histVer == $currVer and $histMaj == $currMaj and $histRel > $currRel) ) {
    $nbErrors+=runScript($vers);
  }
}

if ($currVersion=='0.0.0') {
  debugLog ("create default project");
  $proj=new Project();
  $proj->color='#0000FF';
  $proj->description='Default project' . "\n" .
                     'For example use only.' . "\n" .
                     'Remove or rename this project when initializing your own data.';
  $proj->name='Default project';
  $result=$proj->save();
  debugLog($result);
}
Sql::saveDbVersion($version);
traceLog('=====================================');
traceLog("");
echo "____________________________________________";
echo "<br/><br/>";
if ($nbErrors==0) {
  traceLog("DATABASE UPDATE COMPLETED TO VERSION " . Sql::getDbVersion() );
  echo "DATABASE UPDATE COMPLETED TO VERSION " . Sql::getDbVersion();
} else {
  traceLog($nbErrors . " ERRORS DURING UPDATE TO VERSION " . Sql::getDbVersion() );
  echo $nbErrors . " ERRORS DURING UPDATE TO VERSION " . Sql::getDbVersion() . "<br/>";
  echo "DETAILS CAN BE FOUND IN LOG FILE.";
}
traceLog("");
traceLog("=====================================");
traceLog("");
echo "<br/>____________________________________________";


function runScript($vers) {
  global $paramDbName, $paramDbPrefix, $versionParameters, $parametersLocation;
  set_time_limit(300);
  traceLog("=====================================");
  traceLog("");
  traceLog("VERSION " . $vers);
  traceLog("");
  $handle = @fopen("../db/Projector_" . $vers . ".sql", "r");
  $query="";
  $nbError=0;
  if ($handle) {
    while (!feof($handle)) {
        $buffer = fgets($handle);
        $buffer=trim($buffer);
        $buffer=str_replace('${database}', $paramDbName, $buffer);
        $buffer=str_replace('${prefix}', $paramDbPrefix, $buffer);
        if ( substr($buffer,0,2)=='--' ) {
          $buffer=''; // remove comments
        }
        if ($buffer!='') {
          $query.=$buffer . "\n";
        }
        if ( substr($buffer,strlen($buffer)-1,1)==';' ) {
          $result=Sql::query($query);
          if ( ! $result ) {
            traceLog( "<br/>***** SQL ERROR WHILE EXECUTING SQL REQUEST *****");
            traceLog("");
            traceLog(Sql::$lastQueryErrorMessage);
            traceLog("");
            traceLog("*************************************************");
            traceLog("");
            $nbError++;
          } else {
            $action="";
            if (substr($query,0,12)=='CREATE TABLE') {
              $action="CREATE TABLE";
            }
            if (substr($query,0,11)=='INSERT INTO') {
              $action="INSERT INTO";
            }
            if (substr($query,0,6)=='UPDATE') {
              $action="UPDATE";
            }
            if (substr($query,0,11)=='ALTER TABLE') {
              $action="ALTER TABLE";
            }
            if (substr($query,0,10)=='DROP TABLE') {
              $action="DROP TABLE";
            }
            if (substr($query,0,11)=='DELETE FROM') {
              $action="DELETE FROM";
            }
            if (substr($query,0,14)=='TRUNCATE TABLE') {
              $action="TRUNCATE TABLE";
            }
            $deb=strlen($action);
            $end=strpos($query,' ', $deb+1);
            $len=$end-$deb;
            $tableName=substr($query, $deb, $len );
            $tableName=trim($tableName);
            $tableName=trim($tableName,'`');
            switch ($action) {
              case "CREATE TABLE" :
                traceLog(" Table \"" . $tableName . "\" created."); 
                break;
              case "DROP TABLE" :
                traceLog(" Table \"" . $tableName . "\" dropped."); 
                break;
              case "ALTER TABLE" :
                traceLog(" Table \"" . $tableName . "\" altered."); 
                break;
              case "TRUNCATE TABLE" :
                traceLog(" Table \"" . $tableName . "\" truncated."); 
                break;                
              case "INSERT INTO":
                traceLog(" " . Sql::$lastQueryNbRows . " lines inserted into table \"" . $tableName . "\"."); 
                break;
              case "UPDATE":
                traceLog(" " . Sql::$lastQueryNbRows . " lines updated into table \"" . $tableName . "\"."); 
                break;
              case "DELETE FROM":
                traceLog(" " . Sql::$lastQueryNbRows . " lines deleted from table \"" . $tableName . "\"."); 
                break;              
              default:
                traceLog("ACTION NOT EXPECTED FOR QUERY : " . $query);
            }
          }
          $query="";
        }
    }
    if (array_key_exists($vers,$versionParameters)) {
      $nbParam=0;
      write($parametersLocation,'// New parameters ' . $vers . "\n");
      foreach($versionParameters[$vers] as $id=>$val) {
        $nbParam++;
        write($parametersLocation, '$' . $id . ' = \'' . addslashes($val) . '\';');
        write($parametersLocation, "\n");
        traceLog('Parameter $' . $id . ' added');
      }
      echo i18n('newParameters', array($nbParam, $vers));
      echo '<br/>' . "\n";
    }
    fclose($handle);
    traceLog("");
    traceLog("DATABASE UPDATED");
    if ($nbError==0) {
      traceLog(" WITH NO ERROR");
    } else {
      traceLog(" WITH " . $nbError . " ERROR" . (($nbError>1)?"S":""));
    }
  }
  traceLog("");
  return $nbError;
}

  function write($file,$msg) {
    return error_log($msg,3,$file);
  }

?>