<?php 
/* ============================================================================
 * ActionType defines the type of an issue.
 */ 
class Cron {

  // Define the layout that will be used for lists
    
  private static $sleepTime;
  private static $checkDates;
  private static $checkImport;
  private static $runningFile='../files/cron/RUNNING';
  private static $timesFile='../files/cron/DELAYS';
  private static $stopFile='../files/cron/STOP';
  private static $errorFile='../files/cron/ERROR';
  
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL) {
    
  }

  
   /** ==========================================================================
   * Destructor
   * @return void
   */ 
  function __destruct() {
    
  }

// ============================================================================**********
// GET STATIC DATA FUNCTIONS
// ============================================================================**********
  
  public static function getActualTimes() {
  	if (! is_file(self::$timesFile)) {
  		return array();
  	}
  	$handle=fopen(self::$timesFile, 'r');
    $line=fgets($handle);
    fclose($handle);
    $result=array();
    $arr=explode('|',$line);
    foreach ($arr as $val) {
    	$split=explode('=',$val);
    	if (count($split)==2) {
    	  $result[$split[0]]=$split[1];
    	}
    }
  	return $result;
  }

  public static function setActualTimes() {
    $handle=fopen(self::$timesFile, 'w');
    fwrite($handle,'SleepTime='.self::getSleepTime().'|CheckDates='.self::getCheckDates().'|CheckImport='.self::getCheckImport() );
    fclose($handle);
  }
  
  public static function getSleepTime() {
    if (self::$sleepTime) {
    	return self::$sleepTime;
    }
  	$cronSleepTime=Parameter::getGlobalParameter('cronSleepTime');
    if (! $cronSleepTime) {$cronSleepTime=10;}
    self::$sleepTime=$cronSleepTime;
    return self::$sleepTime;
  }

  public static function getCheckDates() {
    if (self::$checkDates) {
      return self::$checkDates;
    }
    $checkDates=Parameter::getGlobalParameter('cronCheckDates'); 
    if (! $checkDates) {$checkDates=30;}
    self::$checkDates=$checkDates;
    return self::$checkDates;
  }

  public static function getCheckImport() {
    if (self::$checkImport) {
      return self::$checkImport;
    }
    $checkImport=Parameter::getGlobalParameter('cronCheckImport'); 
    if (! $checkImport) {$checkImport=30;}
    self::$checkImport=$checkImport;
    return self::$checkImport;
  }  
  
  public static function check() {
    if (file_exists(self::$runningFile)) {
      $handle=fopen(self::$runningFile, 'r');
      $last=fgets($handle);
      $now=time();
      fclose($handle);
      if ( ($now-$last) > (self::getSleepTime()*5)) {
        // not running for more than 5 cycles : dead process
        self::removeRunningFlag();
        return "stopped";
      } else {
        return "running";
      }
    } else {
      return "stopped";
    }
  }
  
  public static function abort() {
    errorLog('cron abnormally stopped');
  	unlink(self::$runningFile);
    $errorFile=fopen(self::$errorFile.'_'.date('Ymd_His'), 'w');
    fclose($errorFile);  
  } 
  
  public static function removeStopFlag() {
    if (file_exists(self::$stopFile)) {
      unlink(self::$stopFile);
    }
  }
  
  public static function removeRunningFlag() {
    if (file_exists(self::$runningFile)) {
      unlink(self::$runningFile);
    }
  }
  
  public static function setRunningFlag() {
  	$handle=fopen(self::$runningFile, 'w');
    fwrite($handle,time());
    fclose($handle);
  }
  
  public static function setStopFlag() {
    $handle=fopen(self::$stopFile, 'w');
    fclose($handle);
  }
  
  public static function checkStopFlag() {
    if (file_exists(self::$stopFile)) { 
      traceLog('cron normally stopped at '.date('d/m/Y H:i:s'));
      unlink(self::$runningFile);
      unlink(self::$stopFile);
      return true; 
    } else {
    	return false;
    }
  }
  
	// If running flag exists and cron is not really running, relaunch
	public static function relaunch() {
		if (file_exists(self::$runningFile)) {
      $handle=fopen(self::$runningFile, 'r');
      $last=fgets($handle);
      $now=time();
      fclose($handle);
      if ( ($now-$last) > (self::getSleepTime()*5)) {
        // not running for more than 5 cycles : dead process
        self::removeRunningFlag();
        self::run();
      }
		}
	}
	
	public static function run() {
scriptLog('Cron::run()');		
		if (self::check()=='running') {
      errorLog('try to run cron already running');
      return;
    }
    set_time_limit(0);
    ignore_user_abort(1);
    session_write_close();
    $cronCheckDates=self::getCheckDates();
    $cronCheckImport=self::getCheckImport();
    $cronSleepTime=self::getSleepTime();
    self::setActualTimes();
    self::removeStopFlag();
    self::setRunningFlag();
    traceLog('cron started at '.date('d/m/Y H:i:s')); 
    while(1) {
      if (self::checkStopFlag()) {
        return; 
      }
      self::setRunningFlag();
      $cronCheckDates-=$cronSleepTime;
      if ($cronCheckDates<=0) {
      	try { 
          self::checkDates();
      	} catch (Exception $e) {
      		traceLog("Cron::run() - Error on checkDates()");
      	}
        $cronCheckDates=Cron::getCheckDates();
      }
      $cronCheckImport-=$cronSleepTime;
      if ($cronCheckImport<=0) {
      	try { 
          self::checkImport();
      	} catch (Exception $e) {
          traceLog("Cron::run() - Error on checkImport()");
        }
        $cronCheckImport=Cron::getCheckImport();
      }
      sleep($cronSleepTime);
    }
  }
  
  public static function checkDates() {
  	global $globalCronMode;
scriptLog('Cron::checkDates()');
    $globalCronMode=true;  
    $indVal=new IndicatorValue();
    $where="idle='0' and (";
    $where.=" ( warningTargetDateTime<='" . date('Y-m-d H:i:s') . "' and warningSent='0')" ;
    $where.=" or ( alertTargetDateTime<='" . date('Y-m-d H:i:s') . "' and alertSent='0')" ;
    $where.=")";
    $lst=$indVal->getSqlElementsFromCriteria(null, null, $where);

    foreach ($lst as $indVal) {
      $indVal->checkDates();
    }
  }
  
  public static function checkImport() {
scriptLog('Cron::checkImport()');
  	global $globalCronMode, $globalCatchErrors;
    $globalCronMode=true;   	
    $globalCatchErrors=true;
  	$importDir=Parameter::getGlobalParameter('cronImportDirectory');
  	$cpt=0;
  	$pathSeparator=Parameter::getGlobalParameter('paramPathSeparator');
  	$importSummary="";
  	$importFullLog="";
  	if (is_dir($importDir)) {
      if ($dirHandler = opendir($importDir)) {
        while (($file = readdir($dirHandler)) !== false) {
        	if ($file!="." and $file!=".." and filetype($importDir . $pathSeparator . $file)=="file") {
            $importFile=$importDir . $pathSeparator . $file;      
            $split=explode('_',$file);
            $class=$split[0];
            $result=Importable::import($importFile, $class);
            $globalCronMode=false; // VOLOUNTARILY STOP THE CRON. Actions are requested !
            try {
	            if ($result=="OK") {	            	
	              $msg="Import OK : file $file imported with no error [ Number of '$class' imported : " . Importable::$cptDone . " ]";
	              traceLog($msg);
	              $importSummary.="<span style='color:green;'>$msg</span><br/>";
	              if (! is_dir($importDir . $pathSeparator . "done")) {
	              	mkdir($importDir . $pathSeparator . "done",777,true);
	              }
	              rename($importFile,$importDir . $pathSeparator . "done" . $pathSeparator . $file);
	            } else {
	            	if ($result=="INVALID") {
	               	$msg="Import INVALID : file $file imported with " . Importable::$cptInvalid . " control errors [ Number of '$class' imported : " . Importable::$cptOK . " ]";
	               	traceLog($msg);
                  $importSummary.="<span style='color:orange;'>$msg</span><br/>";
	              } else {
	            	  $msg="Import ERROR : file $file imported with " . Importable::$cptRejected . " errors [ Number of '$class' imported : " . Importable::$cptOK . " ]";
	            	  traceLog($msg);
                  $importSummary.="<span style='color:red;'>$msg</span><br/>";
	              }
	              if (! is_dir($importDir . $pathSeparator . "error")) {
	                mkdir($importDir . $pathSeparator . "error",777,true);
	              }
	            	rename($importFile,$importDir . $pathSeparator . "error" . $pathSeparator . $file);
	            }
            } catch (Exception $e) {
            	$msg="CRON : Impossible to move file '$importFile'";
            	traceLog($msg);
              $importSummary.="<span style='color:red;'>$msg</span><br/>";
            	$msg="CRON IS STOPPED TO AVOID MULTIPLE-TREATMENT OF SAME FILES";
            	traceLog($msg);
              $importSummary.="<span style='color:red;'>$msg</span><br/>";
            	$msg="Check access rights to folder '$importDir', subfolders 'done' and 'error' and file '$importFile'";
            	traceLog($msg);
              $importSummary.="<span style='color:red;'>$msg</span><br/>";
            	exit; // VOLOUNTARILY STOP THE CRON. Actions are requested !
            }
            $globalCronMode=true; // If cannot write log file, do not exit CRON (not blocking)
            $logFile=$importDir . $pathSeparator . 'logs' . $pathSeparator . substr($file, 0, strlen($file)-4) . ".log.htm";
        	  if (! is_dir($importDir . $pathSeparator . "logs")) {
              mkdir($importDir . $pathSeparator . "logs",777,true);
            }
            if (file_exists($logFile)) {
            	kill($logFile);
            }
            $fileHandler = fopen($logFile, 'w');
            fwrite($fileHandler, Importable::getLogHeader());
            fwrite($fileHandler, Importable::$importResult);
            fwrite($fileHandler, Importable::getLogFooter());
            fclose($fileHandler);
            $importFullLog.="<br/><br/>";
            $importFullLog.="$file<br/>";
            $importFullLog.=Importable::$importResult;
            $cpt+=1;
        	}
        }
        closedir($dirHandler);
      }
    } else {
    	$msg="ERROR - check Cron::Import() - ". $importDir . " is not a directory";
    	traceLog($msg);
      $importSummary.="<span style='color:red;'>$msg</span><br/>";
    }
    if ($importSummary) {
	    $logDest=Parameter::getGlobalParameter('cronImportLogDestination');
	    if (stripos($logDest,'mail')!==false) {
	    	$baseName=Parameter::getGlobalParameter('paramDbDisplayName');
	    	$to=Parameter::getGlobalParameter('cronImportMailList');
	    	if (! $to) {
	    		traceLog("Cron : email requested, but no email address defined");
	    	} else {
		      $message=$importSummary;
		      if (stripos($logDest,'log')!==false) {
		      	$message=Importable::getLogHeader()
		      	         .$message
		      	         .$importFullLog;
		      	         Importable::getLogFooter();
		      }
	        $title="[$baseName] Import summary ". date('Y-m-d H:i:s');
	        $resultMail=sendMail($to, $title, $message);	        
	    	}
	    }
    }
  }
  
}
?>