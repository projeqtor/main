<?php
/* ============================================================================
 * Habilitation defines right to the application for a menu and a profile.
 */
class Importable extends SqlElement {

	// extends SqlElement, so has $id
	public $id;    // redefine $id to specify its visible place
	public $name;

	public $_isNameTranslatable = true;

	public static $importResult;
	public static $cptTotal;
	public static $cptDone;
	public static $cptUnchanged;
	public static $cptCreated;
	public static $cptModified;
	public static $cptRejected;
	public static $cptInvalid;
	public static $cptError;
	//
	public static $cptOK;
	public static $cptWarning;

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
	// MISCELLANOUS FUNCTIONS
	// ============================================================================**********

	public static function import($fileName, $class) {
		scriptLog("import($fileName, $class)");
		// Control that mbsting is available
    if (! function_exists('mb_detect_encoding')) {
      debugLog ("ERROR - mbstring not enabled - Import cancelled");
      $msg='<b>Error - mbstring is not enabled</b><br/>Import aborted<br/>Contact your administrator';
      self::$importResult=$msg;
      return $msg;
    }
		SqlList::cleanAllLists(); // Added for Cron mode : as Cron is never stopped, Static Lists must be freshened
  	set_time_limit(3600); // 60mn
		$htmlResult="";
		self::$cptTotal=0;
		self::$cptDone=0;
		self::$cptUnchanged=0;
		self::$cptCreated=0;
		self::$cptModified=0;
		self::$cptRejected=0;
		self::$cptInvalid=0;
		self::$cptError=0;
		self::$cptOK=0;
		self::$cptWarning=0;
		$lines=file($fileName);
		$title=null;
		$idxId=-1;
		$csvSep=Parameter::getGlobalParameter('csvSeparator');
		if (! class_exists($class)) {
			self::$importResult="Cron error : class '$class' is unknown";
			self::$cptError=1;
			self::$cptRejected=1;
			return "ERROR";
		}
		$obj=new $class();
		$captionArray=array();
		$captionObjectArray=array();
		$objectArray=array();
		$titleObject=array();
		$idArray=array();
		foreach ($obj as $fld=>$val) {
			if (is_object($val)) {
				$objectArray[$fld]=$val;
				foreach ($val as $subfld=>$subval) {
					$capt=$val->getColCaption($subfld);
					if ($subfld!='id' and substr($capt,0,1)!='[') {
						$captionArray[$capt]=$subfld;
						$captionObjectArray[$capt]=$fld;
					}
				}
			} else {
				$capt=$obj->getColCaption($fld);
				if (substr($capt,0,1)!='[') {
					$captionArray[$capt]=$fld;
				}
			}
		}
		$htmlResult.='<TABLE WIDTH="100%" style="border: 1px solid black; border-collapse:collapse;">';
		Sql::beginTransaction();
		$continuedLine="";
		foreach ($lines as $nbl=>$line) {			
			if (trim($line)=='') {
				continue;
			}			
			if (! mb_detect_encoding($line, 'UTF-8', true) ) {
				$line=utf8_encode($line);
			}
			if ($continuedLine) {
				$line=$continuedLine.$line;
				$continuedLine="";
			}
			if ($title) {
				$htmlResult.= '<TR>';
				$fields=explode($csvSep,$line);
				if (count($fields)!=count($title)) {
					if (count($fields)<count($title)) {
						$continuedLine=$line;
						continue;
					} else {
					  self::$cptError+=1;
					  $htmlResult.= '<td colspan="'.count($title).'" style="border:1px solid black;">';
					  $htmlResult.= '<span class="messageERROR" >ERROR : column count is incorrect</span>';
					  $htmlResult.= '</td>';
					  continue;
					}
				}
				$id=($idxId>=0)?$fields[$idxId]:null;
				$obj=new $class($id);
				$forceInsert=(!$obj->id and $id and !Sql::isPgsql())?true:false;
				self::$cptTotal+=1;
				foreach ($fields as $idx=>$field) {
					if ($field=='') {
						$htmlResult.= '<td class="messageData" style="color:#000000;border:1px solid black;">' . htmlEncode($field) . '</td>';
						continue;
					}
					if ( strtolower($field)=='null') {
						$field=null;
					}
					if (substr(trim($field),0,1)=='"' and substr(trim($field),-1,1)=='"') {
						$field=substr(trim($field),1,strlen(trim($field))-2);
					}
					if ($idx==count($fields)-1) {
						$field=trim($field,"\r");
						$field=trim($field,"\r\n");
					}
					$field=str_replace('""','"',$field);
					if (property_exists($obj,$title[$idx])) {
						if (substr($title[$idx],0,2)=='id' and strlen($title[$idx])>2 and ! is_numeric($field)) {
							$obj->$title[$idx]=SqlList::getIdFromName(substr($title[$idx],2), $field);
						} else {
							$obj->$title[$idx]=$field;
						}
						$htmlResult.= '<td class="messageData" style="color:#000000;border:1px solid black;">' . htmlEncode($field) . '</td>';
						continue;
					}
					if (isset($titleObject[$idx])) {
						$subClass=$titleObject[$idx];
						if (! is_object($obj->$subClass)) {
							$obj->$subClass=new $subClass();
						}
						$sub=$obj->$subClass;
						if (property_exists($subClass,$title[$idx])) {
							if (substr($title[$idx],0,2)=='id' and strlen($title[$idx])>2 and ! is_numeric($field)) {
								$obj->$subClass->$title[$idx]=SqlList::getIdFromName(substr($title[$idx],2), $field);
							} else {
								$obj->$subClass->$title[$idx]=$field;
							}
							$htmlResult.= '<td class="messageData" style="color:#000000;border:1px solid black;">' . htmlEncode($field) . '</td>';
							continue;
						}
					}
					$htmlResult.= '<td class="messageData" style="color:#A0A0A0;border:1px solid black;">' . htmlEncode($field) . '</td>';
					continue;
				}
				$htmlResult.= '<TD class="messageData" width="20%" style="border:1px solid black;">';
				//$obj->id=null;
				if ($forceInsert or !$obj->id) {
					if (property_exists($obj,"creationDate") and ! trim($obj->creationDate)) {
						$obj->creationDate=date('Y-m-d');
					}
					if (property_exists($obj,"creationDateTime") and ! trim($obj->creationDateTime)) {
						$obj->creationDateTime=date('Y-m-d H:i');
					}
				}
				if ($forceInsert) { // object with defined id was not found : force insert
					$result=$obj->insert();
				} else {
					$result=$obj->save();
				}
				if (stripos($result,'id="lastOperationStatus" value="ERROR"')>0 ) {
					$htmlResult.= '<span class="messageERROR" >' . $result . '</span>';
					self::$cptError+=1;
				} else if (stripos($result,'id="lastOperationStatus" value="OK"')>0 ) {
					$htmlResult.= '<span class="messageOK" >' . $result . '</span>';
					self::$cptOK+=1;
					if (stripos($result,'id="lastOperation" value="insert"')>0) {
						self::$cptCreated+=1;
					} else  if (stripos($result,'id="lastOperation" value="update"')>0) {
						self::$cptModified+=1;
					} else {
						// ???
					}
				} else {
					$htmlResult.= '<span class="messageWARNING" >' . $result . '</span>';
					self::$cptWarning+=1;
					if (stripos($result,'id="lastOperationStatus" value="INVALID"')>0) {
						self::$cptInvalid+=1;
					} else if (stripos($result,'id="lastOperationStatus" value="NO_CHANGE"')>0) {
						self::$cptUnchanged+=1;
					} else {
						// ???
					}
				}
				$htmlResult.= '</TD></TR>';
			} else {
				$title=explode($csvSep,$line);
				$htmlResult.= "<TR>";
				$obj=new $class();
				foreach ($title as $idx=>$caption) {
					$title[$idx]=trim($caption);
					$title[$idx]=str_replace(chr(13),'',$title[$idx]);
					$title[$idx]=str_replace(chr(10),'',$title[$idx]);
					$color="#A0A0A0";
					$colCaption=$title[$idx];
					$testTitle=str_replace(' ', '', $title[$idx]);
					$testIdTitle='id'.ucfirst($testTitle);
					$testCaption=$title[$idx];
					if (property_exists($obj,$testTitle)) { // Title is directly field id
						$title[$idx]=$testTitle;
						$color="#000000";
						$colCaption=$obj->getColCaption($title[$idx]);
						if ($title[$idx]=='id') {
							$idxId=$idx;
						}
					} else if (property_exists($obj,$testIdTitle)) { // Title is field id withoud the 'id' (for external reference)
						$title[$idx]=$testIdTitle;
						$idArray[$idx]=true;
						$color="#000000";
						$colCaption=$obj->getColCaption($title[$idx]);
					} else if (array_key_exists($testCaption,$captionArray)) {
						$color="#000000";
						$colCaption=$testCaption;
						$title[$idx]=$captionArray[$testCaption];
						if (isset($captionObjectArray[$testCaption])) {
							$titleObject[$idx]=$captionObjectArray[$testCaption];
						}
					} else {
						foreach ($objectArray as $fld=>$subObj) {
							if (property_exists($subObj,$testTitle)) { // Title is directly field id
								$title[$idx]=$testTitle;
								$color="#000000";
								$titleObject[$idx]=$fld;
								$colCaption=$obj->getColCaption($title[$idx]);
							} else if (property_exists($subObj,$testIdTitle)) { // Title is field id withoud the 'id' (for external reference)
								$title[$idx]=$testIdTitle;
								$idArray[$idx]=true;
								$color="#000000";
								$titleObject[$idx]=$fld;
								$colCaption=$obj->getColCaption($title[$idx]);
							}
						}
					}
					$htmlResult.= '<TH class="messageHeader" style="color:' . $color . ';border:1px solid black;background-color: #DDDDDD">' . $colCaption . "</TH>";
				}
				$htmlResult.= '<th class="messageHeader" style="color:#208020;border:1px solid black;;background-color: #DDDDDD">' . i18n('colResultImport') . '</th></TR>';
			}
		}
		Sql::commitTransaction();
		$htmlResult.= "</TABLE>";
		self::$importResult=$htmlResult;
		self::$cptDone=self::$cptCreated+self::$cptModified+self::$cptUnchanged;
		self::$cptRejected=self::$cptInvalid+self::$cptError;
		if (self::$cptError==0) {
			if (self::$cptInvalid==0) {
				$globalResult="OK";
			} else {
				$globalResult="INVALID";
			}
		} else {
			$globalResult="ERROR";
		}
		$log=new ImportLog();
		$log->name=basename($fileName);
		$log->mode="automatic";
		$log->importDateTime=date('Y-m-d H:i:s');
		$log->importFile=$fileName;
		$log->importClass=$class;
		$log->importStatus=$globalResult;
		$log->importTodo=self::$cptTotal;
		$log->importDone=self::$cptDone;
		$log->importDoneCreated=self::$cptCreated;
		$log->importDoneModified=self::$cptModified;
		$log->importDoneUnchanged=self::$cptUnchanged;
		$log->importRejected=self::$cptRejected;
		$log->importRejectedInvalid=self::$cptInvalid;
		$log->importRejectedError=self::$cptError;
		$log->save();
		return $globalResult;
	}

