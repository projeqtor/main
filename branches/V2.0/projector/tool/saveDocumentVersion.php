<?php 
include_once "../tool/projector.php";
header ('Content-Type: text/html; charset=UTF-8');
/** ===========================================================================
 * Save an attachement (file) : call corresponding method in SqlElement Class
 * The new values are fetched in $_REQUEST
 */

// ATTENTION, this PHP script returns its result into an iframe (the only way to submit a file)
// then the iframe returns the result to resultDiv to reproduce expected behaviour
?>
<html>
<head>   
</head>
<body onload="parent.saveDocumentVersionAck();">
<?php 
$error=false;
$documentVersionLink="";
$uploadedFile=false;
if (array_key_exists('documentVersionLink',$_REQUEST)) {
	$documentVersionLink=$_REQUEST['documentVersionLink'];
}
if (array_key_exists('documentVersionFile',$_FILES)) {
  $uploadedFile=$_FILES['documentVersionFile'];
} else if ($documentVersionLink!='') {
	// OK Link instead of file
} else {
  echo htmlGetErrorMessage(i18n('errorTooBigFile',array($paramAttachementMaxSize,'$paramAttachementMaxSize')));
  errorLog(i18n('errorTooBigFile',array($paramAttachementMaxSize,'$paramAttachementMaxSize')));
  $error=true; 
}
if ($uploadedFile and $documentVersionLink and $uploadedFile['name']) {
	echo htmlGetWarningMessage(i18n('errorFileOrLink',null));
	$error=true; 
}
if (! $error and $uploadedFile and $uploadedFile['name']) {
  if ( $uploadedFile['error']!=0 ) {
    switch ($uploadedFile['error']) {
      case 1:
        echo htmlGetErrorMessage(i18n('errorTooBigFile',array(ini_get('upload_max_filesize'),'upload_max_filesize')));
        errorLog(i18n('errorTooBigFile',array(ini_get('upload_max_filesize'),'upload_max_filesize')));
        break; 
      case 2:
        echo htmlGetErrorMessage(i18n('errorTooBigFile',array($paramAttachementMaxSize,'$paramAttachementMaxSize')));
        errorLog(i18n('errorTooBigFile',array($paramAttachementMaxSize,'$paramAttachementMaxSize')));
        break;  
      case 4:
        echo htmlGetWarningMessage(i18n('errorNoFile'));
        errorLog(i18n('errorNoFile'));
        break;  
      default:
        echo htmlGetErrorMessage(i18n('errorUploadFile',array($uploadedFile['error'])));
        errorLog(i18n('errorUploadFile',array($uploadedFile['error'])));
        break;
    }
    $error=true; 
  }
}
if (! $error and $uploadedFile and !$documentVersionLink) {
  if (! $uploadedFile['name']) {
    echo htmlGetWarningMessage(i18n('errorNoFile'));
    errorLog(i18n('errorNoFile'));
    $error=true; 
  }
}

$documentVersionNewVersion=null;
if (! array_key_exists('documentVersionNewVersion',$_REQUEST)) {
    echo htmlGetErrorMessage('documentVersionNewVersion parameter not found in REQUEST');
    $error=true;
}
$documentVersionNewVersion=$_REQUEST['documentVersionNewVersion'];

$documentVersionNewRevision=null;
if (! array_key_exists('documentVersionNewRevision',$_REQUEST)) {
    echo htmlGetErrorMessage('documentVersionNewRevision parameter not found in REQUEST');
    $error=true;
}
$documentVersionNewRevision=$_REQUEST['documentVersionNewRevision'];

$documentVersionNewDraft=null;
if (! array_key_exists('documentVersionNewDraft',$_REQUEST)) {
    echo htmlGetErrorMessage('documentVersionNewDraft parameter not found in REQUEST');
    $error=true;
}
$documentVersionNewDraft=$_REQUEST['documentVersionNewDraft'];

$documentId=null;
if (! array_key_exists('documentId',$_REQUEST)) {
    echo htmlGetErrorMessage('documentId parameter not found in REQUEST');
    $error=true;
}
$documentId=$_REQUEST['documentId'];

$documentVersionDescription=null;
if (! array_key_exists('documentVersionDescription',$_REQUEST)) {
    echo htmlGetErrorMessage('documentVersionDescription parameter not found in REQUEST');
    $error=true;
}
$documentVersionDescription=$_REQUEST['documentVersionDescription'];

$documentVersionNewVersionDisplay=null;
if (! array_key_exists('documentVersionNewVersionDisplay',$_REQUEST)) {
    echo htmlGetErrorMessage('documentVersionNewVersionDisplay parameter not found in REQUEST');
    $error=true;
}
$documentVersionNewVersionDisplay=$_REQUEST['documentVersionNewVersionDisplay'];


$documentVersionId=null;
if (array_key_exists('documentVersionId',$_REQUEST)) {
    $documentVersionId=$_REQUEST['documentVersionId'];
}

if (! $error) {
  $doc=New Document($documentId);
  $dir=New DocumentDirectory($doc->idDocumentDirectory);
	
	$dv=new DocumentVersion($documentVersionId);
  $dv->idDocument=$documentId;
  $dv->idAuthor=$user->id;
  $dv->versionDate=date("Y-m-d");
  if ($documentVersionLink) {
  	
  } else {
    $dv->fileName=basename($uploadedFile['name']);
    //$dv->mimeType=$uploadedFile['type'];
    //$dv->fileSize=$uploadedFile['size'];
  }
  $dv->description=$documentVersionDescription;
  $dv->version=$documentVersionNewVersion;
  $dv->revision=$documentVersionNewRevision;
  $dv->draft=$documentVersionNewDraft;
  $dv->name=$documentVersionNewVersionDisplay;
  $result=$dv->save();
  $newId= $dv->id;
  
  if ( ($doc->version==null) 
    or ( $dv->version>$doc->version ) 
    or ( $dv->version==$doc->version and $dv->revision>$doc->revision) 
    or ( $dv->version==$doc->version and $dv->revision==$doc->revision and $dv->draft>$doc->draft) ) {
    	$doc->version=$dv->version;
    	$doc->revision=$dv->revision;
    	$doc->draft=$dv->draft;
    	$doc->save();
    }
}

if (! $error and !$documentVersionLink) {
  $uploaddir = $paramAttachementDirectory . $paramPathSeparator . $dir->name . $paramPathSeparator;
  if (! file_exists($uploaddir)) {
    mkdir($uploaddir);
  }
  $uploadfile = $uploaddir . basename($uploadedFile['name']);
  if ( ! move_uploaded_file($uploadedFile['tmp_name'], $uploadfile)) {
     echo htmlGetErrorMessage(i18n('errorUploadFile','hacking ?'));
     errorLog(i18n('errorUploadFile','hacking ?'));
     $error=true;
     $dv->delete(); 
  } else {
    //$dv->subDirectory=$uploaddir;
    //$otherResult=$dv->save();
  }
}
if (! $error) {
  // Message of correct saving
  if (stripos($result,'id="lastOperationStatus" value="ERROR"')>0 ) {
    echo '<span class="messageERROR" >' . $result . '</span>';
  } else if (stripos($result,'id="lastOperationStatus" value="OK"')>0 ) {
    echo '<span class="messageOK" >' . $result . '</span>';
  } else { 
    echo '<span class="messageWARNING" >' . $result . '</span>';
  }
} else {
   echo '<input type="hidden" id="lastSaveId" value="" />';
   echo '<input type="hidden" id="lastOperation" value="file upload" />';
   echo '<input type="hidden" id="lastOperationStatus" value="ERROR" />';
}
?>
</body>
</html>