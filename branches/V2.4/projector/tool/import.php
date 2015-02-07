<?php
include_once "../tool/projector.php";
scriptLog('   ->/tool/import.php');
header ('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" 
  "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <title><?php echo i18n("applicationTitle");?></title>
  <link rel="shortcut icon" href="../view/img/logo.ico" type="../view/image/x-icon" />
  <link rel="icon" href="../view/img/logo.ico" type="../view/image/x-icon" />
  <link rel="stylesheet" type="text/css" href="../view/css/projector.css" />
</head>

<body class="white" onLoad="top.hideWait();" style="overflow: auto; ">
<?php 
$class='';
$dateFormat='dd/mm/yyyy';

if (! array_key_exists('elementType',$_REQUEST)) {
  throwError('elementType parameter not found in REQUEST');
}
$class=SqlList::getNameFromId('Importable',$_REQUEST['elementType'],false);
///
/// Upload file
$error=false;
if (array_key_exists('importFile',$_FILES)) {
  $uploadedFile=$_FILES['importFile'];
} else {
  echo htmlGetErrorMessage(i18n('errorNotFoundFile'));
  errorLog(i18n('errorNotFoundFile'));
  exit;
}
if ( $uploadedFile['error']!=0 ) {
  switch ($uploadedFile['error']) {
    case 1:
      echo htmlGetErrorMessage(i18n('errorTooBigFile',array(ini_get('upload_max_filesize'),'upload_max_filesize')));
      errorLog(i18n('errorTooBigFile',array(ini_get('upload_max_filesize'),'upload_max_filesize')));
      exit;
      break; 
    case 2:
      echo htmlGetErrorMessage(i18n('errorTooBigFile',array($paramAttachementMaxSize,'$paramAttachementMaxSize')));
      errorLog(i18n('errorTooBigFile',array($paramAttachementMaxSize,'$paramAttachementMaxSize')));
      exit;
      break;  
    case 4:
      echo htmlGetWarningMessage(i18n('errorNoFile'));
      errorLog(i18n('errorNoFile'));
      exit;
      break;  
    default:
      echo htmlGetErrorMessage(i18n('errorUploadFile',array($uploadedFile['error'])));
      errorLog(i18n('errorUploadFile',array($uploadedFile['error'])));
      exit;
      break;
  }
}
if (! $uploadedFile['name']) {
  echo htmlGetWarningMessage(i18n('errorNoFile'));
  errorLog(i18n('errorNoFile'));
  $error=true; 
}
$uploaddir = $paramAttachementDirectory . $paramPathSeparator . "import" . $paramPathSeparator;
if (! file_exists($uploaddir)) {
  mkdir($uploaddir);
}
$uploadfile = $uploaddir . basename($uploadedFile['name']);
if ( ! move_uploaded_file($uploadedFile['tmp_name'], $uploadfile)) {
   echo htmlGetErrorMessage(i18n('errorUploadFile','hacking ?'));
   errorLog(i18n('errorUploadFile','hacking ?'));
   exit; 
}
////
set_time_limit(3600);

$fileName=$uploadfile;

$lines=file($fileName);
$title=null;
$idxId=-1;
$csvSep=Parameter::getGlobalParameter('csvSeparator');
$obj=new $class();
$captionArray=array();
foreach ($obj as $fld=>$val) {
	$captionArray[$obj->getColCaption($fld)]=$fld;
}
echo '<TABLE WIDTH="100%" style="border: 1px solid black">';
foreach ($lines as $nbl=>$line) {
	if (! mb_detect_encoding($line, 'UTF-8', true) ) {
		$line=utf8_encode($line);
	}
  if ($title) {
    echo '<TR>';
    $fields=explode($csvSep,$line);     
    $id=($idxId>=0)?$fields[$idxId]:null;
    $obj=new $class($id);
//echo $id . "/" . $obj->id . "<br/>";
    foreach ($fields as $idx=>$field) { 
      if ($field=='') {
        echo '<td class="messageData" style="color:#000000;">' . htmlEncode($field) . '</td>';
        continue; 
      }
      if ( strtolower($field)=='null') {
        $field=null;
      } 
      if (substr(trim($field),0,1)=='"' and substr(trim($field),-1,1)=='"') {
      	$field=substr(trim($field),1,strlen(trim($field))-2);
      }
      $field=str_replace('""','"',$field);     
      if (property_exists($obj,$title[$idx])) {
        $obj->$title[$idx]=$field;
        echo '<td class="messageData" style="color:#000000;">' . htmlEncode($field) . '</td>';
        continue; 
      } 
      $idTitle='id' . ucfirst($title[$idx]);
      if (property_exists($obj,$idTitle)) {
        $val=SqlList::getIdFromName(ucfirst($title[$idx]),$field);
        echo '<td class="messageData" style="color:#000000;">' . htmlEncode($field) . "/" . htmlEncode($val) . '</td>';
        //echo " => " . htmlEncode($idTitle);
        //echo "=" . htmlEncode($val);
        $obj->$idTitle=$val;
        continue; 
      } 
      if (property_exists($obj,get_class($obj).'PlanningElement')) {
        $peClass=get_class($obj).'PlanningElement';
        if (! is_object($obj->$peClass)) {
          $obj->$peClass=new $peClass();
        }
        $pe=$obj->$peClass;
        if (property_exists($pe,$title[$idx])) {
          $obj->$peClass->$title[$idx]=$field;
          echo '<td class="messageData" style="color:#000000;">' . htmlEncode($field) . '</td>';
          continue; 
        }
        $idTitle='id' . ucfirst($title[$idx]);
        if (property_exists($pe,$idTitle)) {   
          echo '<td class="messageData" style="color:#000000;">' . htmlEncode($field) . '</td>';
          $val=SqlList::getIdFromName(ucfirst($title[$idx]),$field);   
          $obj->$peClass->$idTitle=$val;
          continue; 
        } 
      }
      echo '<td class="messageData" style="color:#A0A0A0;">' . htmlEncode($field) . '</td>';
      continue; 
    }
    echo '<TD class="messageData" width="20%">';
    //$obj->id=null;
    $result=$obj->save();
    if (stripos($result,'id="lastOperationStatus" value="ERROR"')>0 ) {
      echo '<span class="messageERROR" >' . $result . '</span>';
    } else if (stripos($result,'id="lastOperationStatus" value="OK"')>0 ) {
      echo '<span class="messageOK" >' . $result . '</span>';
    } else { 
      echo '<span class="messageWARNING" >' . $result . '</span>';
    }
    echo '</TD></TR>';
  } else {
    $title=explode($csvSep,$line);
    echo "<TR>";
    $obj=new $class();
    foreach ($title as $idx=>$caption) {
      $title[$idx]=str_replace(' ','',strtolower(substr($caption,0,1)) . substr($caption,1));
      $title[$idx]=str_replace(chr(13),'',$title[$idx]);
      $title[$idx]=str_replace(chr(10),'',$title[$idx]);
      $color="#A0A0A0";
      $colCaption=$caption;
      if (property_exists($obj,$title[$idx])) {
        $color="#000000";
        $colCaption=$obj->getColCaption($title[$idx]);
        if ($title[$idx]=='id') {
          $idxId=$idx;
        }
      } else { 
        $idTitle='id' . ucfirst($title[$idx]);
        if (property_exists($obj,$idTitle)) {   
          $color="#000000";
          $colCaption=$obj->getColCaption($idTitle);
        } else if (array_key_exists($caption,$captionArray) and property_exists($obj,$captionArray[$caption]) ) {
          $color="#000000";
          $colCaption=$caption;
          $title[$idx]=$captionArray[$caption];
          if (substr($title[$idx],0,2)=="id" and strlen($title[$idx])>2) {
          	$title[$idx]=substr($title[$idx],2);
          }
        } else if (property_exists($obj,get_class($obj).'PlanningElement')) {
          $peClass=get_class($obj).'PlanningElement';
          $pe=$obj->$peClass;
          if (property_exists($pe,$title[$idx])) {
            $color="#000000";
            $colCaption=$pe->getColCaption($title[$idx]);
          } else {
            $idTitle='id' . ucfirst($title[$idx]);
            if (property_exists($pe,$idTitle)) {   
              $color="#000000";
              $colCaption=$pe->getColCaption($idTitle);
            }
          }
        }
      }
      echo '<TH class="messageHeader" style="color:' . $color . ';">' . $colCaption . "</TH>";
    }
    echo '<th class="messageHeader" style="color:#208020">' . i18n('colResultImport') . '</th></TR>';
  }
}
echo "</TABLE>";
?>
</body>
</html>