	public static function getLogHeader() {
		$nl=Parameter::getGlobalParameter('mailEol');
		$result="";
		$result.='<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">'.$nl;
		$result.='<html>'.$nl;
		$result.='<head>'.$nl;
		$result.='<meta http-equiv="content-type" content="text/html; charset=UTF-8" />'.$nl;
		$result.='<title>' . i18n("applicationTitle") . '</title>'.$nl;
		$result.='<style type="text/css">'.$nl;
		$result.='body{font-family:Verdana,Arial,Tahoma,sans-serif;font-size:8pt;}'.$nl;
		$result.='table{width:100%;border-collapse:collapse;border:1px;}'.$nl;
		$result.='.messageData{font-size:90%;padding:1px 5px 1px 5px;border:1px solid #AAAAAA;vertical-align:top;background-color:#FFFFFF;}'.$nl;
		$result.='.messageHeader{border:1px solid #AAAAAA;text-align:center;font-weight:bold;background:#DDDDDD;color:#505050;}';
		$result.='.messageERROR{color:red;font-weight:bold;}';
		$result.='.messageOK{color:green;}';
		$result.='.messageWARNING{color:black;}';
		$result.='</style>'.$nl;
		$result.='</head>'.$nl;
		$result.='<body style="font-family:Verdana,Arial,Tahoma,sans-serif;font-size:8pt;">'.$nl;
		return $result;
	}
	public static function getLogFooter() {
		$nl=Parameter::getGlobalParameter('mailEol');
		$nl=(isset($nl) and $nl)?$nl:"\r\n";
		$result="";
		$result.='</body>'.$nl;
		$result.='</html>';
		return $result;
	}
}
?>