<?php
/** ===========================================================================
 * Download a file descripbde in the correcponding object 
 * @param class=class of object containing file description
 * @param id = id of object
 * @param display = bolean (existence is enough) to enable display, either download is forced
 */

require_once "../tool/projector.php";
scriptLog('   ->/tool/download.php');
$class=$_REQUEST['class'];
$id=$_REQUEST['id'];

$obj=new $class($id);

if ($class=='Attachement') {
  $path = $obj->subDirectory;
  $name = $obj->fileName;
  $size = $obj->fileSize;
  $type = $obj->mimeType;
  $file = $path . $name;
} else if ($class=='DocumentVersion') {
	$doc=new Document($obj->idDocument);
	$dir=new DocumentDirectory($doc->idDocumentDirectory);
	$path = Parameter::getGlobalParameter('documentRoot') . $dir->location;
  $name = $obj->fileName;
  $size = $obj->fileSize;
  $type = $obj->mimeType;
  $file = $path . $paramPathSeparator . $name . '.' . $obj->id;
}
$contentType="application/force-download";
//if (array_key_exists('display',$_REQUEST)) {
//  $contentType=$type;
//}

if (($file != "") && (file_exists($file))) { 
  header("Content-Type: " . $contentType . "; name=\"" . $name . "\""); 
  header("Content-Transfer-Encoding: binary"); 
  header("Content-Length: $size"); 
  header("Content-Disposition: attachment; filename=\"" .$name . "\""); 
  header("Expires: 0"); 
  header("Cache-Control: no-cache, must-revalidate");
  header("Pragma: no-cache");
  readfile($file);  
} else {
	debugLog($file . ' not found');
}

?>